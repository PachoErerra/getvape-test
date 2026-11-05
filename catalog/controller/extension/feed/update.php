<?php
class ControllerExtensionFeedUpdate extends Controller {

	private $warehouses = array();
	private $warehousesnames = array();
	private $attributes = array();
	private $attributesnames = array();
	private $attributValues = array();
	private $attributValuesNames = array();
	private $skuProducts = array();
	private $fordel = array();
	private $manufacturers = array();
	 
	// Склад GetVape/Офис - в онлайн-остаток, без витрины самовывоза
	private $sklad = 230; // ef889057-fba5-11ed-aac0-000c29a71cec
	private $online_warehouses = null;
	
	private $glicerin_price = 30; //цена глицерина
	private $buster_category_id = 2444; //айди категории бустеров
	private $buster_products = array(); //айди товаров бустеров
	private $sol_category_id = 3013; //айди категории бустеров
	private $sol_products = array(); //айди товаров бустеров
	private $nikotin_id = 18565; //айди никотина
	private $nikotin_price = 0; //цена никотина

	private $sync_job_id = 0;
	private $sync_stats = array('updated' => 0, 'created' => 0, 'disabled' => 0, 'skipped' => 0);
	private $sync_stages = array();
	private $sync_stage_name = '';
	private $sync_stage_started = 0;
	private $sync_diff = array(
		'manufacturer' => 0,
		'name' => 0,
		'description' => 0,
		'tag' => 0,
		'price_base' => 0,
		'price_special' => 0,
		'reward' => 0,
		'attributes' => 0,
		'status' => 0,
		'categories' => 0,
		'related' => 0,
		'quantity' => 0,
		'sol_options' => 0,
	);
	private $sync_diff_samples = array();

	private $productsByGuid = array();
	private $categoriesByGuid = array();
	private $productQuantities = array();
	private $productAttributesSig = array();
	private $productCategoriesSig = array();
	private $productRelatedSig = array();
	private $productBonus = array();
	private $modelsByCode = array();
	private $productZnyzhkyLocks = array();

	/**
	 * Старый URL cron/кнопки: только ставит job в очередь (без долгого синка).
	 */
	public function index() {
		return $this->enqueue();
	}

	public function enqueue() {
		$this->load->model('extension/feed/sync_1c');

		$key = isset($this->request->get['key']) ? (string)$this->request->get['key'] : '';
		$source = isset($this->request->get['source']) ? (string)$this->request->get['source'] : 'cron';
		$is_cli = (php_sapi_name() === 'cli');

		if (!$is_cli && !$this->model_extension_feed_sync_1c->checkKey($key)) {
			http_response_code(403);
			echo 'Forbidden';
			return;
		}

		if (!in_array($source, array('cron', 'admin'), true)) {
			$source = 'cron';
		}

		$job_id = $this->model_extension_feed_sync_1c->addJob($source);

		$this->response->addHeader('Content-Type: text/plain; charset=utf-8');
		echo 'queued:' . (int)$job_id;
	}

	public function worker() {
		$this->load->model('extension/feed/sync_1c');

		$key = isset($this->request->get['key']) ? (string)$this->request->get['key'] : '';
		$is_cli = (php_sapi_name() === 'cli' || !empty($this->request->get['cli']));

		if (!$is_cli && !$this->model_extension_feed_sync_1c->checkKey($key)) {
			http_response_code(403);
			echo 'Forbidden';
			return;
		}

		if (!$this->model_extension_feed_sync_1c->acquireLock()) {
			echo 'locked';
			return;
		}

		try {
			$this->model_extension_feed_sync_1c->recoverStuckJobs();

			$job = $this->model_extension_feed_sync_1c->getPendingJob();

			if (!$job) {
				echo 'idle';
				return;
			}

			$job_id = (int)$job['job_id'];

			if (!$this->model_extension_feed_sync_1c->markRunning($job_id)) {
				echo 'busy';
				return;
			}

			$ok = $this->runSync($job_id);
			echo $ok ? ('done:' . $job_id) : ('error:' . $job_id);
		} catch (Throwable $e) {
			if (!empty($this->sync_job_id)) {
				$this->model_extension_feed_sync_1c->markError(
					$this->sync_job_id,
					$e->getMessage(),
					$this->sync_stages,
					$this->sync_stats
				);
			} else {
				$job = isset($job_id) ? $job_id : 0;
				if ($job) {
					$this->model_extension_feed_sync_1c->markError($job, $e->getMessage());
				}
			}
			$this->log->write('sync_1c worker error: ' . $e->getMessage());
			echo 'error';
		} finally {
			$this->model_extension_feed_sync_1c->releaseLock();
		}
	}

	private function syncStageStart($name) {
		$this->syncStageEnd();
		$this->sync_stage_name = $name;
		$this->sync_stage_started = microtime(true);
	}

	private function syncStageEnd() {
		if ($this->sync_stage_name === '' || !$this->sync_stage_started) {
			return;
		}

		$sec = round(microtime(true) - $this->sync_stage_started, 2);
		$this->sync_stages[$this->sync_stage_name] = $sec;

		if ($this->sync_job_id) {
			$this->load->model('extension/feed/sync_1c');
			$this->model_extension_feed_sync_1c->saveStages($this->sync_job_id, $this->sync_stages);
		}

		$this->log->write('sync_1c stage ' . $this->sync_stage_name . ': ' . $sec . 's');
		$this->sync_stage_name = '';
		$this->sync_stage_started = 0;
	}

	public function runSync($job_id = 0) {
		set_time_limit(0);
		ini_set( 'memory_limit', '1024M');
		ignore_user_abort(true);

		$this->sync_job_id = (int)$job_id;
		$this->sync_stats = array('updated' => 0, 'created' => 0, 'disabled' => 0, 'skipped' => 0);
		$this->sync_stages = array();
		$this->sync_stage_name = '';
		$this->sync_stage_started = 0;
		$this->sync_diff = array(
			'manufacturer' => 0,
			'name' => 0,
			'description' => 0,
			'tag' => 0,
			'price_base' => 0,
			'price_special' => 0,
			'reward' => 0,
			'attributes' => 0,
			'status' => 0,
			'categories' => 0,
			'related' => 0,
			'quantity' => 0,
			'sol_options' => 0,
		);
		$this->sync_diff_samples = array();

		$this->load->model('catalog/product');
		$this->load->model('extension/feed/sync_1c');
$this->log->write('обновление началось');
		$this->syncStageStart('download');
		//айди товаров бустеров
		$buster_products = $this->getProducts($this->buster_category_id);
		$this->buster_products = array_diff($buster_products, [$this->nikotin_id]);

		$this->sol_products = $this->getProducts($this->sol_category_id);
/*		
		$sol = $this->getProducts($this->sol_category_id);
		$triger = $this->getProducts(4509);
		
		$this->sol_products = array_merge($sol,$triger);
*/
		$file = DIR_LOGS . 'sol.products';
		file_put_contents($file, json_encode($this->sol_products));
		
		$nikotin_info = $this->model_catalog_product->getProduct($this->nikotin_id);
		//цена никотина
		if (!is_null($nikotin_info['special']) && (float)$nikotin_info['special'] >= 0) {
			$this->nikotin_price = (float)$nikotin_info['special'];
		} else {
			$this->nikotin_price = (float)$nikotin_info['price'];
		}
		
		$sklad = 3; //id склада
		$sklad2 = 5; //id  второго склада
		$cena = 'ce840199-168f-11ee-aac5-000c29a71cec'; 
		$old_cena = '0ee0bfc0-4026-11ee-a4a5-000c298c303a';
		
	//	$two_percent = array('3cf846a4-cfd3-11ed-aab7-000c29a71cec','680d258e-ebbf-11ec-aa97-000c292a686a','fd501dba-ebbe-11ec-aa97-000c292a686a');
	//	$two_percent = array('1099','1122','1271');
		$two_percent = array('1099');
		
		$file_path = DIR_DOWNLOAD . 'importfile.json';
		$img_path = DIR_DOWNLOAD . 'importfile.json';
		
		$url = 'https://getvape.com.ua/nom_new.json';
		
//		$link = 'http://5.9.8.211:8089/VPR_UNF/hs/api/0/nomenclature/?auth_token=3698024';
//		$link = 'http://37.27.98.208/site_retail/hs/api/0/nomenclature/?auth_token=3698024';
		$link = 'http://vaporstlgrm.biz.ua/site_retail/hs/api/0/nomenclature/?auth_token=3698024';

		
		/*
		if (filter_var($url, FILTER_VALIDATE_URL)) {
			$file_content = file_get_contents($url);
			file_put_contents($file_path, $file_content);
		} else {
			echo 'Не удалось загрузить файл';
		}		
		*/
		
			stream_context_set_default( [
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false,
				],
			]);
	

			
				$ch = curl_init($link);
				$fp = fopen($img_path, "wb");
				if ($fp) {
					$options = array(CURLOPT_FILE => $fp,
						CURLOPT_HEADER => 0,
						CURLOPT_FOLLOWLOCATION => 1,
						CURLOPT_TIMEOUT => 1000,
					);
					curl_setopt_array($ch, $options);
					curl_exec($ch);
					curl_close($ch);
					fclose($fp);
				}



		$fileData = file_get_contents($file_path);
		$object = json_decode($fileData, true);
		
		if (!$object) {
			if ($this->sync_job_id) {
				$this->syncStageEnd();
				$this->model_extension_feed_sync_1c->markError($this->sync_job_id, 'Нет данных из 1С', $this->sync_stages, $this->sync_stats);
			}
			echo 'Нет данных';
			return false;
		}
$this->log->write('файл загружен');
		$this->syncStageStart('attributes');
		$result = $this->loadAtributes();

		if (!empty($result)) {
			foreach ($result as $row) {
				$this->attributes[$row['guid']] = $row['attribute_id'];
				$this->attributesnames[$row['guid']] = $row['name'];
			}
		}
				
		$result = $this->loadAtributeValues();

		if (!empty($result)) {
			foreach ($result as $row) {
				$this->attributValues[$row['guid']] = $row['attribute_value_id'];
				$this->attributValuesNames[$row['guid']] = $row['name'];
			}
		}
		
		$result = $this->loadManufacturers();

		if (!empty($result)) {
			foreach ($result as $row) {
				$this->manufacturers[$row['guid']] = $row['manufacturer_id'];
			}
		}

		//обработка характеристик
		foreach ($object[0]['PROPERTYS'] as $data) {

			if (!isset($this->attributes[$data['GUID_Property']])) {
				$attribute_id = $this->AddAtribute($data);
				$this->attributes[$data['GUID_Property']] = $attribute_id;
				
			} else {
				$attribute_id = $this->attributes[$data['GUID_Property']];
			}
			if ($this->attributes && isset($this->attributes[$data['GUID_Property']]) && $this->attributesnames[$data['GUID_Property']] != $data['Name_Property'] ) {
				$this->updateAtributename($this->attributes[$data['GUID_Property']], $data['Name_Property']);
			}
			if (isset($data['Values']) && $data['Values']) {
				foreach ($data['Values'] as $value) {
					if (!$this->attributValues || !isset($this->attributValues[$value['GUID_Value']])) {
						$value_id = $this->AddAtributeValue($attribute_id,$value);
						$this->attributValues[$value['GUID_Value']] = $value_id;
						$this->attributValuesNames[$value['GUID_Value']] = $value['Name_Value'];
					} else {
						$value_id = $this->attributValues[$value['GUID_Value']];
					}
					if ($this->attributValues && isset($this->attributValues[$value['GUID_Value']]) && $this->attributValuesNames[$value['GUID_Value']] != $value['Name_Value'] ) {
						$this->updateAtributeValueName($value);
					}
				}
				if ($data['GUID_Property'] == '676b6251-f783-11ee-a4a8-000c298c303a') {
					foreach ($data['Values'] as $value) {
						if (!isset($this->manufacturers[$value['GUID_Value']])) {		//добавляем бренд если его нет
							$manufacturer_id = $this->Addmanufacturer($value);
							$this->manufacturers[$value['GUID_Value']] = $manufacturer_id;
						}
					}
				}				
			}
		}

$this->log->write('обработаны характеристики');		
		$result = $this->loadWarehouses();
		if (!empty($result)) {
			foreach ($result as $row) {
				$this->warehouses[$row['guid']] = $row['location_id'];
				$this->warehousesnames[$row['guid']] = $row['name'];
			}
		}		
		//обработка складов
		foreach ($object[1]['STORAGES'] as $stock) {

				if (!array_key_exists($stock['GUID_Storage'], $this->warehouses)) {
					$warehouse_id = $this->addwarehouse($stock);
					$this->warehouses[$stock['GUID_Storage']] = $warehouse_id;
					$this->warehousesnames[$stock['GUID_Storage']] = $stock['Storage_Name'];
				}

		}
$this->log->write('обработаны склады');
		$this->syncStageStart('goods');
		$this->preloadSyncMaps();
		$this->fordel = array();
		if (!empty($this->skuProducts)) {
			$this->fordel = $this->skuProducts;
		}

		$goods_processed = 0;

		//обработка товаров
		foreach ($object[3]['GOODS'] as $data) {
			if (isset($data['Items']) && $data['Items']) {
				$category_info = $this->getcategoryid($data['GUID_Group']);
				if ($category_info) {
					$clean_name = preg_replace('/ {2,}/', ' ', trim($data['Name_Group']));
					if ($category_info['name'] != $clean_name) {
						$this->updatecategoryname($category_info['category_id'],$clean_name);
					}
					$main_category_id = $category_info['category_id'];
				} else {
						
					$category_data = array(
						'guid' => $data['GUID_Group'],
						'sort_order' => 0,
						'parent_id' => 0,
						'top' => 1,
						'status' => 1,
						'noindex' => 1,
						'column' => 1,
						'category_description' => array(
							1 => array(
								'name' => $data['Name_Group'],
								'meta_title' => '',
								'meta_keyword' => '',
								'meta_description' => '',
								'description' => '',
								'meta_title' => '',
								'meta_h1' => ''
							)
						),
						'category_seo_url' =>  array(
									0 => array(
											1 => $this->translit($data['Name_Group']),
										)		
								),
						'category_store' => array(
							0
						),
					);
					$main_category_id = $this->addcategory($category_data);
				}
			}
			if ($data['Items']) {
				foreach ($data['Items'] as $subcategory) {
					if (isset($subcategory['SubItems']) && $subcategory['SubItems']) {
						$category_info = $this->getcategoryid($subcategory['GUID_Item']);
						if ($category_info) {
							$clean_name = preg_replace('/ {2,}/', ' ', trim($subcategory['Name_Item']));
							if ($category_info['name'] != $clean_name) {
								$this->updatecategoryname($category_info['category_id'],$clean_name);
							}
							$this->updatecategoryparent($category_info['category_id'],$main_category_id);
							$second_category_id = $category_info['category_id'];
						} else {
								
							$category_data = array(
								'guid' => $subcategory['GUID_Item'],
								'sort_order' => 0,
								'parent_id' => $main_category_id,
								'top' => 0,
								'status' => 1,
								'noindex' => 1,
								'column' => 1,
								'category_description' => array(
									1 => array(
										'name' => $subcategory['Name_Item'],
										'meta_title' => '',
										'meta_keyword' => '',
										'meta_description' => '',
										'description' => '',
										'meta_title' => '',
										'meta_h1' => ''
									)
								),
								'category_seo_url' =>  array(
											0 => array(
													1 => $this->translit($subcategory['Name_Item']),
												)		
										),
								'category_store' => array(
									0
								),
							);
							$second_category_id = $this->addcategory($category_data);
						}
					}
					if (isset($subcategory['SubItems']) && $subcategory['SubItems']) {
						$product_types = array();
						foreach ($subcategory['SubItems'] as $product) {
							$changed = false;
							$product_info = $this->getproductid($product['GUID_SubItem']);
							
							$product_price = 0;
							$old_price = 0;
							$special = 0;
							$product_special = array();
							
							if (isset($product['Prices']) && $product['Prices']) {
								foreach ($product['Prices'] as $price) {
									if ($price['GUID_PriceType'] == $cena) {
										$product_price = $price['Price'];
									}
									if ($price['GUID_PriceType'] == $old_cena) {
										$old_price = $price['Price'];
									}									
								}
							
								if ($old_price && ($old_price > 0) && $old_price > $product_price) {		
									$special = $product_price;
									$product_special[] = array(
										'customer_group_id' => 1,
										'priority' => 1,
										'date_start' => '',
										'date_end' => '',
										'price' => $special,
									);
									$product_price = $old_price;
								}							
							}
							
							$product_attribute = array();
							$manufacturer_id = '';
							if (isset($product['Properties']) && $product['Properties']) {
								foreach ($product['Properties'] as $property) {	
									$product_attribute[] = array(
										'attribute_id' => $this->attributes[$property['GUID_Property']],
										'product_attribute_description' => array(
											1 => array(
												'text' => $this->attributValuesNames[$property['GUID_Value']],
											),
										)
									);
									
									if ($property['GUID_Property'] == '676b6251-f783-11ee-a4a8-000c298c303a') {
										$manufacturer_id = $this->manufacturers[$property['GUID_Value']];
									}
								}
							}
							
							$quantity = 0;
							if (isset($product['Stock']) && $product['Stock']) {
								$stockarray = array();
								foreach ($product['Stock'] as $stock) {
									if ($this->warehouses[$stock['GUID_Storage']] == $sklad || $this->warehouses[$stock['GUID_Storage']] == $sklad2) {
										$stockarray[] = 'Склад getvape.com.ua';
									} else {
										if ($this->warehousesnames[$stock['GUID_Storage']] != 'hide') {
											$stockarray[] = $this->warehousesnames[$stock['GUID_Storage']];
										}
									}
								}
								$stockarray = array_unique($stockarray);
								$product_attribute[] = array(
									'attribute_id' => 42,
									'product_attribute_description' => array(
										1 => array(
											'text' => implode(' | ', $stockarray),
										),
									)
								);								
							}
								
							if (isset($product['Name_SubItem']) && $product['Name_SubItem']) {
								if ($product_info) {
									$product_id = (int)$product_info['product_id'];
									$changed = false;

									if ((int)$product_info['manufacturer_id'] != (int)$manufacturer_id) {
										$this->updatemanufacturer($manufacturer_id, $product_id);
										$this->bumpDiff('manufacturer', $product_id, (int)$product_info['manufacturer_id'] . '=>' . (int)$manufacturer_id);
										$changed = true;
									}
									$clean_name = preg_replace('/ {2,}/', ' ', trim($product['Name_SubItem']));
									if ($product_info['name'] != $clean_name) {
										$this->updateproductname($product_id, $clean_name);
										$this->bumpDiff('name', $product_id, mb_substr((string)$product_info['name'], 0, 40) . '=>' . mb_substr($clean_name, 0, 40));
										$changed = true;
									}
									
									if ($product['Detail_SubItem'] && (!$product_info['description'] || $product_info['description'] == '<p><br></p>')) { 
										$this->updateproductdescription($product_id, $product['Detail_SubItem']);
										$this->bumpDiff('description', $product_id);
										$changed = true;
									}
									
									if (isset($product['Tags_SubItem']) && $product['Tags_SubItem'] && (!isset($product_info['tag']) || $product_info['tag'] != $product['Tags_SubItem'])) {
										$this->updateproducttag($product_id, $product['Tags_SubItem']);
										$this->bumpDiff('tag', $product_id);
										$changed = true;
									}

									$new_special_price = $special ? $special : $product_price;
									$price_diff = $this->detectPriceDiff($product_id, $product_info, $product_price, $special);

									if ($price_diff['base'] || $price_diff['special']) {
										$this->updateproductprice($product_id, $product_price, $product_special, $new_special_price);
										if ($price_diff['base']) {
											$this->bumpDiff('price_base', $product_id, $price_diff['base_detail']);
										}
										if ($price_diff['special']) {
											$this->bumpDiff('price_special', $product_id, $price_diff['special_detail']);
										}
										$changed = true;
									}

									$product_reward = array(
										1 => round(($special ? $special : $product_price) * (in_array($main_category_id, $two_percent) ? 0.02 : 0.05)),
									);
									$bonus = in_array($main_category_id, $two_percent) ? 2 : 5;
									$old_bonus = isset($this->productBonus[$product_id]) ? (int)$this->productBonus[$product_id] : -1;
									$old_reward = isset($product_info['reward_points']) ? (int)$product_info['reward_points'] : -1;
									// Не гоняем reward из-за сайт-скидки в product_special; только если 1С-цена/bonus реально менялись
									if ($price_diff['base'] || $price_diff['special'] || $old_bonus !== (int)$bonus) {
										$this->updateproductreward($product_id, $product_reward, $bonus);
										$this->productBonus[$product_id] = (int)$bonus;
										$this->bumpDiff('reward', $product_id, 'bonus ' . $old_bonus . '=>' . (int)$bonus . ', pts ' . $old_reward . '=>' . (int)$product_reward[1]);
										$changed = true;
									}

									$attr_sig = $this->buildAttributesSignature($product_attribute);
									$old_attr_sig = isset($this->productAttributesSig[$product_id]) ? $this->productAttributesSig[$product_id] : null;
									if ($old_attr_sig !== $attr_sig) {
										$this->updateproductattrbutes($product_id, $product_attribute);
										$this->productAttributesSig[$product_id] = $attr_sig;
										$this->bumpDiff('attributes', $product_id, substr((string)$old_attr_sig, 0, 8) . '=>' . substr($attr_sig, 0, 8));
										$changed = true;
									}
									
									if (!$product_info['status']) {
										$this->onproduct($product_id);
										$this->bumpDiff('status', $product_id);
										$changed = true;
									}

									$categories = array($second_category_id, $main_category_id, 1526);
									$cat_sig = $this->buildCategoriesSignature($second_category_id, $categories);
									$old_cat_sig = isset($this->productCategoriesSig[$product_id]) ? $this->productCategoriesSig[$product_id] : null;
									if ($old_cat_sig !== $cat_sig) {
										$this->updateproductcategories($product_id, $second_category_id, $categories);
										$this->productCategoriesSig[$product_id] = $cat_sig;
										$this->bumpDiff('categories', $product_id, (string)$old_cat_sig . '=>' . $cat_sig);
										$changed = true;
									}

									
								} else { 
									
									$product_description = array(
										1 => array(
											'name' => $product['Name_SubItem'],
											'description' => $product['Detail_SubItem'],
											'meta_keyword' => '',
											'meta_description' => '',
											'tag' => (isset($product['Tags_SubItem'])?$product['Tags_SubItem']:''),
											'meta_title' => '',
											'meta_h1' => '',
										),
									);

									$product_data = array(
										'guid' => $product['GUID_SubItem'],
										'product_description' => $product_description,
										'product_special' => $product_special,
										'product_store' => array(0),
										'main_category_id' => $second_category_id,
										'product_category' => array(
											$second_category_id, $main_category_id, 1526
										),
										'product_attribute' => $product_attribute,
										'model' => $product['Vendor_Code'],
										'image' => '',
										'sku' => $product['Vendor_Code'],
										'product_seo_url' =>  array(
													0 => array(
														1 => $this->translit($product['Name_SubItem'] . '-' . $product['Vendor_Code']),
													)
												),
										'product_reward' =>  array(
														1 => ($special?$special:$product_price) * (in_array($main_category_id,$two_percent)?0.02:0.05),
												),
										'upc' => '',
										'ean' => '',
										'jan' => '',
										'isbn' => '',
										'mpn' => '',
										'product_type' => '',
										'code' => 'UAH',
										'mpn' => '',
										'location' => '',
										'quantity' => 0,
										'minimum' => 1,
										'subtract' => 1,
									//	'stock_status_id' => ($offer['available'] == 'true') ? 7 : 8,
										'stock_status_id' => 5,
										'date_available' => date('Y-m-d'),
										'manufacturer_id' => $manufacturer_id,
										'shipping' => 1,
										'price' => $product_price,
										'points' => ($special?$special:$product_price),
										'weight' => '',
										'weight_class_id' => '',
										'length' => '',
										'width' => '',
										'height' => '',
										'length_class_id' => '',
										'status' => '1',
										'tax_class_id' => '',
										'sort_order' => '',
										'product_image' => array(),
										'product_tag' => array(), // for ocstore 1.5.1
										'noindex' => '1',
									);
									
									$product_id = $this->addproduct($product_data);
									$this->sync_stats['created']++;
									$changed = true;
									$this->applySiteDiscountIfMatched($product_id, $product_price);
									if ($main_category_id == $this->sol_category_id) {
										array_push($this->sol_products, $product_id);
									}
									if (!empty($product['Vendor_Code'])) {
										$this->modelsByCode[$product['Vendor_Code']] = (int)$product_id;
									}
								}
							} else {
								continue;
							}

							$related_sig = '';
							if (isset($product['Relateds']) && $product['Relateds']) {
								$related_sig = $this->buildRelatedSignature($product['Relateds']);
								$old_related_sig = isset($this->productRelatedSig[$product_id]) ? $this->productRelatedSig[$product_id] : null;
								if ($old_related_sig !== $related_sig) {
									$this->updateRelateds($product_id, $product['Relateds']);
									$this->productRelatedSig[$product_id] = $related_sig;
									if (!empty($product_info)) {
										$this->bumpDiff('related', $product_id, (string)$old_related_sig . '=>' . $related_sig);
									}
									$changed = true;
								}
							} else {
								if (!empty($this->productRelatedSig[$product_id])) {
									$this->deleteRelateds($product_id);
									$this->productRelatedSig[$product_id] = '';
									if (!empty($product_info)) {
										$this->bumpDiff('related', $product_id, 'cleared');
									}
									$changed = true;
								}
							}
							
							
							$quantitydata = array();
							if (isset($product['Stock']) && $product['Stock']) {
								foreach ($product['Stock'] as $stock) {
									$quantitydata[$this->warehouses[$stock['GUID_Storage']]] = $stock['Qtty'];
								}
							}
							if (!$this->quantitiesAreEqual($product_id, $quantitydata)) {
								$this->updatequantity($product_id, $quantitydata);
								$this->productQuantities[$product_id] = $this->normalizeQuantityMap($quantitydata);
								if (!empty($product_info)) {
									$this->bumpDiff('quantity', $product_id);
								}
								$changed = true;
							}

							// Sol: финальная цена = база 1С + глицерин/никотин (абсолютно, не += каждый прогон) 
							if (!empty($product_info) && in_array($product_id, $this->sol_products)) {
								$sol_addon = $this->getSolAddon($product_id);

								if ($sol_addon['volume'] > 0) {
									$sol_fix = $this->updateOptions(
										$product_id,
										$product_price,
										$special,
										$sol_addon['glicerin'],
										$sol_addon['nikotin'],
										$this->buster_products,
										isset($product['Vendor_Code']) ? $product['Vendor_Code'] : ''
									);

									if ($sol_fix) {
										$this->bumpDiff('sol_options', $product_id, $sol_fix['detail']);
										$changed = true;
									}
								}
							}

							if (!empty($product_info)) {
								if ($changed) {
									$this->sync_stats['updated']++;
								} else {
									$this->sync_stats['skipped']++;
								}
							}
							
							if (isset($product['Properties']) && $product['Properties']) {
								foreach ($product['Properties'] as $property) {	
									$product_types[] = array(
										'category_id' => $second_category_id,
										'product_id' => $product_id,
										'attribute_id' => $this->attributes[$property['GUID_Property']],
										'attribute_text' => $this->attributValuesNames[$property['GUID_Value']],
									);
								}
								
							}
							unset($this->fordel[$product['GUID_SubItem']]);
							$goods_processed++;

							if ($goods_processed % 200 === 0) {
								$this->sync_stages['goods_progress'] = $goods_processed;
								if ($this->sync_job_id) {
									$this->model_extension_feed_sync_1c->saveStages($this->sync_job_id, $this->sync_stages);
									$this->model_extension_feed_sync_1c->touchLock();
								}
							}
							
						}
						$this->setTypes($second_category_id,$product_types);
						$this->cache->delete('product*');
					}
					
					
				}
			}
		}
		if (isset($object[3]['GOODS'])) {
			foreach ($this->fordel as $key=>$value){
				$this->offproduct($value);
				$this->sync_stats['disabled']++;
				unset($this->fordel[$key]);
			}	
		}

		unset($this->sync_stages['goods_progress']);
		$this->flushDiffStats();

$this->syncStageStart('cache');
//сброс кэша
$removed = $this->cache->delete('product*');
$this->log->write('Удалено ключей товаров: '.  $removed);	
$removed = $this->cache->delete('seopro*');
$this->log->write('Удалено ключей сеопро: '.  $removed);
$this->cache->delete('module*');

$lock_table = $this->db->query("SHOW TABLES LIKE '" . str_replace('_', '\_', DB_PREFIX . 'getvape_znyzhky_lock') . "'");
if ($lock_table->num_rows) {
	$this->load->model('extension/module/getvape_znyzhky');
	$this->model_extension_module_getvape_znyzhky->clearFrontCaches();
}

$this->syncStageStart('ocfilter');
// OCFilter copy start
$this->load->controller('extension/module/ocfilter/copy', [
  'copy_attribute' => 1, // Копіювати атрибути
  'copy_group_as_attribute' => 0, // Групи атрибутів як фільтри
  'copy_attribute_id_exclude' => 1, // Дані для копіювання
  'copy_attribute_group_id_exclude' => 1, // Дані для копіювання
  'copy_attribute_category_id_exclude' => 1, // Дані для копіювання
  'copy_filter' => 0, // Копіювати стандартні фільтри
  'copy_option' => 1, // Копіювати опції товарів
  'copy_option_in_stock' => 0, // Тільки в наявності
  'copy_type' => 'checkbox', // Тип скопійованих фільтрів
  'copy_dropdown' => 0, // Помістити в список, що випадає
  'copy_status' => 1, // Статус скопійованих фільтрів
  'copy_truncate' => 0, // Очистити існуючі фільтри OCFilter
  'copy_category' => 1, // Прив'язати фільтри до категорій
  'copy_cron_wget' => 0, // Команда для виклику по cron (планувальник)
  'copy_value_separator' => [], // 
  'copy_attribute_id' => [], // 
  'copy_attribute_group_id' => [], // 
  'copy_attribute_category_id' => [], // 
]);
// OCFilter copy end		

$this->syncStageStart('menu_cache');
//Обновление кэша меню
$this->load->controller('extension/feed/setcachecategory');

//обновление топпродаж
//$this->load->controller('extension/feed/setsales');

$this->syncStageStart('variants');
//Обновление вариантов вкуса
$this->load->controller('extension/feed/updatevarints');


		$file = DIR_LOGS . 'cache.header.special' . (int)$this->config->get('config_language_id');
		$total_special = $this->model_catalog_product->getTotalProductSpecials();
		file_put_contents($file, json_encode($total_special));
		$this->syncStageEnd();
		$this->log->write('Данные обновлены');

		if ($this->sync_job_id) {
			$this->model_extension_feed_sync_1c->markDone($this->sync_job_id, $this->sync_stats, $this->sync_stages);
		}

		echo 'Данные обновлены';
		return true;
	}

	public function updateproductcategories($product_id, $main_category_id, $categories) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		if (isset($categories)) {
			foreach ($categories as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		} 
		
		if (isset($main_category_id) && $main_category_id > 0) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$main_category_id . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$main_category_id . "', main_category = 1");
				} elseif (isset($categories[0])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET main_category = 1 WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$categories[0] . "'");
		}

		$this->applySiteDiscountIfMatched($product_id);
	}
	
	public function setTypes($category_id,$product_types) {

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_types WHERE category_id = '" . (int)$category_id . "'");

		foreach ($product_types as $type) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_types SET category_id = '" . (int)$category_id . "', product_id = '" . (int)$type['product_id'] . "', attribute_id = '" . (int)$type['attribute_id'] . "', attribute_text = '" .  $this->db->escape($type['attribute_text']) . "'");
		}

	}
	
	public function AddAtributeValue($attribute_id,$data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_value_to_1c SET attribute_id = '" . (int)$attribute_id . "', guid = '" . $this->db->escape($data['GUID_Value']) . "', name = '" . $this->db->escape(isset($data['Name_ValueColor'])?$data['Name_ValueColor']:trim($data['Name_Value'])) . "'");

		$value_id = $this->db->getLastId();

		return $value_id;
	}
	
	public function updateAtributeValueName($data) {
		$this->db->query("UPDATE " . DB_PREFIX . "attribute_value_to_1c SET name = '" . $this->db->escape(isset($data['Name_ValueColor'])?$data['Name_ValueColor']:trim($data['Name_Value'])) . "' WHERE guid = '" . $this->db->escape($data['GUID_Value']) . "'");
	}
	
	public function loadAtributeValues() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_value_to_1c");

		return $query->rows;
	}
	
	public function updateAtributename($attribute_id, $name) {
		$this->db->query("UPDATE " . DB_PREFIX . "attribute_description SET name = '" . $this->db->escape($name) . "' WHERE attribute_id = '" . (int)$attribute_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
	}
	
	public function AddAtribute($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "attribute SET attribute_group_id = 1, sort_order = 0");

		$attribute_id = $this->db->getLastId();

		$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = 1, name = '" . $this->db->escape($data['Name_Property']) . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_to_1c SET attribute_id = '" . (int)$attribute_id . "', guid = '" . $this->db->escape($data['GUID_Property']) . "'");

		return $attribute_id;
	}
	
	public function loadAtributes() {
		$query = $this->db->query("SELECT at.guid, ad.name, ad.attribute_id FROM " . DB_PREFIX . "attribute_description ad LEFT JOIN " . DB_PREFIX . "attribute_to_1c at ON (ad.attribute_id = at.attribute_id)");

		return $query->rows;
	}
	
	public function loadManufacturers() {
		$query = $this->db->query("SELECT mc.guid, m.name, m.manufacturer_id FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_1c mc ON (m.manufacturer_id = mc.manufacturer_id)");

		return $query->rows;
	}

	public function Addmanufacturer($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape(trim($data['Name_Value'])) . "', sort_order = 0, noindex = 1");

		$manufacturer_id = $this->db->getLastId();

		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_description SET manufacturer_id = '" . (int)$manufacturer_id . "', language_id = 1");
		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_1c SET manufacturer_id = '" . (int)$manufacturer_id . "', guid = '" . $this->db->escape($data['GUID_Value']) . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = 0");
		$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = 0, language_id = 1, query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($this->translit(trim($data['Name_Value']))) . "'");
		
		return $manufacturer_id;
	}
	
	public function updatequantity($product_id,$quantitydata) {
		$online = $this->getOnlineWarehouseIds();

		$totalquantity = 0;
		$totalquantity_sklad = 0;
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_quantity WHERE product_id = '" . (int)$product_id . "'");
		foreach ($quantitydata as $warehouse_id => $quantity) {
			$warehouse_id = (int)$warehouse_id;
			$quantity = (int)$quantity;

			if (isset($online[$warehouse_id])) {
				$totalquantity += $quantity;
			} else {
				$totalquantity_sklad += $quantity;
			}

			// Офис не показываем в "забрати в магазині"
			if ($warehouse_id != $this->sklad) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_quantity SET product_id = '" . (int)$product_id . "', warehouse_id = '" . (int)$warehouse_id . "', quantity = '" . (int)$quantity . "'");
			}
		}
		
		$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$totalquantity . "', quantity_sklad = '" . (int)$totalquantity_sklad . "' WHERE product_id = '" . (int)$product_id . "'");
	}

	private function ensureOnlineOrderColumn() {
		static $checked = false;

		if ($checked) {
			return;
		}

		$checked = true;

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "location` LIKE 'online_order'");

		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "location` ADD COLUMN `online_order` TINYINT(1) NOT NULL DEFAULT 0 AFTER `guid`, ADD INDEX `idx_location_online_order` (`online_order`)");

			// Fallback: старые 4 склада, пока админ не нажмет "проставити відкриті"
			$this->db->query("UPDATE `" . DB_PREFIX . "location` SET online_order = 1 WHERE location_id IN (230, 77, 2, 1)");
		}
	}

	private function getOnlineWarehouseIds() {
		if ($this->online_warehouses !== null) {
			return $this->online_warehouses;
		}

		$this->ensureOnlineOrderColumn();

		$this->online_warehouses = array();

		$query = $this->db->query("SELECT location_id FROM `" . DB_PREFIX . "location` WHERE online_order = 1");

		foreach ($query->rows as $row) {
			$this->online_warehouses[(int)$row['location_id']] = true;
		}

		// На всякий случай офис всегда в онлайн
		$this->online_warehouses[(int)$this->sklad] = true;

		return $this->online_warehouses;
	}
	
	public function loadWarehouses() {
		$this->ensureOnlineOrderColumn();

		$query = $this->db->query("SELECT DISTINCT guid, name, location_id FROM " . DB_PREFIX . "location");

		return $query->rows;
	}
	
	public function addwarehouse($data) {
		$this->ensureOnlineOrderColumn();

		$this->db->query("INSERT INTO " . DB_PREFIX . "location SET name = '" . $this->db->escape($data['Storage_Name']) . "', guid = '" . $this->db->escape($data['GUID_Storage']) . "', online_order = 0");
	
		return $this->db->getLastId();
	}
	
	public function getcategoryid($GUID) {
		if ($GUID !== '' && isset($this->categoriesByGuid[$GUID])) {
			return $this->categoriesByGuid[$GUID];
		}

		$query = $this->db->query("SELECT DISTINCT c.category_id, c.parent_id, cd.name, c1s.guid FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_1c c1s ON (c.category_id = c1s.category_id) WHERE c1s.guid = '" . $this->db->escape($GUID) . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		if ($query->num_rows) {
			$this->categoriesByGuid[$GUID] = $query->row;
		}

		return $query->row;
	}
	
	public function updatecategoryname($category_id, $name) {
		$this->db->query("UPDATE " . DB_PREFIX . "category_description SET name = '" . $this->db->escape($name) . "' WHERE category_id = '" . (int)$category_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
	}

	public function updatecategoryparent($category_id,$parent_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "category SET parent_id = '" . (int)$parent_id . "' WHERE category_id = '" . (int)$category_id . "'");

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE path_id = '" . (int)$category_id . "' ORDER BY level ASC");

		if ($query->rows) {
			foreach ($query->rows as $category_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' AND level < '" . (int)$category_path['level'] . "'");

				$path = array();

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_path['category_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', level = '" . (int)$level . "'");
		}
	}
	
	public function addcategory($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', noindex = '" . (int)$data['noindex'] . "', date_modified = NOW(), date_added = NOW()");

		$category_id = $this->db->getLastId();

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_h1 = '" . $this->db->escape($value['meta_h1']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		
		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape(trim($keyword)).'-'.$category_id . "'");
					}
				}
			}
		}
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_1c SET category_id = '" . (int)$category_id . "', guid = '" . $this->db->escape($data['guid']) . "'");

		$this->categoriesByGuid[$data['guid']] = array(
			'category_id' => (int)$category_id,
			'parent_id' => (int)$data['parent_id'],
			'name' => isset($data['category_description'][1]['name']) ? $data['category_description'][1]['name'] : '',
			'guid' => $data['guid'],
		);

		$this->cache->delete('category*');
		
		if($this->config->get('config_seo_pro')){		
		$this->cache->delete('seopro*');
		}

		return $category_id;
	}

	public function getproductid($GUID) {
		if ($GUID !== '' && isset($this->productsByGuid[$GUID])) {
			return $this->productsByGuid[$GUID];
		}

		$query = $this->db->query("SELECT DISTINCT p.product_id, p.price, p.status, p.manufacturer_id, p.quantity, p.quantity_sklad, p.bonus, pd.name, pd.description, pd.tag, pt.guid, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' LIMIT 1) AS reward_points FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_1c pt ON (p.product_id = pt.product_id) WHERE pt.guid = '" . $this->db->escape($GUID) . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		if ($query->num_rows) {
			$this->productsByGuid[$GUID] = $query->row;
			$this->skuProducts[$GUID] = (int)$query->row['product_id'];
		}

		return $query->row;
	}
	
	public function updateproductname($product_id, $name) {
		$this->db->query("UPDATE " . DB_PREFIX . "product_description SET name = '" . $this->db->escape($name) . "' WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
	}
	
	public function updateproductdescription($product_id, $description) {
		$this->db->query("UPDATE " . DB_PREFIX . "product_description SET description = '" . $this->db->escape($description) . "' WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
	}
	
	public function updateproducttag($product_id, $tag) {
		$this->db->query("UPDATE " . DB_PREFIX . "product_description SET tag = '" . $this->db->escape($tag) . "' WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
	}
	
	public function updateproductprice($product_id, $price, $special,$special_price) {
		$module = false;
		$winner = null;

		$lock_table = $this->db->query("SHOW TABLES LIKE '" . str_replace('_', '\_', DB_PREFIX . 'getvape_znyzhky_lock') . "'");

		if ($lock_table->num_rows) {
			$this->load->model('extension/module/getvape_znyzhky');
			$module = true;
			$winner = $this->model_extension_module_getvape_znyzhky->findWinningRuleForProduct($product_id);
		}

		if ($winner) {
			// База з 1С + сайт-знижка (у т.ч. новий товар бренду/категорії).
			// Запам'ятовуємо знижку 1С, щоб після закінчення/видалення правила сайту одразу її повернути.
			$has_1c_special = !empty($special) && (float)$special_price > 0 && (float)$special_price < (float)$price;
			$backup = array(
				'backup_price'       => (float)$price,
				'backup_special'     => $has_1c_special ? (float)$special_price : 0,
				'backup_has_special' => $has_1c_special ? 1 : 0
			);

			$this->db->query("UPDATE " . DB_PREFIX . "product SET price = '" . (float)$price . "', points = '" . (float)$price . "' WHERE product_id = '" . (int)$product_id . "'");
			$this->model_extension_module_getvape_znyzhky->applyRuleToProduct($product_id, $winner, $price, $backup);
		} else {
			if ($module) {
				$lock = $this->model_extension_module_getvape_znyzhky->getLock($product_id);

				if ($lock) {
					$this->db->query("DELETE FROM `" . DB_PREFIX . "getvape_znyzhky_lock` WHERE `product_id` = '" . (int)$product_id . "'");
				}
			}

			$this->db->query("UPDATE " . DB_PREFIX . "product SET price = '" . (float)$price . "', points = '" . (float)($special_price) . "' WHERE product_id = '" . (int)$product_id . "'");

			if (!empty($special)) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
				foreach ($special as $product_special) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
				}
			} else {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
			}
		}

		$queryd = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC");

		if ($queryd->num_rows) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
			foreach ($queryd->rows as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price_percent = '" . (float)$product_discount['price_percent'] . "', price = '" . (round($price - ( $price * (float)$product_discount['price_percent'] / 100 ))) . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

	}
	
	public function updatemanufacturer($manufacturer_id, $product_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET manufacturer_id = '" . (int)$manufacturer_id . "' WHERE product_id = '" . (int)$product_id . "'");
		$this->applySiteDiscountIfMatched($product_id);
	}

	/**
	 * Підхопити товар під активне правило сайту (бренд/категорія/товар).
	 */
	private function applySiteDiscountIfMatched($product_id, $price = null) {
		$lock_table = $this->db->query("SHOW TABLES LIKE '" . str_replace('_', '\_', DB_PREFIX . 'getvape_znyzhky_lock') . "'");

		if (!$lock_table->num_rows) {
			return;
		}

		$this->load->model('extension/module/getvape_znyzhky');
		$this->model_extension_module_getvape_znyzhky->applyMatchingRulesForProduct($product_id, $price);
	}

	public function updateproductmodel($product_id, $model) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($model) . "', sku = '" . $this->db->escape($model) . "' WHERE product_id = '" . (int)$product_id . "'");
	}
	
	public function updateproductreward($product_id, $product_reward,$bonus) {
		
			$this->db->query("UPDATE " . DB_PREFIX . "product SET bonus = '" . (int)$bonus . "' WHERE product_id = '" . (int)$product_id . "'");
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
			
			foreach ($product_reward as $customer_group_id => $product_reward) {
				if ((int)$product_reward > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward . "', bonus = '" . (int)$bonus . "'");
				}
			}
	}
	
	public function updateproductattrbutes($product_id, $product_attributes) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
			foreach ($product_attributes as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}

	}
	
	public function addProduct($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', noindex = '" . (int)$data['noindex'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");

		$product_id = $this->db->getLastId();

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_h1 = '" . $this->db->escape($value['meta_h1']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
		
		if (isset($data['main_category_id']) && $data['main_category_id'] > 0) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['main_category_id'] . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['main_category_id'] . "', main_category = 1");
				} elseif (isset($data['product_category'][0])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET main_category = 1 WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['product_category'][0] . "'");
		}

		
		// SEO URL
		if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape(trim($keyword)) . "'");
					}
				}
			}
		}
		
		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
				if ((int)$product_reward > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward . "'");
				}
			}
		}
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_1c SET product_id = '" . (int)$product_id . "', guid = '" . $this->db->escape($data['guid']) . "'");

		$this->cache->delete('product*');
		
		if($this->config->get('config_seo_pro')){		
		$this->cache->delete('seopro*');
		}

		return $product_id;
	}
	
	private function translit($s) {
	  $s = (string) $s; // преобразуем в строковое значение
	  $s = strip_tags($s); // убираем HTML-теги
	  $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
	  $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
	  $s = trim($s); // убираем пробелы в начале и конце строки
	  $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
	  $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>'','і'=>'i',' - '=>'-'));
	  $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
	  $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
	  return $s; // возвращаем результат
	}
	
	public function loadProducts()
	{
		$result = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'product_to_1c`');

		return $result->rows;
	}
	
	public function offproduct($value)
	{
//		$this->db->query("UPDATE " . DB_PREFIX . "product SET status = 0 WHERE product_id = '" . $this->db->escape($value) . "'");
	}
	
	public function onproduct($value)
	{
//		$this->db->query("UPDATE " . DB_PREFIX . "product SET status = 1 WHERE product_id = '" . $this->db->escape($value) . "'");
	}
	
	public function updateIsnew()
	{

		$sql = "SELECT product_id, date_added FROM " . DB_PREFIX . "product WHERE date_added < DATE_SUB(NOW(), INTERVAL 30 DAY) AND date_new = '0000-00-00 00:00:00'  ORDER BY date_added DESC";
		
		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			
			$this->db->query("UPDATE " . DB_PREFIX . "product SET isnew = 0 WHERE product_id = '" . (int)$result['product_id'] . "'");
		}

		$sql = "SELECT product_id, date_new FROM " . DB_PREFIX . "product WHERE date_new < DATE_SUB(NOW(), INTERVAL 30 DAY) AND date_new != '0000-00-00 00:00:00' ORDER BY date_new DESC";
		
		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			
			$this->db->query("UPDATE " . DB_PREFIX . "product SET isnew = 0 WHERE product_id = '" . (int)$result['product_id'] . "'");
		}
		
	}

	public function getProducts($category_id) {
		$product_data = array();
/*		
		$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product_to_category p2c
		LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)
		WHERE category_id = '" . $category_id . "' AND status = 1
		");
*/
		$query = $this->db->query("
		SELECT DISTINCT p.product_id
		FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)
		LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)
		WHERE
		  cp.path_id = '" . $category_id . "'
		  AND p.status = 1
		;");

		
		foreach ($query->rows as $result) {
			$product_data[] = $result['product_id'];
		}
		
		return $product_data;
	}

	public function getTotalGlicerin($category_id) {
		$product_data = array();
		$query = $this->db->query("SELECT SUM(quantity) as total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category pc ON (p.product_id = pc.product_id) WHERE pc.category_id = '" . $category_id . "' AND p.status = 1");
		
		return $query->row['total'];
	}
	
	public function getProductVolume($product_id) {

		$query = $this->db->query("SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . $product_id . "' AND attribute_id = 41 AND language_id = 1");
		
		if ($query->num_rows) {
			return $query->row['text'];
		} else {
			return false;
		}
	}

	/**
	 * Надбавка sol: глицерин + никотин по объему (attr 41).
	 */
	private function getSolAddon($product_id) {
		$volume = (int)$this->getProductVolume($product_id);

		if ($volume <= 0) {
			return array('volume' => 0, 'glicerin' => 0, 'nikotin' => 0);
		}

		if ($volume >= 30) {
			$nikotin = $this->nikotin_price;
		} elseif ($volume >= 15) {
			$nikotin = $this->nikotin_price * 0.7;
		} else {
			$nikotin = $this->nikotin_price * 0.4;
		}

		return array(
			'volume' => $volume,
			'glicerin' => (float)$this->glicerin_price,
			'nikotin' => (float)$nikotin,
		);
	}
	
	/**
	 * Ставит финальную цену = база 1С + addon абсолютно (не price += addon).
	 * @return array|false detail при изменении, иначе false
	 */
	public function updateOptions($product_id, $base_price, $special_1c, $glicerin_price, $nikotin_price_corrected, $buster_products, $vendor_code = '') {
		$this->load->model('catalog/product');

		$product_id = (int)$product_id;
		$base_price = $this->normalizePrice($base_price);
		$special_1c = $this->normalizePrice($special_1c);
		$addon = (float)round((float)$glicerin_price) + (float)round((float)$nikotin_price_corrected);
		$final_price = $this->normalizePrice($base_price + $addon);
		$final_special_1c = ($special_1c > 0 && $special_1c < $base_price)
			? $this->normalizePrice($special_1c + $addon)
			: 0;

		$glicerin_price = (float)round((float)$glicerin_price);
		$nikotin_price_corrected = (float)round((float)$nikotin_price_corrected);

		$current_query = $this->db->query("SELECT price, points, model FROM " . DB_PREFIX . "product WHERE product_id = '" . $product_id . "'");
		$current_price = $current_query->num_rows ? $this->normalizePrice($current_query->row['price']) : 0;
		$model = $vendor_code !== '' ? $vendor_code : ($current_query->num_rows ? (string)$current_query->row['model'] : '');

		$opt_g = $this->db->query("SELECT pov.price FROM " . DB_PREFIX . "product_option po
			INNER JOIN " . DB_PREFIX . "product_option_value pov ON (po.product_option_id = pov.product_option_id)
			WHERE po.product_id = '" . $product_id . "' AND po.option_id = 3 LIMIT 1");
		$opt_n = $this->db->query("SELECT pov.price FROM " . DB_PREFIX . "product_option po
			INNER JOIN " . DB_PREFIX . "product_option_value pov ON (po.product_option_id = pov.product_option_id)
			WHERE po.product_id = '" . $product_id . "' AND po.option_id = 4 LIMIT 1");

		$opt_g_price = $opt_g->num_rows ? $this->normalizePrice($opt_g->row['price']) : null;
		$opt_n_price = $opt_n->num_rows ? $this->normalizePrice($opt_n->row['price']) : null;

		$winner = null;
		$lock = null;
		$lock_row = null;
		$lock_table = $this->db->query("SHOW TABLES LIKE '" . str_replace('_', '\_', DB_PREFIX . 'getvape_znyzhky_lock') . "'");

		if ($lock_table->num_rows) {
			$this->load->model('extension/module/getvape_znyzhky');
			$winner = $this->model_extension_module_getvape_znyzhky->findWinningRuleForProduct($product_id);
			$lock_row = $this->model_extension_module_getvape_znyzhky->getLock($product_id);
			$lock = $winner ? true : $lock_row;
		}

		// Очікувані ціни опцій: при знижці - пропорційно special/kit, інакше повні
		$expected_g = $glicerin_price;
		$expected_n = $nikotin_price_corrected;
		$expected_special = false;

		if ($winner) {
			$expected_special = $this->model_extension_module_getvape_znyzhky->calculateSpecial(
				$final_price,
				$winner['discount_type'],
				$winner['discount_value']
			);
		} elseif ($lock_row) {
			$expected_special = $this->model_extension_module_getvape_znyzhky->calculateSpecial(
				$final_price,
				$lock_row['discount_type'],
				$lock_row['discount_value']
			);
		} elseif ($final_special_1c > 0) {
			$expected_special = $final_special_1c;
		}

		if ($expected_special && $expected_special < $final_price && $final_price > 0) {
			if (!isset($this->model_extension_module_getvape_znyzhky)) {
				$this->load->model('extension/module/getvape_znyzhky');
			}

			$expected_g = $this->model_extension_module_getvape_znyzhky->scaleOptionPrice(
				$glicerin_price,
				$final_price,
				$expected_special
			);
			$expected_n = $this->model_extension_module_getvape_znyzhky->scaleOptionPrice(
				$nikotin_price_corrected,
				$final_price,
				$expected_special
			);
		}

		$need_options = ($opt_g_price === null || !$this->priceEq($opt_g_price, $expected_g)
			|| $opt_n_price === null || !$this->priceEq($opt_n_price, $expected_n));

		// При сайт-lock эталон финала - final_price; special считает правило
		$need_price = !$this->priceEq($current_price, $final_price);

		if (!$need_price && !$need_options && ($winner || $lock)) {
			return false;
		}

		if (!$need_price && !$need_options && !$winner && !$lock) {
			$ps = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_special
				WHERE product_id = '" . $product_id . "'
				AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW()))
				ORDER BY priority ASC, price ASC LIMIT 1");
			$db_special = $ps->num_rows ? $this->normalizePrice($ps->row['price']) : 0;
			$need_special = $final_special_1c > 0
				? !$this->priceEq($db_special, $final_special_1c)
				: ($db_special > 0);

			if (!$need_special) {
				return false;
			}
		}

		$old_price = $current_price;
		$delta = abs($final_price - $old_price);

		$this->db->query("UPDATE " . DB_PREFIX . "product SET
			price = '" . (float)$final_price . "',
			points = '" . (float)$final_price . "',
			buster_products = '" . $this->db->escape(json_encode($buster_products ?? array(), JSON_UNESCAPED_UNICODE)) . "'
			WHERE product_id = '" . $product_id . "'");

		$price = $final_price;

		if ($winner) {
			// backup базы 1С без addon - чтобы следующий sync не считал базу "измененной"
			$backup = array(
				'backup_price' => (float)$base_price,
				'backup_special' => ($special_1c > 0 && $special_1c < $base_price) ? (float)$special_1c : 0,
				'backup_has_special' => ($special_1c > 0 && $special_1c < $base_price) ? 1 : 0,
			);
			$admin_special = $this->model_extension_module_getvape_znyzhky->applyRuleToProduct($product_id, $winner, $final_price, $backup);

			if ($admin_special) {
				$price = (float)$admin_special;
			}
		} elseif ($lock) {
			$existing = $this->model_extension_module_getvape_znyzhky->getLock($product_id);
			if ($existing) {
				$this->db->query("UPDATE `" . DB_PREFIX . "getvape_znyzhky_lock` SET
					backup_price = '" . (float)$base_price . "',
					backup_special = '" . (($special_1c > 0 && $special_1c < $base_price) ? (float)$special_1c : 0) . "',
					backup_has_special = '" . (($special_1c > 0 && $special_1c < $base_price) ? 1 : 0) . "'
					WHERE product_id = '" . $product_id . "'");
			}

			$admin_special = $this->model_extension_module_getvape_znyzhky->syncLockedSpecialFromPrice($product_id, $final_price);

			if ($admin_special) {
				$this->db->query("UPDATE " . DB_PREFIX . "product SET points = '" . (float)$admin_special . "' WHERE product_id = '" . $product_id . "'");
				$price = (float)$admin_special;
			}
		} else {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $product_id . "'");

			if ($final_special_1c > 0) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET
					product_id = '" . $product_id . "',
					customer_group_id = '1',
					priority = '1',
					price = '" . (float)$final_special_1c . "',
					date_start = '0000-00-00',
					date_end = '0000-00-00'");
				$price = $final_special_1c;
			}
		}

		$product_reward = array(1 => round($price * 0.05));
		$this->updateproductreward($product_id, $product_reward, 5);

		$query = $this->db->query("SELECT product_option_id, option_id FROM " . DB_PREFIX . "product_option WHERE product_id = '" . $product_id . "' AND option_id = 3");
		if (!$query->num_rows) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . $product_id . "', option_id = 3, required = 0");
			$product_option_id = $this->db->getLastId();
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . $product_id . "', option_id = 3, option_value_id = 25, quantity = 100, subtract = 1, price = '" . (float)$glicerin_price . "', price_prefix = '-', points = '" . (int)round($glicerin_price * 0.05) . "', points_prefix = '-', weight = 0, weight_prefix = '+'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET
				quantity = 100,
				price = '" . (float)$glicerin_price . "',
				points = '" . (int)round($glicerin_price * 0.05) . "',
				points_prefix = '-'
				WHERE product_option_id = '" . (int)$query->row['product_option_id'] . "' AND product_id = '" . $product_id . "'");
		}

		$query = $this->db->query("SELECT product_option_id, option_id FROM " . DB_PREFIX . "product_option WHERE product_id = '" . $product_id . "' AND option_id = 4");
		if (!$query->num_rows) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . $product_id . "', option_id = 4, required = 0");
			$product_option_id = $this->db->getLastId();
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . $product_id . "', option_id = 4, option_value_id = 26, quantity = 100, subtract = 1, price = '" . (float)$nikotin_price_corrected . "', price_prefix = '-', points = '" . (int)round($nikotin_price_corrected * 0.05) . "', points_prefix = '-', weight = 0, weight_prefix = '+'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET
				quantity = 100,
				price = '" . (float)$nikotin_price_corrected . "',
				points = '" . (int)round($nikotin_price_corrected * 0.05) . "',
				points_prefix = '-'
				WHERE product_option_id = '" . (int)$query->row['product_option_id'] . "' AND product_id = '" . $product_id . "'");
		}

		// Повні g/n вже в БД; при special масштабуємо пропорційно, щоб зняття опцій = base*знижка
		if (!isset($this->model_extension_module_getvape_znyzhky)) {
			$this->load->model('extension/module/getvape_znyzhky');
		}

		$this->model_extension_module_getvape_znyzhky->refreshMinusOptionDiscount($product_id, $final_price);

		$this->cache->delete('product*');

		$detail = $model . ' id=' . $product_id
			. ' ' . $old_price . '=>' . $final_price
			. ' (base=' . $base_price . ' g=' . $glicerin_price . ' n=' . $nikotin_price_corrected . ')';

		// Крупный скачок - в error.log, чтобы ловить кривые цены вроде 951
		if ($delta >= 50) {
			$this->log->write('sync_1c SOL PRICE FIX: ' . $detail);
		}

		return array(
			'detail' => $detail,
			'old' => $old_price,
			'new' => $final_price,
			'delta' => $delta,
		);
	}
	
	
	public function deleteRelateds($product_id)
	{
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related_1c WHERE product_id = '" . (int)$product_id . "'");
	}
	
	public function updateRelateds($product_id, $relateds)
	{
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related_1c WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($relateds as $related) {
			$related_id = 0;

			if (isset($this->modelsByCode[$related])) {
				$related_id = (int)$this->modelsByCode[$related];
			} else {
				$product_query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE model = '" . $this->db->escape($related) . "'");
				if ($product_query->num_rows) {
					$related_id = (int)$product_query->row['product_id'];
					$this->modelsByCode[$related] = $related_id;
				}
			}

			if ($related_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related_1c SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
			}
		}
	}

	private function preloadSyncMaps() {
		$this->productsByGuid = array();
		$this->categoriesByGuid = array();
		$this->skuProducts = array();
		$this->productQuantities = array();
		$this->productAttributesSig = array();
		$this->productCategoriesSig = array();
		$this->productRelatedSig = array();
		$this->productBonus = array();
		$this->modelsByCode = array();
		$this->productZnyzhkyLocks = array();

		$language_id = (int)$this->config->get('config_language_id');
		$customer_group_id = (int)$this->config->get('config_customer_group_id');

		$lock_table = $this->db->query("SHOW TABLES LIKE '" . str_replace('_', '\_', DB_PREFIX . 'getvape_znyzhky_lock') . "'");
		if ($lock_table->num_rows) {
			$lock_query = $this->db->query("SELECT product_id, backup_price, backup_special, backup_has_special, special_price FROM `" . DB_PREFIX . "getvape_znyzhky_lock`");
			foreach ($lock_query->rows as $row) {
				$this->productZnyzhkyLocks[(int)$row['product_id']] = $row;
			}
		}

		$query = $this->db->query("SELECT p.product_id, p.price, p.status, p.manufacturer_id, p.quantity, p.quantity_sklad, p.bonus, p.model, pd.name, pd.description, pd.tag, pt.guid,
			(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . $customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special,
			(SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . $customer_group_id . "' LIMIT 1) AS reward_points
			FROM " . DB_PREFIX . "product_to_1c pt
			INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = pt.product_id)
			LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id AND pd.language_id = '" . $language_id . "')");

		foreach ($query->rows as $row) {
			$guid = $row['guid'];
			$product_id = (int)$row['product_id'];
			$this->productsByGuid[$guid] = $row;
			$this->skuProducts[$guid] = $product_id;
			$this->productBonus[$product_id] = (int)$row['bonus'];
			if ($row['model'] !== '') {
				$this->modelsByCode[$row['model']] = $product_id;
			}
		}

		$cat_query = $this->db->query("SELECT c.category_id, c.parent_id, cd.name, c1s.guid
			FROM " . DB_PREFIX . "category_to_1c c1s
			INNER JOIN " . DB_PREFIX . "category c ON (c.category_id = c1s.category_id)
			LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id AND cd.language_id = '" . $language_id . "')");

		foreach ($cat_query->rows as $row) {
			$this->categoriesByGuid[$row['guid']] = $row;
		}

		$qty_query = $this->db->query("SELECT product_id, warehouse_id, quantity FROM " . DB_PREFIX . "product_quantity");
		foreach ($qty_query->rows as $row) {
			$pid = (int)$row['product_id'];
			if (!isset($this->productQuantities[$pid])) {
				$this->productQuantities[$pid] = array();
			}
			$this->productQuantities[$pid][(int)$row['warehouse_id']] = (int)$row['quantity'];
		}

		// Синк пишет атрибуты с language_id = 1
		$attr_query = $this->db->query("SELECT product_id, attribute_id, text FROM " . DB_PREFIX . "product_attribute WHERE language_id = 1 ORDER BY product_id, attribute_id");
		$attr_buf = array();
		foreach ($attr_query->rows as $row) {
			$pid = (int)$row['product_id'];
			if (!isset($attr_buf[$pid])) {
				$attr_buf[$pid] = array();
			}
			$attr_buf[$pid][(int)$row['attribute_id']] = (string)$row['text'];
		}
		foreach ($attr_buf as $pid => $attrs) {
			ksort($attrs);
			$this->productAttributesSig[$pid] = md5(json_encode($attrs, JSON_UNESCAPED_UNICODE));
		}

		$cat_map_query = $this->db->query("SELECT product_id, category_id, main_category FROM " . DB_PREFIX . "product_to_category");
		$cat_buf = array();
		$main_buf = array();
		foreach ($cat_map_query->rows as $row) {
			$pid = (int)$row['product_id'];
			if (!isset($cat_buf[$pid])) {
				$cat_buf[$pid] = array();
			}
			$cat_buf[$pid][] = (int)$row['category_id'];
			if ((int)$row['main_category'] === 1) {
				$main_buf[$pid] = (int)$row['category_id'];
			}
		}
		foreach ($cat_buf as $pid => $cats) {
			sort($cats);
			$main = isset($main_buf[$pid]) ? $main_buf[$pid] : 0;
			$this->productCategoriesSig[$pid] = $main . ':' . implode(',', $cats);
		}

		$rel_query = $this->db->query("SELECT product_id, related_id FROM " . DB_PREFIX . "product_related_1c ORDER BY product_id, related_id");
		$rel_buf = array();
		foreach ($rel_query->rows as $row) {
			$pid = (int)$row['product_id'];
			if (!isset($rel_buf[$pid])) {
				$rel_buf[$pid] = array();
			}
			$rel_buf[$pid][] = (int)$row['related_id'];
		}
		foreach ($rel_buf as $pid => $ids) {
			sort($ids);
			$this->productRelatedSig[$pid] = implode(',', $ids);
		}

		$this->log->write('sync_1c preload: products=' . count($this->productsByGuid) . ' categories=' . count($this->categoriesByGuid));
	}

	private function normalizePrice($value) {
		if (is_string($value)) {
			$value = str_replace(array(' ', "\xc2\xa0", "\t"), '', $value);
			$value = str_replace(',', '.', $value);
		}

		return round((float)$value, 2);
	}

	private function priceEq($a, $b) {
		return abs($this->normalizePrice($a) - $this->normalizePrice($b)) < 0.005;
	}

	/**
	 * Сравниваем базу и special именно 1С, а не сайт-скидку из product_special.
	 * При сайт-lock эталон базы - backup_price (последняя известная цена 1С), не витрина.
	 * Sol: в product.price лежит база 1С + глицерин/никотин - сравниваем с ожидаемым финалом.
	 */
	private function detectPriceDiff($product_id, $product_info, $product_price, $special_1c) {
		$product_id = (int)$product_id;
		$db_price = $this->normalizePrice($product_info['price']);
		$new_price = $this->normalizePrice($product_price);
		$ref_price = $db_price;
		$ref_source = 'db';

		$new_has_special = !empty($special_1c) && $this->normalizePrice($special_1c) > 0 && $this->normalizePrice($special_1c) < $new_price;
		$new_special = $new_has_special ? $this->normalizePrice($special_1c) : 0;

		$old_special = 0;
		$old_has_special = false;

		if (isset($this->productZnyzhkyLocks[$product_id])) {
			$lock = $this->productZnyzhkyLocks[$product_id];
			$old_has_special = !empty($lock['backup_has_special']);
			$old_special = $old_has_special ? $this->normalizePrice($lock['backup_special']) : 0;

			$backup_price = isset($lock['backup_price']) ? $this->normalizePrice($lock['backup_price']) : 0;
			if ($backup_price > 0) {
				$ref_price = $backup_price;
				$ref_source = 'backup';
			}
		} else {
			$db_special = isset($product_info['special']) && $product_info['special'] !== null && $product_info['special'] !== ''
				? $this->normalizePrice($product_info['special'])
				: 0;
			if ($db_special > 0 && $db_special < $db_price) {
				$old_has_special = true;
				$old_special = $db_special;
			}
		}

		$sol_addon = 0;
		if (in_array($product_id, $this->sol_products)) {
			$addon_info = $this->getSolAddon($product_id);
			$sol_addon = (float)round($addon_info['glicerin']) + (float)round($addon_info['nikotin']);
		}

		if ($ref_source === 'backup') {
			$base_changed = !$this->priceEq($ref_price, $new_price);
			$special_changed = ($old_has_special !== $new_has_special) || ($new_has_special && !$this->priceEq($old_special, $new_special));
			$base_detail = $ref_price . '=>' . $new_price . ' [backup/db=' . $db_price . ']';
		} elseif ($sol_addon > 0) {
			// Витрина = база 1С + addon; эталон финала
			$expected_final = $this->normalizePrice($new_price + $sol_addon);
			$base_changed = !$this->priceEq($db_price, $expected_final);
			$expected_special = $new_has_special ? $this->normalizePrice($new_special + $sol_addon) : 0;
			$special_changed = $new_has_special
				? (!$old_has_special || !$this->priceEq($old_special, $expected_special))
				: $old_has_special;
			$base_detail = $db_price . '=>' . $expected_final . ' [sol base=' . $new_price . ' addon=' . $sol_addon . ']';
		} else {
			$base_changed = !$this->priceEq($ref_price, $new_price);
			$special_changed = ($old_has_special !== $new_has_special) || ($new_has_special && !$this->priceEq($old_special, $new_special));
			$base_detail = $ref_price . '=>' . $new_price . ' [db]';
		}

		return array(
			'base' => $base_changed,
			'special' => $special_changed,
			'base_detail' => $base_detail,
			'special_detail' => $old_special . '=>' . $new_special . ($old_has_special || $new_has_special ? '' : ' none'),
		);
	}

	private function bumpDiff($reason, $product_id = 0, $detail = '') {
		if (!isset($this->sync_diff[$reason])) {
			$this->sync_diff[$reason] = 0;
		}

		$this->sync_diff[$reason]++;

		if (!isset($this->sync_diff_samples[$reason])) {
			$this->sync_diff_samples[$reason] = array();
		}

		if (count($this->sync_diff_samples[$reason]) < 5) {
			$sample = 'id=' . (int)$product_id;
			if ($detail !== '') {
				$sample .= ' ' . $detail;
			}
			$this->sync_diff_samples[$reason][] = $sample;
		}
	}

	private function flushDiffStats() {
		$parts = array();

		foreach ($this->sync_diff as $reason => $count) {
			if ((int)$count > 0) {
				$this->sync_stages['diff_' . $reason] = (int)$count;
				$parts[] = $reason . '=' . (int)$count;
			}
		}

		$summary = $parts ? implode(', ', $parts) : 'none';
		$this->log->write('sync_1c diff reasons: ' . $summary);

		foreach ($this->sync_diff_samples as $reason => $samples) {
			if ($samples) {
				$this->log->write('sync_1c diff samples [' . $reason . ']: ' . implode(' || ', $samples));
			}
		}

		if ($this->sync_job_id) {
			$this->load->model('extension/feed/sync_1c');
			$this->model_extension_feed_sync_1c->saveStages($this->sync_job_id, $this->sync_stages);
		}
	}

	private function buildAttributesSignature($product_attributes) {
		$map = array();
		foreach ($product_attributes as $product_attribute) {
			$attribute_id = (int)$product_attribute['attribute_id'];
			if (!$attribute_id) {
				continue;
			}
			$text = '';
			if (isset($product_attribute['product_attribute_description'][1]['text'])) {
				$text = (string)$product_attribute['product_attribute_description'][1]['text'];
			}
			$map[$attribute_id] = $text;
		}
		ksort($map);
		return md5(json_encode($map, JSON_UNESCAPED_UNICODE));
	}

	private function buildCategoriesSignature($main_category_id, $categories) {
		$cats = array();
		foreach ($categories as $category_id) {
			$cats[] = (int)$category_id;
		}
		$cats = array_values(array_unique($cats));
		sort($cats);
		return (int)$main_category_id . ':' . implode(',', $cats);
	}

	private function buildRelatedSignature($relateds) {
		$ids = array();
		foreach ($relateds as $related) {
			if (isset($this->modelsByCode[$related])) {
				$ids[] = (int)$this->modelsByCode[$related];
				continue;
			}

			$product_query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE model = '" . $this->db->escape($related) . "' LIMIT 1");
			if ($product_query->num_rows) {
				$related_id = (int)$product_query->row['product_id'];
				$this->modelsByCode[$related] = $related_id;
				$ids[] = $related_id;
			}
		}
		$ids = array_values(array_unique($ids));
		sort($ids);
		return implode(',', $ids);
	}

	private function normalizeQuantityMap($quantitydata) {
		$map = array();
		foreach ($quantitydata as $warehouse_id => $quantity) {
			$warehouse_id = (int)$warehouse_id;
			if ($warehouse_id == $this->sklad) {
				continue;
			}
			$map[$warehouse_id] = (int)$quantity;
		}
		ksort($map);
		return $map;
	}

	private function quantitiesAreEqual($product_id, $quantitydata) {
		$new_map = $this->normalizeQuantityMap($quantitydata);
		$old_map = isset($this->productQuantities[$product_id]) ? $this->productQuantities[$product_id] : array();
		ksort($old_map);

		if ($old_map != $new_map) {
			return false;
		}

		$online = $this->getOnlineWarehouseIds();
		$totalquantity = 0;
		$totalquantity_sklad = 0;
		foreach ($quantitydata as $warehouse_id => $quantity) {
			$warehouse_id = (int)$warehouse_id;
			$quantity = (int)$quantity;
			if (isset($online[$warehouse_id])) {
				$totalquantity += $quantity;
			} else {
				$totalquantity_sklad += $quantity;
			}
		}

		$info = null;
		foreach ($this->productsByGuid as $row) {
			if ((int)$row['product_id'] === (int)$product_id) {
				$info = $row;
				break;
			}
		}

		if ($info) {
			if ((int)$info['quantity'] !== (int)$totalquantity || (int)$info['quantity_sklad'] !== (int)$totalquantity_sklad) {
				return false;
			}
		}

		return true;
	}
}
