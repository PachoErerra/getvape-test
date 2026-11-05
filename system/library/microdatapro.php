<?php

/************************copyright***************************/
/*                                                          */
/* telegram: https://t.me/PrutNikolay                       */
/* forum: https://opencartforum.com/profile/18336-exploits/ */
/* email: info@microdata.pro                                */
/* site: https://microdata.pro                              */
/*                                                          */
/************************copyright***************************/

#[AllowDynamicProperties]
class MicrodataPro {

	//language
		public function language($word = false, $from_data = false){
			$from = 'en';

			if(!$from_data){
				$from_data = $this->config->get('config_admin_language');
			}

			$from_data = explode('-', $from_data);

			if(array_intersect(['uk','ua','ukrainian','ukraine'], $from_data)){
				$from = 'ua';
			}
			if(array_intersect(['ru','fr','russian','rus'], $from_data)){
				$from = 'ru';
			}

			$language['en']['heading_title'] = __CLASS__ . ' - v ' .  $this->ver;
			$language['en']['text_module'] = 'Extension';
			$language['en']['text_microdata_status']  = 'Status';
			$language['en']['text_InStock'] = 'In Stock';
			$language['en']['OutOfStock'] = 'Out Of Stock';
			$language['en']['LimitedAvailability'] = 'Limited Availability';
			$language['en']['InStoreOnly'] = 'InStore Only';
			$language['en']['OnlineOnly'] = 'Online Only';
			$language['en']['PreOrder'] = 'PreOrder';
			$language['en']['PreSale'] = 'PreSale';
			$language['en']['Discontinued'] = 'Discontinued';
			$language['en']['SoldOut'] = 'SoldOut';
			$language['en']['text_storetype_0'] = 'OnlineStore';
			$language['en']['text_storetype_1'] = 'AutoPartsStore';
			$language['en']['text_storetype_2'] = 'BikeStore';
			$language['en']['text_storetype_3'] = 'BookStore';
			$language['en']['text_storetype_4'] = 'ClothingStore';
			$language['en']['text_storetype_5'] = 'ComputerStore';
			$language['en']['text_storetype_6'] = 'ConvenienceStore';
			$language['en']['text_storetype_7'] = 'DepartmentStore';
			$language['en']['text_storetype_8'] = 'ElectronicsStore';
			$language['en']['text_storetype_9'] = 'Florist';
			$language['en']['text_storetype_10'] = 'FurnitureStore';
			$language['en']['text_storetype_11'] = 'GardenStore';
			$language['en']['text_storetype_12'] = 'GroceryStore';
			$language['en']['text_storetype_13'] = 'HardwareStore';
			$language['en']['text_storetype_14'] = 'HobbyShop';
			$language['en']['text_storetype_15'] = 'HomeGoodsStore';
			$language['en']['text_storetype_16'] = 'JewelryStore';
			$language['en']['text_storetype_17'] = 'LiquorStore';
			$language['en']['text_storetype_18'] = 'MensClothingStore';
			$language['en']['text_storetype_19'] = 'MobilePhoneStore';
			$language['en']['text_storetype_20'] = 'MovieRentalStore';
			$language['en']['text_storetype_21'] = 'MusicStore';
			$language['en']['text_storetype_22'] = 'OfficeEquipmentStore';
			$language['en']['text_storetype_23'] = 'OutletStore';
			$language['en']['text_storetype_24'] = 'PawnShop';
			$language['en']['text_storetype_25'] = 'PetStore';
			$language['en']['text_storetype_26'] = 'ShoeStore';
			$language['en']['text_storetype_27'] = 'SportingGoodsStore';
			$language['en']['text_storetype_28'] = 'tireShop';
			$language['en']['text_storetype_29'] = 'toyStore';
			$language['en']['text_storetype_30'] = 'WholesaleStore';
			$language['en']['button_save'] = 'Save';
			$language['en']['text_edit'] = 'Edit MicrodataPro settings';
			$language['en']['button_cancel'] = '&larr; in the module';
			$language['en']['text_company'] = 'Store';
			$language['en']['text_url_contact'] = 'Contact information page';
			$language['en']['text_url_privacy'] = 'Privacy information page';
			$language['en']['text_video'] = 'Markup video object';
			$language['en']['text_video_key'] = 'Youtube API KEY';
			$language['en']['text_name'] = 'Shop name in google';
			$language['en']['text_blog'] = 'Blog';
			$language['en']['text_blog_type'] = 'Blog type';
			$language['en']['text_blog_author'] = 'Blog author';
			$language['en']['text_blog_author_url'] = 'Blog author url';
			$language['en']['text_mod_admin'] = 'Mods in admin';
			$language['en']['text_mod_system'] = 'Mods in system directory';
			$language['en']['text_mod_go'] = 'Start';
			$language['en']['text_mod_from'] = 'From replace';
			$language['en']['text_mod_to'] = 'To replace';
			$language['en']['text_mod_cache_mod'] = 'mods cache';
			$language['en']['text_locations_coordinates'] = 'Coordinates';
			$language['en']['text_locations_country'] = 'Country';
			$language['en']['text_locations_city'] = 'City';
			$language['en']['text_locations_address'] = 'Address';
			$language['en']['text_locations_code'] = 'Code';
			$language['en']['text_config_hcard'] = 'Mark hCard microdata';
			$language['en']['text_config_hcard_h'] = 'hCard - microformat for publishing contact information';
			$language['en']['text_entry_telephone'] = 'All company phones';
			$language['en']['text_entry_telephone_h'] = 'Separate with commas. Enter in the international format +12-345-678-90-00 For example: +48-345-678-90-00, +72-345-678-90-00';
			$language['en']['text_entry_group'] = 'Groups in social networks';
			$language['en']['text_entry_group_h'] = 'Separate with commas, for example: https://facebook.com/group, https://instagram.com/link';
			$language['en']['text_entry_address'] = 'Address (representative office, delivery point)';
			$language['en']['text_entry_address_h'] = 'Coordinates in the format: latitude;longitude';
			$language['en']['text_entry_map'] = 'Map link';
			$language['en']['text_entry_map_h'] = 'Just copy the link to the map';
			$language['en']['text_product_page'] = 'Product';
			$language['en']['text_opengraph'] = 'Form open graph';
			$language['en']['text_opengraph_h'] = 'Works on the product page.';
			$language['en']['text_twitter_account'] = 'Your X (twitter) account';
			$language['en']['text_twitter_account_h'] = 'If not filled in - X (twitter) cards will not be displayed';
			$language['en']['text_meta_desc'] = 'Meta description in opengraph';
			$language['en']['text_meta_desc_h'] = 'If not included, the product description is displayed in Opengraph';
			$language['en']['text_breadcrumb'] = 'Mark breadcrumbs';
			$language['en']['text_other_data_h'] = 'Include only if the data is displayed on the product page';
			$language['en']['text_reviews'] = 'Mark reviews';
			$language['en']['text_related'] = 'Mark related products';
			$language['en']['text_attribute'] = 'Mark attributes (characteristics)';
			$language['en']['text_in_stock'] = 'The status is always available';
			$language['en']['text_in_stock_h'] = 'Even when the product is out of stock (quantity= 0) it will be marked inStock';
			$language['en']['text_information_page'] = 'Information';
			$language['en']['text_category_page'] = 'Category';
			$language['en']['text_manufacturer_page'] = 'Manufacturer';
			$language['en']['text_hide_price'] = 'Hide price';
			$language['en']['text_oh'] = 'Working hours';
			$language['en']['text_monday'] = 'Monday:';
			$language['en']['text_tuesday'] = 'tuesday:';
			$language['en']['text_wednesday'] = 'Wednesday:';
			$language['en']['text_thursday'] = 'thursday:';
			$language['en']['text_friday'] = 'Friday:';
			$language['en']['text_saturday'] = 'Saturday:';
			$language['en']['text_sunday'] = 'Sunday:';
			$language['en']['text_information'] = 'Information about the module';
			$language['en']['text_check'] = 'Check micromarkup';
			$language['en']['text_check_h'] = 'Before checking, donʼt forget to save the module settings';
			$language['en']['text_contacts'] = 'Developer contacts';
			$language['en']['text_contacts_h'] = 'If you have any questions about the module, write to the developer';
			$language['en']['text_email'] = 'Mail';
			$language['en']['text_site'] = 'Site';
			$language['en']['text_faq'] = 'Most popular questions and answers';
			$language['en']['text_faq_h'] = 'Read, maybe you will find the answer to your question there';
			$language['en']['text_old_microdata'] = 'Old microdata detected in files:';
			$language['en']['text_clear'] = 'Clear';
			$language['en']['text_success_removed'] = 'Old micromarkup successfully removed';
			$language['en']['text_store_type'] = 'type of store';
			$language['en']['text_store_type_h']= 'If nothing fits - donʼt choose. Will Store';
			$language['en']['text_select']= 'Select';
			$language['en']['text_social'] = 'Social networks';
			$language['en']['text_other'] = 'Other';
			$language['en']['text_other_modules'] = 'Other modules of the author';
			$language['en']['text_opengraph_h'] = 'Some templates have a basic one, but MicrodataPro forms the most complete OpenGraph markup. It is recommended to enable.';
			$language['en']['text_age_group_desc'] = 'If the store is for children only or adults only, select settings. For regular stores, you donʼt have to choose anything';
			$language['en']['text_age_group'] = 'type of store for social networks';
			$language['en']['text_age_default'] = 'Normal store';
			$language['en']['text_age_children'] = 'For children';
			$language['en']['text_age_adult'] = 'For adults';
			$language['en']['text_target_gender_desc'] = 'target gender of the product - for whom the products are intended. If you are not sure - do not choose anything.';
			$language['en']['text_target_gender'] = 'Target gender of the product';
			$language['en']['text_target_gender_female'] = 'For women';
			$language['en']['text_target_gender_male'] = 'For men';
			$language['en']['text_target_gender_unisex'] = 'Unisex';
			$language['en']['text_profile_id'] = 'Your facebook profile';
			$language['en']['text_profile_id_desc'] = 'In this field you need to insert a link to your Facebook profile, for example: https://www.facebook.com/nikolay.prut';
			$language['en']['text_attr_color'] = 'Attribute for color';
			$language['en']['text_attr_material'] = 'Attribute for material';
			$language['en']['text_attr_size'] = 'Attribute for size';
			$language['en']['text_gallery'] = 'Mark additional photo of the product as a gallery';
			$language['en']['text_diagnostic'] = 'Diagnostics (<span style="color:#00b32d;">ok</span>)';
			$language['en']['text_diagnostic_e'] = 'Diagnostics';
			$language['en']['text_for_work'] = 'The MicrodataPro module requires very little to work:';
			$language['en']['text_succ_mod'] = 'The module modifier has been successfully installed';
			$language['en']['text_click_view'] = 'Click to view information';
			$language['en']['text_err_mod'] = 'The module modifier is not fully installed (problems:';
			$language['en']['text_file_original'] = 'Original file:';
			$language['en']['text_modok'] = 'can be modified';
			$language['en']['text_in_file'] = 'In the file';
			$language['en']['text_find_string'] = 'Found string (or beginning) for binding:';
			$language['en']['text_code_in_file'] = 'Module code will be in';
			$language['en']['text_opencart_file'] = 'Store file:';
			$language['en']['text_none_mode'] = 'cannot be modified.';
			$language['en']['text_why_mod'] = 'How to fix?';
			$language['en']['text_in_file_strong'] = 'Need to be in the file';
			$language['en']['text_start_string'] = 'was the beginning of the string:';
			$language['en']['text_or'] = 'or';
			$language['en']['text_support_text'] = 'If not, add it. If it does not work, write <a href="https://microdata.pro/support/" target="_blank" title="Opens in a new window">MicrodataPro technical support</a><br>After editing the file <a href="#" class="refresh btn btn-primary btn-sm">refresh modifier cache</a> and go back to MicrodataPro to check';
			$language['en']['text_module_in_file'] = 'Module in file:';
			$language['en']['text_installed'] = 'installed';
			$language['en']['text_added_code_before'] = 'Successfully added code after the line:';
			$language['en']['text_module_code'] = '...module code...';
			$language['en']['text_not_installed'] = 'not installed.';
			$language['en']['text_what_to_do'] = 'What to do?';
			$language['en']['text_if_in_orig'] = 'If in the original file';
			$language['en']['text_all_good'] = 'everything is good, try to <a href="#" class="refresh btn btn-primary btn-sm">refresh the modifier cache</a> and the module code should register.<br>If not, review the comments on the original file, fix and <a href="#" class="refresh btn btn-primary btn-sm">refresh the mod cache</a> - should work.<br>Also, check if <b>MicrodataPro ' . $this->ver . '</b> is installed in modifiers.';
			$language['en']['text_for_good_work'] = '<p style="margin-top:10px;"><b>For the module to work correctly, donʼt forget to <a href="#" class="refresh btn btn-primary btn-sm">update modifier cache</a> and reset all caches set on the site</b></p>';
			$language['en']['text_old_microdata_not_find'] = 'Old markup elements in the template are not found';
			$language['en']['text_old_info'] = '<p>It often happens that templates already have built-in basic markup, which in 99% of cases does not work correctly and gives errors.</p><p>When detected such , it is recommended to clean it.</p><p>MicrodataPro has a built-in tool for cleaning old markup.</p><p>In your case, there is no built-in markup, nothing needs to be cleaned.</p>';
			$language['en']['text_old_microdata_find'] = 'Found old markup elements in the template!';
			$language['en']['text_recoment_clear'] = 'It is recommended to clear';
			$language['en']['text_cleared_info'] = '<p>It often happens that templates already have built-in basic markup, which in 99% of cases does not work correctly and gives errors.</p><p>When detected such , it is recommended to clean it.</p><p>MicrodataPro has a built-in tool for cleaning old markup.</p><p>In your case, the module saw old markup elements, it is recommended to clean it.</p><p><b> It is important to understand</b> that the automatic cleaning of the markup requires a further check for the correctness of the cleaning.</p>';
			$language['en']['text_other_modules_find'] = 'Other markup modules/modifiers found';
			$language['en']['text_other_modules_not_find'] = 'You have no other markup applications found';
			$language['en']['text_other_modules_info'] = '<p><b>Item 2 can also cause this error</b>. First, make sure that everything is OK with it.</p><p>Sometimes it happens that stores have some add-ons for micro-marking.</p><p>As a rule, they only mark the basic data and very often with errors. </p><p>For MicrodataPro to work properly, you need to remove all other markup mods and <a href="#" class="refresh btn btn-primary btn-sm">clear the modifier cache</a>.</p>';
			$language['en']['text_not_other_modules_info'] = '<p>Sometimes it happens that stores have some add-ons for micro-marking.</p><p>As a rule, they mark only the basic data and very often with errors. </p><p>For MicrodataPro to work properly, you need to remove all other markup modules.</p><p>You are fine, there are no other add-ons. Only MicrodataPro works.</p>';
			$language['en']['text_google_check'] = 'Check the site pages in google validator';
			$language['en']['text_link_check'] = '<p><b>After configuring</b> the module, it is recommended to check all pages for the correct operation of MicrodataPro</p><p>You can check the pages using the links</p>';
			$language['en']['text_main_page'] = 'Home';
			$language['en']['text_in_new_tab'] = 'Opens in a new tab';
			$language['en']['text_info_page'] = 'Information page';
			$language['en']['text_empty_category'] = '<li>Category - there are no categories in the store yet</li>';
			$language['en']['text_empty_product'] = '<li>Product - there are no products in the store yet</li>';
			$language['en']['text_empty_brand'] = '<li>Manufacturer - there are no manufacturers in the store yet</li>';
			$language['en']['text_empty_info'] = '<li>Information page - there are no information pages in the store yet</li>';
			$language['en']['text_google_validator'] = '<p>If any page is not available, check if maintenance mode is enabled in the store settings, or insert any other page (products, categories, etc.) in <a href="https://search.google.com/structured-data/testing-tool" target="_blank" title="Opens in a new window">google validator</a></p>';
			$language['en']['text_mod_cleared'] = 'The cache of modifiers has been successfully updated, the page will be reloaded!';
			$language['en']['text_thanks_new'] = '<h3>Thank you for using the MicrodataPro module.</h3><h4>This is a great choice, I hope it meets all your expectations! <span style="color:#00b32d;">:)</span></h4><p>Sincerely, module developer <a href="https://t.me/PrutNikolay" target="_blank"> Prut Mykola</a></p>';
			$language['en']['text_reviews_mod'] = 'Module reviews';
			$language['en']['text_on_site'] = 'on';
			$language['en']['text_old_microdata_deleted'] = 'Old markup elements deleted, reload the page';
			$language['en']['text_price_from_to'] = 'Mark prices from and to';
			$language['en']['text_all_rating'] = 'Mark the general rating of products';
			$language['en']['text_images_to_gallery'] = 'Mark product photos as a gallery';
			$language['en']['text_product_aggregator'] = 'Only for product aggregator!';
			$language['en']['text_category_rating'] = 'Rating of products in the category';
			$language['en']['text_support_info'] = 'Before writing to info@microdata.pro support, look here: <a href="https://microdata.pro/faq/" title="Opens in a new in the window" target="_blank" data-toggle="tooltip">https://microdata.pro/faq/</a><br>If you find it difficult to understand, it is better to contact the author of the module.';
			$language['en']['text_warning_info'] = 'Be sure to read the questions/answers: <a href="https://microdata.pro/faq/" target="_blank">https://microdata. pro/ faq/</a><br>Developer Telegram: <strong>@PrutNikolay</strong><br>Mail: <strong>info@microdata.pro</strong >' ;
			$language['en']['text_page_info'] = 'Attention! By specifying the data here, you must be sure that the visitor also sees it on the site pages. If this is not on the site, it is better to disable the store markup or add information to the site.';
			$language['en']['text_logo_size'] = 'Logo sizes';
			$language['en']['text_logo_alt'] = 'Alternative logo for markup';
			$language['en']['text_cron_key'] = 'Key for CRON price sampling link';
			$language['en']['text_recommended_to_fill'] = 'recommended to fill';
			$language['en']['text_products_carousel'] = 'Mark category products as carousel';
			$language['en']['text_products_carousel_extended'] = 'Extended carousel';
			$language['en']['text_photo_in_list'] = 'Mark photo in list';
			$language['en']['text_preview_size'] = 'Preview sizes';
			$language['en']['text_thumb_size'] = 'Sizes of large photos';
			$language['en']['text_status_from_status'] = 'Status in markup with product status:';
			$language['en']['text_shipping_cost'] = 'Shipping cost';
			$language['en']['text_shipping_cost_default'] = 'default 50';
			$language['en']['text_shipping_country'] = 'Shipping country';
			$language['en']['text_shipping_country_default'] = 'default UA';
			$language['en']['text_product_agregator'] = 'Only for product agregator';
			$language['en']['text_shipping_time1_min'] = 'Minimum order processing time';
			$language['en']['text_shipping_time1_min_default'] = 'default 0';
			$language['en']['text_shipping_time1_max'] = 'Maximum order processing time';
			$language['en']['text_shipping_time1_max_default'] = 'default 1';
			$language['en']['text_shipping_time2_min'] = 'Minimum delivery time after order processing';
			$language['en']['text_shipping_time2_min_default'] = 'default 1';
			$language['en']['text_shipping_time2_max'] = 'Maximum delivery time after order processing';
			$language['en']['text_shipping_time2_max_default'] = 'default 3';
			$language['en']['text_return_days'] = 'Product return days';
			$language['en']['text_return_days_default'] = 'default is 14';
			$language['en']['text_no_active'] = '<div style="font-size:22px;text-align:center;margin-top:20px;"><h3><strong>Sorry, MicrodataPro couldnʼt automatically pull up the license key for this domain</strong></h3><small style="display:block;margin:10px 0;font-size:16px;">It happens, itʼs okay</small><div class= "alert alert-info" style="font-size:14px;line-height:16px;">To manually collect the key, follow the link: <a target="_blank" href="https://microdata.pro/key?s=' .base64_encode($this->info('site')) .'">https://microdata.pro/key</a> and paste it in the box below</div><div><textarea name="txt" id="key_manual" class="form-control" placeholder="Paste your license key here and wait a couple of seconds"></textarea></div><div style="margin-top:15px;border :1px solid #00C962;padding:15px;font-size:12px;"><p>Thanks for waiting, I hope that after activation the module will justify all your hopes. Sincerely, MicrodataPro developer Nikolay</p><p>In the meantime, you can read about the moduleʼs capabilities and see instructions on the site <a target="_blank" title="Opens in a new tab" href="https://microdata.pro" >https://microdata.pro</a></p></div></div>';


			$language['ua']['heading_title'] = __CLASS__ . ' - v ' .  $this->ver;
			$language['ua']['text_module'] = 'Доповнення';
			$language['ua']['text_microdata_status']  = 'Статус';
			$language['ua']['text_InStock'] = 'В наявності';
			$language['ua']['OutOfStock'] = 'Немає в наявності';
			$language['ua']['LimitedAvailability'] = 'Обмежена кількість';
			$language['ua']['InStoreOnly'] = 'Доступний тільки в фізичному магазині';
			$language['ua']['OnlineOnly'] = 'Доступний тільки в онлайн';
			$language['ua']['PreOrder'] = 'Під замовлення';
			$language['ua']['PreSale'] = 'Попередня покупка';
			$language['ua']['Discontinued'] = 'Знятий з виробництва';
			$language['ua']['SoldOut'] = 'Товар розпроданий';
			$language['ua']['text_storetype_0'] = 'Інтернет магазин';
			$language['ua']['text_storetype_1'] = 'Автозапчастини';
			$language['ua']['text_storetype_2'] = 'Веломагазин';
			$language['ua']['text_storetype_3'] = 'Книгарня';
			$language['ua']['text_storetype_4'] = 'Магазин одягу';
			$language['ua']['text_storetype_5'] = 'Компʼютерний магазин';
			$language['ua']['text_storetype_6'] = 'Невеликий магазин';
			$language['ua']['text_storetype_7'] = 'Універсальний магазин';
			$language['ua']['text_storetype_8'] = 'Магазин електроніки';
			$language['ua']['text_storetype_9'] = 'Флористичний магазин';
			$language['ua']['text_storetype_10'] = 'Меблевий магазин';
			$language['ua']['text_storetype_11'] = 'Садовий магазин';
			$language['ua']['text_storetype_12'] = 'Продуктовий магазин';
			$language['ua']['text_storetype_13'] = 'Господарський магазин';
			$language['ua']['text_storetype_14'] = 'Хобі';
			$language['ua']['text_storetype_15'] = 'Продукти для дому';
			$language['ua']['text_storetype_16'] = 'Ювелірний магазин';
			$language['ua']['text_storetype_17'] = 'Лікеро-горілчаний магазин';
			$language['ua']['text_storetype_18'] = 'Магазин чоловічого одягу';
			$language['ua']['text_storetype_19'] = 'Магазин мобільних телефонів';
			$language['ua']['text_storetype_20'] = 'Магазин фільмів';
			$language['ua']['text_storetype_21'] = 'Музичний магазин';
			$language['ua']['text_storetype_22'] = 'Магазин офісного обладнання';
			$language['ua']['text_storetype_23'] = 'Стоковий магазин';
			$language['ua']['text_storetype_24'] = 'Ломбард';
			$language['ua']['text_storetype_25'] = 'Зоомагазин';
			$language['ua']['text_storetype_26'] = 'Взуттєвий магазин';
			$language['ua']['text_storetype_27'] = 'Магазин спортивних товарів';
			$language['ua']['text_storetype_28'] = 'Магазин шин та дисків';
			$language['ua']['text_storetype_29'] = 'Магазин іграшок';
			$language['ua']['text_storetype_30'] = 'Оптовий магазин';
			$language['ua']['button_save'] = 'Зберегти';
			$language['ua']['text_edit'] = 'Редагувати налаштування MicrodataPro';
			$language['ua']['button_cancel'] = '&larr; у модулі';
			$language['ua']['text_company'] = 'Магазин';
			$language['ua']['text_url_contact'] = 'Сторінка контактної інформації';
			$language['ua']['text_url_privacy'] = 'Сторінка умов використання';
			$language['ua']['text_video'] = 'Відео на сторінках';
			$language['ua']['text_video_key'] = 'Ключ Youtube API';
			$language['ua']['text_name'] = 'Назва магазину в пошуку';
			$language['ua']['text_blog'] = 'Блог';
			$language['ua']['text_blog_type'] = 'Тип блога';
			$language['ua']['text_blog_author'] = 'Автор блога';
			$language['ua']['text_blog_author_url'] = 'Посилання на автора';
			$language['ua']['text_mod_admin'] = 'Модифікатори в адмінці де знайдена розмітка';
			$language['ua']['text_mod_system'] = 'Модифікатори в System де знайдена розмітка';
			$language['ua']['text_mod_go'] = 'Виконати';
			$language['ua']['text_mod_from'] = 'Що змінити';
			$language['ua']['text_mod_to'] = 'На що змінити';
			$language['ua']['text_mod_cache_mod'] = 'кеша модифікаторів';
			$language['ua']['text_locations_coordinates'] = 'Координати';
			$language['ua']['text_locations_country'] = 'Країна';
			$language['ua']['text_locations_city'] = 'Населений пункт';
			$language['ua']['text_locations_address'] = 'Адреса';
			$language['ua']['text_locations_code'] = 'Поштовий код';
			$language['ua']['text_config_hcard'] = 'Розмічати мікродані hCard';
			$language['ua']['text_config_hcard_h'] = 'hCard - мікроформат для публікації контактної інформації';
			$language['ua']['text_entry_telephone'] = 'Всі телефони компанії';
			$language['ua']['text_entry_telephone_h'] = 'Розділяти комами. Вводити у міжнародному форматі +12-345-678-90-00 Наприклад: +48-345-678-90-00, +72-345-678-90-00';
			$language['ua']['text_entry_group'] = 'Групи в соціальних мережах';
			$language['ua']['text_entry_group_h'] = 'Розділяти комами, Наприклад: https://facebook.com/group, https://instagram.com/link';
			$language['ua']['text_entry_address'] = 'Адреса (представництво, пункт видачі)';
			$language['ua']['text_entry_address_h'] = 'Координати у форматі: latitude;longitude';
			$language['ua']['text_entry_map'] = 'Посилання на карту';
			$language['ua']['text_entry_map_h'] = 'Необхідно просто скопіювати посилання на карту';
			$language['ua']['text_product_page'] = 'Товар';
			$language['ua']['text_opengraph'] = 'Формувати open graph';
			$language['ua']['text_opengraph_h'] = 'Працює на сторінці товару.';
			$language['ua']['text_twitter_account'] = 'Ваш обліковий запис X (twitter)';
			$language['ua']['text_twitter_account_h'] = 'Якщо не заповнити - X (twitter) cards не буде виведено';
			$language['ua']['text_meta_desc'] = 'Meta description в opengraph';
			$language['ua']['text_meta_desc_h'] = 'Якщо не включено, в Opengraph виводиться опис товару';
			$language['ua']['text_breadcrumb'] = 'Розмічати хлібні крихти';
			$language['ua']['text_other_data_h'] = 'Включати лише якщо дані виведені на сторінці товару';
			$language['ua']['text_reviews'] = 'Розмічати відгуки';
			$language['ua']['text_related'] = 'Розмічати супутні товари';
			$language['ua']['text_attribute'] = 'Розмічати атрибути (характеристики)';
			$language['ua']['text_in_stock'] = 'В наявності завжди';
			$language['ua']['text_in_stock_h'] = 'Навіть, коли товар не в наявності (кількість= 0) він буде в розмітці inStock';
			$language['ua']['text_information_page'] = 'Інформація';
			$language['ua']['text_category_page'] = 'Категорія';
			$language['ua']['text_manufacturer_page'] = 'Виробник';
			$language['ua']['text_hide_price'] = 'Приховати ціну';
			$language['ua']['text_oh'] = 'Час роботи';
			$language['ua']['text_monday'] = 'Понеділок:';
			$language['ua']['text_tuesday'] = 'Вівторок:';
			$language['ua']['text_wednesday'] = 'Середа:';
			$language['ua']['text_thursday'] = 'Четвер:';
			$language['ua']['text_friday'] = 'Пʼятниця:';
			$language['ua']['text_saturday'] = 'Субота:';
			$language['ua']['text_sunday'] = 'Неділя:';
			$language['ua']['text_information'] = 'Інформація про модуль';
			$language['ua']['text_check'] = 'Перевірити мікророзмітку';
			$language['ua']['text_check_h'] = 'Перед перевіркою не забудьте зберегти налаштування модуля';
			$language['ua']['text_contacts'] = 'Контакти розробника';
			$language['ua']['text_contacts_h'] = 'якщо є питання щодо модуля, напишіть розробнику';
			$language['ua']['text_email'] = 'Пошта';
			$language['ua']['text_site'] = 'Сайт';
			$language['ua']['text_faq'] = 'Найпопулярніші запитання та відповіді';
			$language['ua']['text_faq_h'] = 'Почитайте, можливо Ви знайдете там відповідь на своє запитання';
			$language['ua']['text_old_microdata'] = 'Виявлено стару мікророзмітку у файлах:';
			$language['ua']['text_clear'] = 'Очистити';
			$language['ua']['text_success_removed'] = 'Стару мікророзмітку успішно видалено';
			$language['ua']['text_store_type'] = 'Тип магазину';
			$language['ua']['text_store_type_h']= 'якщо нічого не підходить - не вибирайте. Буде Store';
			$language['ua']['text_select']= 'Вибрати';
			$language['ua']['text_social'] = 'Соціальні мережі';
			$language['ua']['text_other'] = 'Інше';
			$language['ua']['text_other_modules'] = 'Інші модулі автора';
			$language['ua']['text_opengraph_h'] = 'У деяких шаблонах базова є, але MicrodataPro формує максимально повну розмітку OpenGraph. Рекомендується вмикати.';
			$language['ua']['text_age_group_desc'] = 'якщо магазин призначений лише для дітей або лише для дорослих, виберіть налаштування. Для звичайних магазинів нічого не треба вибирати';
			$language['ua']['text_age_group'] = 'Тип магазину для соціальних мереж';
			$language['ua']['text_age_default'] = 'Звичайний магазин';
			$language['ua']['text_age_children'] = 'Для дітей';
			$language['ua']['text_age_adult'] = 'Для дорослих';
			$language['ua']['text_target_gender_desc'] = 'Цільова стать продукту - для кого призначені товари. Якщо не впевнені - нічого не вибирайте.';
			$language['ua']['text_target_gender'] = 'Цільова стать товару';
			$language['ua']['text_target_gender_female'] = 'Для жінок';
			$language['ua']['text_target_gender_male'] = 'Для чоловіків';
			$language['ua']['text_target_gender_unisex'] = 'Унісекс';
			$language['ua']['text_profile_id'] = 'Ваш профіль facebook';
			$language['ua']['text_profile_id_desc'] = 'У це поле потрібно вставити посилання на профіль у facebook, наприклад: https://www.facebook.com/nikolay.prut';
			$language['ua']['text_attr_color'] = 'Атрибут для кольору';
			$language['ua']['text_attr_material'] = 'Атрибут для матеріалу';
			$language['ua']['text_attr_size'] = 'Атрибут для розміру';
			$language['ua']['text_gallery'] = 'Розмічати додаткові фото товару';
			$language['ua']['text_diagnostic'] = 'Діагностика (<span style="color:#00b32d;">ок</span>)';
			$language['ua']['text_diagnostic_e'] = 'Діагностика';
			$language['ua']['text_for_work'] = 'Для роботи модуля MicrodataPro необхідно зовсім небагато:';
			$language['ua']['text_succ_mod'] = 'Модифікатор модуля успішно встановлений';
			$language['ua']['text_click_view'] = 'Клікніть, щоб переглянути інформацію';
			$language['ua']['text_err_mod'] = 'Модифікатор модуля не повністю встановився (проблем:';
			$language['ua']['text_file_original'] = 'Файл оригінал:';
			$language['ua']['text_modok'] = 'можна модифікувати';
			$language['ua']['text_in_file'] = 'У файлі';
			$language['ua']['text_find_string'] = 'знайдено рядок (або початок) для привʼязки:';
			$language['ua']['text_code_in_file'] = 'Код модуля буде в';
			$language['ua']['text_opencart_file'] = 'Файл магазину:';
			$language['ua']['text_none_mode'] = 'не можна модифікувати.';
			$language['ua']['text_why_mod'] = 'як виправити?';
			$language['ua']['text_in_file_strong'] = 'Необхідно щоб у файлі';
			$language['ua']['text_start_string'] = 'було початок рядка:';
			$language['ua']['text_or'] = 'або';
			$language['ua']['text_support_text'] = 'якщо ні, додайте. Якщо не виходить, пишіть <a href="https://microdata.pro/support/" target="_blank" title="Відкриється в новому вікні">технічну підтримку MicrodataPro</a><br>Після правки файлу <a href="#" class="refresh btn btn-primary btn-sm">оновіть кеш модифікаторів</a> і зайдіть знову до MicrodataPro для контролю';
			$language['ua']['text_module_in_file'] = 'Модуль у файл:';
			$language['ua']['text_installed'] = 'встановлений';
			$language['ua']['text_added_code_before'] = 'Успішно доданий код після рядка:';
			$language['ua']['text_module_code'] = '...код модуля...';
			$language['ua']['text_not_installed'] = 'не встановлено.';
			$language['ua']['text_what_to_do'] = 'Що робити?';
			$language['ua']['text_if_in_orig'] = 'якщо в оригінальному файлі';
			$language['ua']['text_all_good'] = 'все добре, спробуйте <a href="#" class="refresh btn btn-primary btn-sm">оновити кеш модифікаторів</a> і код модуля повинен прописатися.<br>Якщо ні, перегляньте зауваження щодо файлу оригіналу, виправте і <a href="#" class="refresh btn btn-primary btn-sm">оновіть кеш модифікаторів</a> - має все заробити.<br>Також, перевірте, чи встановлено <b>MicrodataPro ' . $this->ver . '</b> у модифікаторах.';
			$language['ua']['text_for_good_work'] = '<p style="margin-top:10px;"><b>Для коректної роботи модуля не забудьте <a href="#" class="refresh btn btn-primary btn-sm">оновити кеш модифікаторів</a> та обнулити всі кеші встановлені на сайті</b></p>';
			$language['ua']['text_old_microdata_not_find'] = 'Старі елементи розмітки в шаблоні не знайдені';
			$language['ua']['text_old_info'] = '<p>Часто буває, що в шаблонах є вже вбудована базова розмітка, яка в 99% випадках працює не коректно і дає помилки.</p><p>При виявленні такої , рекомендується її чистити.</p><p>MicrodataPro має вбудований інструмент очищення старої розмітки.</p><p>У Вашому випадку вбудованої розмітки немає, чистити нічого не треба.</p>';
			$language['ua']['text_old_microdata_find'] = 'Знайдені старі елементи розмітки у шаблоні!';
			$language['ua']['text_recoment_clear'] = 'Рекомендується очистити';
			$language['ua']['text_cleared_info'] = '<p>Часто буває, що в шаблонах є вже вбудована базова розмітка, яка в 99% випадках працює не коректно і дає помилки.</p><p>При виявленні такої , рекомендується її чистити.</p><p>MicrodataPro має вбудований інструмент очищення старої розмітки.</p><p>У Вашому випадку модуль побачив старі елементи розмітки, рекомендується почистити.</p><p><b>Важливо розуміти</b> що автоматичне чищення розмітки потребує подальшої перевірки на коректність очищення.</p>';
			$language['ua']['text_other_modules_find'] = 'Виявлено інші модулі/модифікатори розмітки';
			$language['ua']['text_other_modules_not_find'] = 'У Вас не виявлено інших додатків розмітки';
			$language['ua']['text_other_modules_info'] = '<p><b>Цю помилку може викликати і 2 пункт</b>. Для початку переконайтеся, що з ним все гаразд.</p><p>Іноді буває, що в магазинах є якісь доповнення для мікророзмітки.</p><p>Як правило, вони розмічають лише базові дані і дуже часто з помилками .</p><p>Для правильної роботи MicrodataPro необхідно видалити всі інші моди розмітки та <a href="#" class="refresh btn btn-primary btn-sm">почистити кеш модифікаторів</a>.</p>';
			$language['ua']['text_not_other_modules_info'] = '<p>Іноді буває, що в магазинах є якісь доповнення для мікророзмітки.</p><p>Як правило, вони розмічають тільки базові дані і дуже часто з помилками .</p><p>Для правильної роботи MicrodataPro треба прибрати всі інші модулі для розмітки.</p><p>У Вас все добре, інших доповнень немає. Працює лише MicrodataPro.</p>';
			$language['ua']['text_google_check'] = 'Перевірте сторінки сайту у валідаторе google';
			$language['ua']['text_link_check'] = '<p><b>Після налаштування</b> модуля, рекомендується перевірити всі сторінки на коректність роботи MicrodataPro</p><p>Перевірити сторінки можна за посиланнями</p>';
			$language['ua']['text_main_page'] = 'Головна';
			$language['ua']['text_in_new_tab'] = 'Відкриється в новій вкладці';
			$language['ua']['text_info_page'] = 'Інформаційна сторінка';
			$language['ua']['text_empty_category'] = '<li>Категорія - в магазині поки що немає категорій</li>';
			$language['ua']['text_empty_product'] = '<li>Товар - у магазині поки що немає товарів</li>';
			$language['ua']['text_empty_brand'] = '<li>Виробник - у магазині поки що немає виробників</li>';
			$language['ua']['text_empty_info'] = '<li>Інформаційна сторінка - у магазині поки що немає інформаційних сторінок</li>';
			$language['ua']['text_google_validator'] = '<p>Якщо будь-яка сторінка недоступна, перевірте, чи не ввімкнено режим обслуговування в налаштуваннях магазину, або вставте будь-яку іншу сторінку (товари, категорії тощо) у <a href="https://search.google.com/structured-data/testing-tool" target="_blank" title="Відкриється в новому вікні">валідатор google</a></p>';
			$language['ua']['text_mod_cleared'] = 'Кеш модифікаторів успішно оновлено, сторінку буде перезавантажено!';
			$language['ua']['text_thanks_new'] = '<h3>Дякую за використання модуля MicrodataPro.</h3><h4>Це відмінний вибір, сподіваюся він виправдає всі Ваші очікування! <span style="color:#00b32d;">:)</span></h4><p>З повагою розробник модуля <a href="https://t.me/PrutNikolay" target="_blank"> Прут Микола</a></p>';
			$language['ua']['text_reviews_mod'] = 'Відгуки про модуль';
			$language['ua']['text_on_site'] = 'на';
			$language['ua']['text_old_microdata_deleted'] = 'Старі елементи розмітки видалені, перезавантажте сторінку';
			$language['ua']['text_price_from_to'] = 'Розмічати ціни від і до';
			$language['ua']['text_all_rating'] = 'Розмічати загальний рейтинг товарів';
			$language['ua']['text_images_to_gallery'] = 'Розмічати фото товарів як галерею';
			$language['ua']['text_product_agregator'] = 'Тільки для агрегатора товарів!';
			$language['ua']['text_category_rating'] = 'Рейтинг товарів у категорії';
			$language['ua']['text_support_info'] = 'Перед тим як написати в підтримку info@microdata.pro подивіться тут: <a href="https://microdata.pro/faq/" title="Відкриється в новому вікні" target="_blank" data-toggle="tooltip">https://microdata.pro/faq/</a><br>Якщо вам важко розібратися, краще зверніться до автора модуля.';
			$language['ua']['text_warning_info'] = 'Обовʼязково почитайте питання/відповіді: <a href="https://microdata.pro/faq/" target="_blank">https://microdata.pro/ faq/</a><br>Телеграм розробника: <strong>@PrutNikolay</strong><br>Пошта: <strong>info@microdata.pro</strong>' ;
			$language['ua']['text_page_info'] = 'Увага! Вказуючи дані тут ви повинні бути впевнені, що відвідувач це також бачить на сторінках сайту. Якщо цього немає на сайті, краще вимкнути розмітку магазину або додати інформацію на сайт.';
			$language['ua']['text_logo_size'] = 'Розміри логотипу';
			$language['ua']['text_logo_alt'] = 'Альтернативний логотип для розмітки';
			$language['ua']['text_cron_key'] = 'Ключ для CRON посилання вибірки цін';
			$language['ua']['text_recommended_to_fill'] = 'рекомендується заповнювати';
			$language['ua']['text_products_carousel'] = 'Розмічати товари категорії як карусель';
			$language['ua']['text_products_carousel_extended'] = 'Розширена карусель';
			$language['ua']['text_photo_in_list'] = 'Розмічати фото у списку';
			$language['ua']['text_preview_size'] = 'Розміри превʼю';
			$language['ua']['text_thumb_size'] = 'Розміри великих фото';
			$language['ua']['text_status_from_status'] = 'Статус в розмітці при статусі товара:';
			$language['ua']['text_shipping_cost'] = 'Вартість доставки';
			$language['ua']['text_shipping_cost_default'] = 'за замовчуванням 50';
			$language['ua']['text_shipping_country'] = 'Країна доставки';
			$language['ua']['text_shipping_country_default'] = 'за замовчуванням UA';
			$language['ua']['text_shipping_time1_min'] = 'Мін. час обробки замовлення (дні)';
			$language['ua']['text_shipping_time1_min_default'] = 'за замовчуванням 0';
			$language['ua']['text_shipping_time1_max'] = 'Макс. час обробки замовлення (дні)';
			$language['ua']['text_shipping_time1_max_default'] = 'за замовчуванням 1';
			$language['ua']['text_shipping_time2_min'] = 'Мін. час доставки після обробки (дні)';
			$language['ua']['text_shipping_time2_min_default'] = 'за замовчуванням 1';
			$language['ua']['text_shipping_time2_max'] = 'Макс. час доставки після обробки (дні)';
			$language['ua']['text_shipping_time2_max_default'] = 'за замовчуванням 3';
			$language['ua']['text_return_days'] = 'Повернення товару протягом (дні)';
			$language['ua']['text_return_days_default'] = 'за замовчуванням 14';
			$language['ua']['text_no_active'] = '<div style="font-size:22px;text-align:center;margin-top:20px;"><h3><strong>На жаль MicrodataPro не зміг автоматично підтягнути ключ ліцензії на цей домен</strong></h3><small style="display:block;margin:10px 0;font-size:16px;">Таке буває, нічого страшного</small><div class= "alert alert-info" style="font-size:14px;line-height:16px;">Щоб вручну забрати ключ, перейдіть за посиланням: <a target="_blank" href="https://microdata.pro/key?s=' . base64_encode($this->info('site')) . '">https://microdata.pro/key</a> і вставте його в поле нижче</div><div><textarea name="txt" id="key_manual" class="form-control" placeholder="Вставте сюди ключ ліцензії та зачекайте кілька секунд"></textarea></div><div style="margin-top:15px;border :1px solid #00C962;padding:15px;font-size:12px;"><p>Дякую за очікування, сподіваюся, що після активації модуль виправдає всі ваші надії. З повагою розробник MicrodataPro Микола</p><p>А поки що можна почитати про можливості модуля та переглянути інструкції на сайті <a target="_blank" title="Відкриється в новій вкладці" href="https://microdata.pro" >https://microdata.pro</a></p></div></div>';


			$language['ru']['heading_title'] = __CLASS__ . ' - v ' .  $this->ver;
			$language['ru']['text_module'] = 'Дополнения';
			$language['ru']['text_microdata_status']  = 'Статус';
			$language['ru']['text_InStock'] = 'В наличии';
			$language['ru']['OutOfStock'] = 'Нет в наличии';
			$language['ru']['LimitedAvailability'] = 'Ограниченное количество';
			$language['ru']['InStoreOnly'] = 'Доступен только в физическом магазине';
			$language['ru']['OnlineOnly'] = 'Доступен только в онлайн';
			$language['ru']['PreOrder'] = 'Под заказ';
			$language['ru']['PreSale'] = 'Предварительная покупка';
			$language['ru']['Discontinued'] = 'Снят с производства';
			$language['ru']['SoldOut'] = 'Товар распродан';
			$language['ru']['text_storetype_0'] = 'Интернет магазин';
			$language['ru']['text_storetype_1'] = 'Автозапчасти';
			$language['ru']['text_storetype_2'] = 'Веломагазин';
			$language['ru']['text_storetype_3'] = 'Книжный магазин';
			$language['ru']['text_storetype_4'] = 'Магазин одежды';
			$language['ru']['text_storetype_5'] = 'Компьютерный магазин';
			$language['ru']['text_storetype_6'] = 'Небольшой магазин';
			$language['ru']['text_storetype_7'] = 'Универсальный магазин';
			$language['ru']['text_storetype_8'] = 'Магазин электроники';
			$language['ru']['text_storetype_9'] = 'Флористический магазин';
			$language['ru']['text_storetype_10'] = 'Мебельный магазин';
			$language['ru']['text_storetype_11'] = 'Садовый магазин';
			$language['ru']['text_storetype_12'] = 'Продуктовый магазин';
			$language['ru']['text_storetype_13'] = 'Хозяйственный магазин';
			$language['ru']['text_storetype_14'] = 'Хобби';
			$language['ru']['text_storetype_15'] = 'Продукты для дома';
			$language['ru']['text_storetype_16'] = 'Ювелирный магазин';
			$language['ru']['text_storetype_17'] = 'Ликеро-водочный магазин';
			$language['ru']['text_storetype_18'] = 'Магазин мужской одежды';
			$language['ru']['text_storetype_19'] = 'Магазин мобильных телефонов';
			$language['ru']['text_storetype_20'] = 'Магазин фильмов';
			$language['ru']['text_storetype_21'] = 'Музыкальный магазин';
			$language['ru']['text_storetype_22'] = 'Магазин офисного оборудования';
			$language['ru']['text_storetype_23'] = 'Стоковый магазин';
			$language['ru']['text_storetype_24'] = 'Ломбард';
			$language['ru']['text_storetype_25'] = 'Зоомагазин';
			$language['ru']['text_storetype_26'] = 'Обувной магазин';
			$language['ru']['text_storetype_27'] = 'Магазин спортивных товаров';
			$language['ru']['text_storetype_28'] = 'Магазин шин и дисков';
			$language['ru']['text_storetype_29'] = 'Магазин игрушек';
			$language['ru']['text_storetype_30'] = 'Оптовый магазин';
			$language['ru']['button_save'] = 'Сохранить';
			$language['ru']['text_edit'] = 'Редактировать настройки MicrodataPro';
			$language['ru']['button_cancel'] = '&larr; В модули';
			$language['ru']['text_company'] = 'Магазин';
			$language['ru']['text_url_contact'] = 'Страница контактной информации';
			$language['ru']['text_url_privacy'] = 'Страница условий использования';
			$language['ru']['text_video'] = 'Видео на страницах';
			$language['ru']['text_video_key'] = 'Ключ Youtube API';
			$language['ru']['text_name'] = 'Название магазина в поиске';
			$language['ru']['text_blog'] = 'Блог';
			$language['ru']['text_blog_type'] = 'Тип блога';
			$language['ru']['text_blog_author'] = 'Автор блога';
			$language['ru']['text_blog_author_url'] = 'Ссылка на автора';
			$language['ru']['text_mod_admin'] = 'Модификаторы в админке где найдена разметка';
			$language['ru']['text_mod_system'] = 'Модификаторы в System где найдена разметка';
			$language['ru']['text_mod_go'] = 'Выполнить';
			$language['ru']['text_mod_from'] = 'Что заменить';
			$language['ru']['text_mod_to'] = 'На что заменить';
			$language['ru']['text_mod_cache_mod'] = 'кеша модификаторов';
			$language['ru']['text_locations_coordinates'] = 'Координаты';
			$language['ru']['text_locations_country'] = 'Страна';
			$language['ru']['text_locations_city'] = 'Населенный пункт';
			$language['ru']['text_locations_address'] = 'Адрес';
			$language['ru']['text_locations_code'] = 'Почтовый код';
			$language['ru']['text_config_hcard'] = 'Размечать микроданные hCard';
			$language['ru']['text_config_hcard_h'] = 'hCard - микроформат для публикации контактной информации';
			$language['ru']['text_entry_telephone'] = 'Все телефоны компании';
			$language['ru']['text_entry_telephone_h'] = 'Разделять запятыми. Вводить в международном формате +12-345-678-90-00 Например: +48-345-678-90-00,+72-345-678-90-00';
			$language['ru']['text_entry_group'] = 'Группы в социальных сетях';
			$language['ru']['text_entry_group_h'] = 'Разделять запятыми, Например: https://facebook.com/group, https://instagram.com/link';
			$language['ru']['text_entry_address'] = 'Адрес (представительство, пункт выдачи)';
			$language['ru']['text_entry_address_h'] = 'Координаты в формате: latitude;longitude';
			$language['ru']['text_entry_map'] = 'Ссылка на карту';
			$language['ru']['text_entry_map_h'] = 'Необходимо просто скопировать ссылку на карту';
			$language['ru']['text_product_page'] = 'Товар';
			$language['ru']['text_opengraph'] = 'Формировать open graph';
			$language['ru']['text_opengraph_h'] = 'Работает на странице товара.';
			$language['ru']['text_twitter_account'] = 'Ваш аккаунт X (twitter)';
			$language['ru']['text_twitter_account_h'] = 'Если не заполнить - X (twitter) cards не будет выведен';
			$language['ru']['text_meta_desc'] = 'Meta description в opengraph';
			$language['ru']['text_meta_desc_h'] = 'Если не включено, в opengraph выводится описание товара';
			$language['ru']['text_breadcrumb'] = 'Размечать хлебные крошки';
			$language['ru']['text_other_data_h'] = 'Включать только если данные выведены на странице товара';
			$language['ru']['text_reviews'] = 'Размечать отзывы';
			$language['ru']['text_related'] = 'Размечать сопутствующие товары';
			$language['ru']['text_attribute'] = 'Размечать атрибуты (характеристики)';
			$language['ru']['text_in_stock'] = 'Статус всегда в наличии';
			$language['ru']['text_in_stock_h'] = 'Даже когда товар не в наличии (количество= 0) он будет в разметке inStock';
			$language['ru']['text_information_page'] = 'Информация';
			$language['ru']['text_category_page'] = 'Категория';
			$language['ru']['text_manufacturer_page'] = 'Производитель';
			$language['ru']['text_hide_price'] = 'Скрыть цену';
			$language['ru']['text_oh'] = 'Время работы';
			$language['ru']['text_monday'] = 'Понедельник:';
			$language['ru']['text_tuesday'] = 'Вторник:';
			$language['ru']['text_wednesday'] = 'Среда:';
			$language['ru']['text_thursday'] = 'Четверг:';
			$language['ru']['text_friday'] = 'Пятница:';
			$language['ru']['text_saturday'] = 'Суббота:';
			$language['ru']['text_sunday'] = 'Воскресенье:';
			$language['ru']['text_information'] = 'Информация о модуле';
			$language['ru']['text_check'] = 'Проверить микроразметку';
			$language['ru']['text_check_h'] = 'Перед проверкой не забудьте сохранить настройки модуля';
			$language['ru']['text_contacts'] = 'Контакты разработчика';
			$language['ru']['text_contacts_h'] = 'Если есть вопросы по модулю, напишите разработчику';
			$language['ru']['text_email'] = 'Почта';
			$language['ru']['text_site'] = 'Сайт';
			$language['ru']['text_faq'] 	 = 'Наиболее популярные вопросы и ответы';
			$language['ru']['text_faq_h'] = 'Почитайте, возможно Вы найдете там ответ на свой вопрос';
			$language['ru']['text_old_microdata'] = 'Обнаружена старая микроразметка в файлах:';
			$language['ru']['text_clear'] = 'Очистить';
			$language['ru']['text_success_removed'] = 'Старая микроразметка успешно удалена';
			$language['ru']['text_store_type'] = 'Тип магазина';
			$language['ru']['text_store_type_h']= 'Если ничего не подходит - не выбирайте. Будет Store';
			$language['ru']['text_select']= 'Выбрать';
			$language['ru']['text_social'] = 'Социальные сети';
			$language['ru']['text_other'] = 'Другое';
			$language['ru']['text_other_modules'] = 'Другие модули автора';
			$language['ru']['text_opengraph_h'] = 'В некоторых шаблонах базовая есть, но MicrodataPro формирует максимально полную разметку OpenGraph. Рекомендуется включать.';
			$language['ru']['text_age_group_desc'] = 'Если магазин предназначен только для детей или только для взрослых выберите настройку. Для обычных магазинов не надо ничего выбирать';
			$language['ru']['text_age_group'] = 'Тип магазина для соц сетей';
			$language['ru']['text_age_default'] = 'Обычный магазин';
			$language['ru']['text_age_children'] = 'Для детей';
			$language['ru']['text_age_adult'] = 'Для взрослых';
			$language['ru']['text_target_gender_desc'] = 'Целевой пол продукта - для кого предназначены товары. Если не уверены - ничего не выбирайте.';
			$language['ru']['text_target_gender'] = 'Целевой пол товара';
			$language['ru']['text_target_gender_female'] = 'Для женщин';
			$language['ru']['text_target_gender_male'] = 'Для мужчин';
			$language['ru']['text_target_gender_unisex'] = 'Унисекс';
			$language['ru']['text_profile_id'] = 'Ваш профиль facebook';
			$language['ru']['text_profile_id_desc'] = 'В это поле нужно вставить ссылку на профиль в facebook, например: https://www.facebook.com/nikolay.prut';
			$language['ru']['text_attr_color'] = 'Атрибут для цвета';
			$language['ru']['text_attr_material'] = 'Атрибут для материала';
			$language['ru']['text_attr_size'] = 'Атрибут для размера';
			$language['ru']['text_gallery'] = 'Размечать доп.фото товара как галерею';
			$language['ru']['text_diagnostic'] = 'Диагностика (<span style="color:#00b32d;">ок</span>)';
			$language['ru']['text_diagnostic_e'] = 'Диагностика ';
			$language['ru']['text_for_work'] = 'Для работы модуля MicrodataPro необходимо совсем немного:';
			$language['ru']['text_succ_mod'] = 'Модификатор модуля успешно установлен';
			$language['ru']['text_click_view'] = 'Кликните для просмотра информации';
			$language['ru']['text_err_mod'] = 'Модификатор модуля не полностью установился (проблем:';
			$language['ru']['text_file_original'] = 'Файл оригинал:';
			$language['ru']['text_modok'] = 'можно модифицировать';
			$language['ru']['text_in_file'] = 'В файле';
			$language['ru']['text_find_string'] = 'найдена строка (или начало) для привязки:';
			$language['ru']['text_code_in_file'] = 'Код модуля будет в';
			$language['ru']['text_opencart_file'] = 'Файл магазина:';
			$language['ru']['text_none_mode'] = 'нельзя модифицировать.';
			$language['ru']['text_why_mod'] = 'Как исправить?';
			$language['ru']['text_in_file_strong'] = 'Необходимо что бы в файле';
			$language['ru']['text_start_string'] = 'было начало строки:';
			$language['ru']['text_or'] = 'или';
			$language['ru']['text_support_text'] = 'Если нет, добавьте. Если не получается, пишите в <a href="https://microdata.pro/support/" target="_blank" title="Откроется в новом окне">техническую поддержку MicrodataPro</a><br>После правки файла <a href="#" class="refresh btn btn-primary btn-sm">обновите кеш модификаторов</a> и зайдите опять в MicrodataPro для контроля';
			$language['ru']['text_module_in_file'] = 'Модуль в файл:';
			$language['ru']['text_installed'] = 'установлен';
			$language['ru']['text_added_code_before'] = 'Успешно добавлен код после строки:';
			$language['ru']['text_module_code'] = '...код модуля...';
			$language['ru']['text_not_installed'] = 'не установлен.';
			$language['ru']['text_what_to_do'] = 'Что делать?';
			$language['ru']['text_if_in_orig'] = 'Если в оригинальном файле';
			$language['ru']['text_all_good'] = 'все хорошо, попробуйте <a href="#" class="refresh btn btn-primary btn-sm">обновить кеш модификаторов</a> и код модуля должен прописаться.<br>Если нет, посмотрите замечания по файлу оригиналу, исправьте и <a href="#" class="refresh btn btn-primary btn-sm">обновите кеш модификаторов</a> - должно все заработать.<br>Также, проверьте установлен ли <b>MicrodataPro ' . $this->ver . '</b> в модификаторах.';
			$language['ru']['text_for_good_work'] = '<p style="margin-top:10px;"><b>Для корректной работы модуля не забудьте <a href="#" class="refresh btn btn-primary btn-sm">обновить кеш модификаторов</a> и обнулить все кеши установленные на сайте</b></p>';
			$language['ru']['text_old_microdata_not_find'] = 'Старые элементы разметки в шаблоне не найдены';
			$language['ru']['text_old_info'] = '<p>Часто бывает что в шаблонах есть уже встроенная базовая разметка, которая в 99% случаях работает не корректно и дает ошибки.</p><p>При обнаружении такой, рекомендуется ее чистить.</p><p>MicrodataPro имеет встроенный инструмент очистки старой разметки.</p><p>В Вашем случае разметки встроенной нет, чистить ничего не надо.</p>';
			$language['ru']['text_old_microdata_find'] = 'Найдены старые элементы разметки в шаблоне!';
			$language['ru']['text_recoment_clear'] = 'Рекомендуется очистить';
			$language['ru']['text_cleared_info'] = '<p>Часто бывает что в шаблонах есть уже встроенная базовая разметка, которая в 99% случаях работает не корректно и дает ошибки.</p><p>При обнаружении такой, рекомендуется ее чистить.</p><p>MicrodataPro имеет встроенный инструмент очистки старой разметки.</p><p>В Вашем случае модуль увидил старые элементы разметки, рекомендуется почистить.</p><p><b>Важно понимать</b> что автоматическая чистка разметки нуждается в последующей проверки на корректность очистки.</p>';
			$language['ru']['text_other_modules_find'] = 'Обнаружены другие модули/модификаторы разметки';
			$language['ru']['text_other_modules_not_find'] = 'У Вас не обнаружены другие дополнения разметки';
			$language['ru']['text_other_modules_info'] = '<p><b>Эту ошибку может вызывать и 2 пункт</b>. Для начала убедитесь что с ним все в порядке.</p><p>Иногда бывает что в магазинах есть какие-то дополнения для микроразметки.</p><p>Как правило, они размечают только базовые данные и очень часто с ошибками.</p><p>Для правильной работы MicrodataPro необходимо удалить все другие моды разметки и <a href="#" class="refresh btn btn-primary btn-sm">почистить кеш модификаторов</a>.</p>';
			$language['ru']['text_not_other_modules_info'] = '<p>Иногда бывает что в магазинах есть какие-то дополнения для микроразметки.</p><p>Как правило, они размечают только базовые данные и очень часто с ошибками.</p><p>Для правильной работы MicrodataPro надо убрать все другие модули для разметки.</p><p>У Вас все хорошо, других дополнений нет. Работает только MicrodataPro.</p>';
			$language['ru']['text_google_check'] = 'Проверьте страницы сайта в валидаторе google';
			$language['ru']['text_link_check'] = '<p><b>После настройки</b> модуля, рекомендуется проверить все страницы на корректность работы MicrodataPro</p><p>Проверить страницы можно по ссылкам</p>';
			$language['ru']['text_main_page'] = 'Главная';
			$language['ru']['text_in_new_tab'] = 'Откроется в новой вкладке';
			$language['ru']['text_info_page'] = 'Информационная страница';
			$language['ru']['text_empty_category'] = '<li>Категория - в магазине пока что нет категорий</li>';
			$language['ru']['text_empty_product'] = '<li>Товар - в магазине пока что нет товаров</li>';
			$language['ru']['text_empty_brand'] = '<li>Производитель - в магазине пока что нет производителей</li>';
			$language['ru']['text_empty_info'] = '<li>Информационная страница - в магазине пока что нет информационных страниц</li>';
			$language['ru']['text_google_validator'] = '<p>Если какая либо страница недоступна, проверьте не включен ли режим обслуживания в настройках магазина, либо вставьте любую другую страницу (товара, категории и т.п.) в <a href="https://search.google.com/structured-data/testing-tool" target="_blank" title="Откроется в новом окне">валидатор google</a></p>';
			$language['ru']['text_mod_cleared'] = 'Кеш модификаторов успешно обновлен, страница будет перезагружена!';
			$language['ru']['text_thanks_new'] = '<h3>Спасибо за использование модуля MicrodataPro.</h3><h4>Это отличный выбор, надеюсь он оправдает все Ваши ожидания! <span style="color:#00b32d;">:)</span></h4><p>С уважением разработчик модуля <a href="https://t.me/PrutNikolay" target="_blank">Прут Николай</a></p>';
			$language['ru']['text_reviews_mod'] = 'Отзывы о модуле';
			$language['ru']['text_on_site'] = 'на';
			$language['ru']['text_old_microdata_deleted'] = 'Старые элементы разметки удалены, перезагрузите страницу';
			$language['ru']['text_price_from_to'] = 'Размечать цены от и до';
			$language['ru']['text_all_rating'] = 'Размечать общий рейтинг товаров';
			$language['ru']['text_images_to_gallery'] = 'Размечать фото товаров как галерею';
			$language['ru']['text_product_agregator'] = 'Только для агрегатора товаров!';
			$language['ru']['text_category_rating'] = 'Рейтинг товаров в категории';
			$language['ru']['text_support_info'] = 'Перед тем как написать в поддержку info@microdata.pro посмотрите здесь: <a href="https://microdata.pro/faq/" title="Откроектся в новом окне" target="_blank" data-toggle="tooltip">https://microdata.pro/faq/</a><br>Если вам тяжело разобраться, лучше обратитесь к автору модуля.';
			$language['ru']['text_warning_info'] = 'Обязательно почитайте вопросы/ответы: <a href="https://microdata.pro/faq/" target="_blank">https://microdata.pro/faq/</a><br>Телеграмм разработчика: <strong>@PrutNikolay</strong><br>Почта: <strong>info@microdata.pro</strong>';
			$language['ru']['text_page_info'] = 'Внимание! Указывая данные здесь вы должны быть уверены что посетитель это также видит на страницах сайта.<br>Если этого нет на сайте, лучше выключить разметку магазина или добавить информацию на сайт.';
			$language['ru']['text_logo_size'] = 'Размеры логотипа';
			$language['ru']['text_logo_alt'] = 'Альтернативный логотип для разметки';
			$language['ru']['text_cron_key'] = 'Ключ для CRON ссылки выборки цен';
			$language['ru']['text_recommended_to_fill'] = 'рекомендуется заполнять';
			$language['ru']['text_products_carousel'] = 'Размечать товары категории как карусель';
			$language['ru']['text_products_carousel_extended'] = 'Расширенная карусель';
			$language['ru']['text_photo_in_list'] = 'Размечать фото в списке';
			$language['ru']['text_preview_size'] = 'Размеры превью';
			$language['ru']['text_thumb_size'] = 'Размеры больших фото';
			$language['ru']['text_status_from_status'] = 'Статус в разметке при статусе товара:';
			$language['ru']['text_shipping_cost'] = 'Cтоимость доставки';
			$language['ru']['text_shipping_cost_default'] = 'по умолчанию 50';
			$language['ru']['text_shipping_country'] = 'Cтрана доставки';
			$language['ru']['text_shipping_country_default'] = 'по умолчанию UA';
			$language['ru']['text_shipping_time1_min'] = 'Мин. время обработки заказа (дни)';
			$language['ru']['text_shipping_time1_min_default'] = 'по умолчанию 0';
			$language['ru']['text_shipping_time1_max'] = 'Макс. время обработки заказа (дни)';
			$language['ru']['text_shipping_time1_max_default'] = 'по умолчанию 1';
			$language['ru']['text_shipping_time2_min'] = 'Мин. время доставки после обработки (дни)';
			$language['ru']['text_shipping_time2_min_default'] = 'по умолчанию 1';
			$language['ru']['text_shipping_time2_max'] = 'Макс. время доставки после обработки (дни)';
			$language['ru']['text_shipping_time2_max_default'] = 'по умолчанию 3';
			$language['ru']['text_return_days'] = 'Возврат товара на протяжении (дни)';
			$language['ru']['text_return_days_default'] = 'по умолчанию 14';
			$language['ru']['text_no_active'] = '<div style="font-size:22px;text-align:center;margin-top:20px;"><h3><strong>К сожалению MicrodataPro не смог автоматически подтянуть ключ лицензии на этот домен</strong></h3><small style="display:block;margin:10px 0;font-size:16px;">Такое бывает, ничего страшного</small><div class="alert alert-info" style="font-size:14px;line-height:16px;">Чтобы вручную забрать ключ перейдите по ссылке: <a target="_blank" href="https://microdata.pro/key?s=' . base64_encode($this->info('site')) . '">https://microdata.pro/key</a> и вставьте его в поле ниже</div><div><textarea name="txt" id="key_manual" class="form-control" placeholder="Вставьте сюда ключ лицензии и подождите пару секунд"></textarea></div><div style="margin-top:15px;border:1px solid #00C962;padding:15px;font-size:12px;"><p>Спасибо за ожидание, надеюсь что после активации модуль оправдает все ваши надежды. С уважением разработчик MicrodataPro Николай</p><p>А пока можно почитать о возможностях модуля и посмотреть инструкции на сайте <a target="_blank" title="Откроется в новой вкладке" href="https://microdata.pro">https://microdata.pro</a></p></div></div>';

			if($word){
				return isset($language[$from][$word])?$language[$from][$word]:'';
			}else{
				$this->lang = $language[$from];
			}

		}
	//language

	public $a 				= 0;
	public $ver 			= '10.1';
	public $lang 			= '';
	public $message 	= '';
	public $extension = 'extension/extension';
	public $path 			= 'extension/module/microdatapro';
	public $refresh 	= 'marketplace/modification/refresh';
	public $refresh23 = 'extension/modification/refresh';
	public $datfile 	= '/microdatapro.min.max.dat';
	public $vidfile 	= '/microdatapro.video.dat';


	public function __construct($registry) {
		$this->config 	= $registry->get('config');
		$this->currency = $registry->get('currency');
		$this->tax 			= $registry->get('tax');
		$this->session 	= $registry->get('session');
		$this->db 			= $registry->get('db');
		$this->url 			= $registry->get('url');
		$this->language = $registry->get('language');
	}

	//mdc
		public function mdc($key){
			return $this->config->get($this->key() . '_' . $key);
		}
	//mdc

	//key
		public function key(){
			$key = 'microdatapro';
			if(substr(VERSION, 0, 1) == 3){
				$key = 'module_' . $key;
			}

			return $key;
		}
	//key

	//info
		public function info($key = false){
			$dmn = explode("//", defined('HTTP_CATALOG')?HTTP_CATALOG:HTTP_SERVER);
			$information = [
				'site'		  => str_replace(['www.','/'],'',$dmn[1]),
				'date'		  => date("Y-m-d H:i:s"),
				'lang'		  => $this->config->get('config_language'),
				'email'		  => $this->config->get('config_email'),
				'engine' 	  => VERSION,
				'module' 	  => strtolower(__CLASS__),
				'version'   => $this->ver,
				'sec_token' => "3274507573",
			];

			return $key?$information[$key]:$information;
		}
	//info

	//init
		public function init() {
			$this->status();
			$this->language();

			if(!$this->a){
				$txt = $this->auto();
				if($txt){
					$txt_data = explode("::", $txt);
					if($txt_data[0] == 'Key' && isset($txt_data[1]) && $txt_data[1]){
						$this->a = $txt_data[1];
						$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($this->a) . "', serialized = '0'  WHERE `" . (((int)VERSION >= 2)?'code':'group') . "` = '" . $this->key() . "' AND `key` = '" . $this->key() . "_key' AND store_id = '0'");
					}else{
						if(isset($txt_data[1])){
							$this->message = $txt_data[1];
						}
					}
					if(!$this->message){
						$this->message = $this->lang['text_no_active'];
					}
				}else{
					$this->message = $this->lang['text_no_active'];
				}
			}

			$lang_dir = $this->config->get('config_language');
			$query = $this->db->query("SELECT directory FROM " . DB_PREFIX . "language WHERE code = '" . $this->db->escape($this->config->get('config_language')) . "'");
			if($query->row['directory']){
				$lang_dir = $query->row['directory'];
			}

			$lang_filename = DIR_LANGUAGE . $lang_dir . '/' . $this->path . '.php';
			$lang_filepath = str_replace('/microdatapro.php', '', $lang_filename);
			$lang_filedata = '<?php $_[\'heading_title\'] = \'<span style="color:#00b32d;">Microdata PRO</span> - v <strong>' . $this->ver . '</strong>\';';

			if(is_file($lang_filename)){
				unlink($lang_filename);
			}

			if(!is_dir($lang_filepath)){
				mkdir($lang_filepath, 0777, true);
			}

			$lang_file = fopen($lang_filename, 'w');
			fwrite($lang_file, $lang_filedata);
			fclose($lang_file);
		}
	//init

	//status
		public function status($txt_manual = false){
			$txt = $txt_manual?$txt_manual:$this->check();

			if($txt){
				$key_array = explode("327450", base64_decode(strrev(substr($txt, 0, -7))));
				if($key_array[0] == base64_encode($this->info('site')) && $key_array[1] == base64_encode('3274507573')){
					$this->a = 1;
				}
			}
			if($txt_manual){
				return $this->a;
			}
		}
	//status

	//check
		public function check(){
			$txt = '';
			$key_query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE `key` = '" . $this->key() . "_key'");
			if(!$key_query->num_rows){
				$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET `value` = '', serialized = '0', `" . (((int)VERSION >= 2)?'code':'group') . "` = '" . $this->key() . "', `key` = '" . $this->key() . "_key', store_id = '0'");
			}else{
				$txt = $key_query->row['value'];
			}

			return $txt;
		}
	//check

	//auto
		public function auto(){
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, "https://microdata.pro/index.php?route=sale/sale");
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $this->info());
			$auto = curl_exec($curl);
			curl_close($curl);

			return $auto;
		}
	//auto

}
