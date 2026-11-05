<?php

/************************copyright***************************/
/*                                                          */
/* telegram: https://t.me/PrutNikolay                       */
/* forum: https://opencartforum.com/profile/18336-exploits/ */
/* email: info@microdata.pro                                */
/* site: https://microdata.pro                              */
/*                                                          */
/************************copyright***************************/

require_once(DIR_SYSTEM . 'library/microdatapro.php');

class ControllerExtensionModuleMicrodataPro extends Controller {

	private $ver = 'MDP v10.1';
	public function index($input){
		$mdp 							= [];
		$data 					  = $input[1];
		$data['function'] = $input[0]?$input[0]:'ogcompany';
		$data['raw_data'] = isset($input[2])?$input[2]:[];
		$data['add_data'] = isset($input[3])?$input[3]:[];

		if($this->getStatus($data)){
			//checkVariable +++ //tmp off, check vars
			$this->getAuthor($data, $mdp);
			$this->getThisUrl($data, $mdp);

			$this->{$data['function']}($data, $mdp);
		}

		return $data;
	}

	//main functions

		//ogcompany
			private function ogcompany(&$data, $mdp){
				$this->getOgImageSize($data, $mdp);
				$this->getOgData($data, $mdp);
				$this->getCompany($data, $mdp);
				$this->getVideo($data, $mdp);

				$this->templateOg($data, $mdp);
	      $this->templateOgPrefix($data, $mdp);
				$this->templateLocalCompany($data, $mdp);
			}
		//ogcompany

		//category
			private function category(&$data, $mdp){
				$this->getCategoryImages($data, $mdp);
				$this->getCategoryList($data, $mdp);
				$this->getCategoryReviews($data, $mdp);
				$this->getPriceRange($data, $mdp);
				$this->getBreadcrumbs($data, $mdp);
				$this->getOgImageSize($data, $mdp);
				$this->getOgData($data, $mdp);
				$this->getDescription($data, $mdp);
				$this->getCompany($data, $mdp);
				$this->getVideo($data, $mdp);

	      $this->templateCategory($data, $mdp);
				$this->templateOg($data, $mdp);
	      $this->templateOgPrefix($data, $mdp);
				$this->templateLocalCompany($data, $mdp);
			}
		//category

		//product
			private function product(&$data, $mdp){
				$this->getProductImages($data, $mdp);
				$this->getPrice($data, $mdp);
				$this->getStock($data, $mdp);
				$this->getReviews($data, $mdp);
				$this->getAttributes($data, $mdp);
				$this->getRelated($data, $mdp);
				$this->getBreadcrumbs($data, $mdp);
				$this->getOgImageSize($data, $mdp);
				$this->getOgData($data, $mdp);
				$this->getDescription($data, $mdp);
				$this->getProductData($data, $mdp);
				$this->getCompany($data, $mdp);
				$this->getVideo($data, $mdp);
				$this->getImageText($data, $mdp, 1);

	      $this->templateProduct($data, $mdp);
				$this->templateOg($data, $mdp);
	      $this->templateOgPrefix($data, $mdp);
				$this->templateLocalCompany($data, $mdp);
		  }
		//product

		//manufacturer
			private function manufacturer(&$data, $mdp){
				if($this->request->get['route'] == 'product/manufacturer/info'){
					$this->getCategoryImages($data, $mdp);
					$this->getCategoryList($data, $mdp);
					$this->getCategoryReviews($data, $mdp);
					$this->getPriceRange($data, $mdp);
					$this->getBreadcrumbs($data, $mdp);
					$this->getOgImageSize($data, $mdp);
					$this->getOgData($data, $mdp);
					$this->getDescription($data, $mdp);
					$this->getCompany($data, $mdp);
					$this->getVideo($data, $mdp);

		      $this->templateCategory($data, $mdp);
					$this->templateOg($data, $mdp);
		      $this->templateOgPrefix($data, $mdp);
					$this->templateLocalCompany($data, $mdp);
				}
			}
		//manufacturer

		//article
			private function article(&$data, $mdp){
				$this->getBreadcrumbs($data, $mdp);
				$this->getOgImageSize($data, $mdp);
				$this->getOgData($data, $mdp);
				$this->getDescription($data, $mdp);
				$this->getCompany($data, $mdp);
				$this->getVideo($data, $mdp);
				$this->getImageText($data, $mdp);

				$this->templateArticle($data, $mdp);
				$this->templateOg($data, $mdp);
	      $this->templateOgPrefix($data, $mdp);
				$this->templateLocalCompany($data, $mdp);
			}
		//article

		//article
			private function blog(&$data, $mdp){
				$this->getBreadcrumbs($data, $mdp);
				$this->getOgImageSize($data, $mdp);
				$this->getOgData($data, $mdp);
				$this->getDescription($data, $mdp);
				$this->getCompany($data, $mdp);
				$this->getVideo($data, $mdp);
				$this->getBlogData($data, $mdp);

				$this->templateBlog($data, $mdp);
				$this->templateOg($data, $mdp);
	      $this->templateOgPrefix($data, $mdp);
				$this->templateLocalCompany($data, $mdp);
			}
		//article

	//main functions

	//template functions

		//templateOg
		  private function templateOg(&$data, $mdp, $html = ''){
		    if($this->mdp->mdc('twitter_account')){
		      $html .= '<!--' . $this->ver . ' X Summary Card start-->';
		      $html .= '<meta property="twitter:card" content="summary_large_image">';
		      $html .= '<meta property="twitter:creator" content="' . $this->mdp->mdc('twitter_account') . '">';
		      $html .= '<meta property="twitter:site" content="' . $mdp['title'] . '">';
		      $html .= '<meta property="twitter:title" content="' . $mdp['title'] . '">';
		      $html .= '<meta property="twitter:description" content="' . $mdp['desc'] . '">';
		      $html .= '<meta property="twitter:image" content="' . $mdp['image'] . '">';
		      $html .= '<meta property="twitter:image:alt" content="' . $mdp['title'] . '">';
		      $html .= '<!--' . $this->ver . ' X Summary Card end-->';
		    }
		    if($this->mdp->mdc('opengraph')){
		      $html .= '<!--' . $this->ver . ' Open Graph start-->';
		      $html .= '<meta property="og:locale" content="' . $mdp['locale'] . '">';
		      $html .= '<meta property="og:rich_attachment" content="true">';
		      $html .= '<meta property="og:site_name" content="' . $mdp['author'] . '">';
		      $html .= '<meta property="og:type" content="' . $mdp['function'] . '">';
		      $html .= '<meta property="og:title" content="' . $mdp['title'] . '">';
		      $html .= '<meta property="og:description" content="' . $mdp['desc'] . '">';
			  
			  $html .= '<meta property="og:logo" content="' . $mdp['logo'] . '">';
			  
		      $html .= '<meta property="og:image" content="' . $mdp['image'] . '">';
		      $html .= '<meta property="og:image:secure_url" content="' . $mdp['image'] . '">';
		      $html .= '<meta property="og:image:width" content="' . $mdp['image_width'] . '">';
		      $html .= '<meta property="og:image:height" content="' . $mdp['image_height'] . '">';
		      $html .= '<meta property="og:url" content="' . $mdp['url'] . '">';
					$html .= '<meta property="business:contact_data:email" content="' . $mdp['email'] . '">';
					if($mdp['address']){
						$html .= '<meta property="business:contact_data:street_address" content="' . $mdp['address']['street'] . '">';
						$html .= '<meta property="business:contact_data:locality" content="' . $mdp['address']['locality'] . '">';
						$html .= '<meta property="business:contact_data:postal_code" content="' . $mdp['address']['postal'] . '">';
						$html .= '<meta property="business:contact_data:country_name" content="' . $mdp['address']['country'] . '">';
						$html .= '<meta property="place:location:latitude" content="' . $mdp['address']['latitude'] . '">';
						$html .= '<meta property="place:location:longitude" content="' . $mdp['address']['longitude'] . '">';
						$html .= '<meta property="business:contact_data:phone_number" content="' . $mdp['telephone'] . '">';
					}
		      if($this->mdp->mdc('profile_id')){
		        $html .= '<meta property="fb:profile_id" content="' . $this->mdp->mdc('profile_id') . '">';
		      }
		      if($data['function'] == 'product'){
		        $html .= '<meta property="product:product_link" content="' . $mdp['url'] . '">';
		        $html .= '<meta property="product:brand" content="' . $mdp['brand'] . '">';
		        $html .= '<meta property="product:category" content="' . $mdp['category'] . '">';
		        $html .= '<meta property="product:availability" content="' . $mdp['stock'] . '">';
		        if($this->mdp->mdc('age_group')){
		          $html .= '<meta property="product:age_group" content="' . $this->mdp->mdc('age_group') . '">';
		          if($this->mdp->mdc('age_group') == "adult"){
		            $html .= '<meta property="og:restrictions:age" content="18+">';
		          }
		        }
		        $html .= '<meta property="product:condition" content="' . $mdp['cond_small'] . '">';
		        foreach(['ean','isbn','upc','color','material','size'] as $type){
		          if($mdp[$type]){
		            $html .= '<meta property="product:' . $type . '" content="' . $mdp[$type] . '">';
		          }
		        }
						if($mdp['audience']){
							$html .= '<meta property="product:target_gender" content="' . $mdp['audience'] . '">';
						}
		        if($mdp['price_old']){
							$html .= '<meta property="product:price:amount" content="' . $mdp['price_old'] . '">';
							$html .= '<meta property="product:price:currency" content="' . $mdp['code'] . '">';
		          $html .= '<meta property="product:sale_price:amount" content="' . $mdp['price'] . '">';
		          $html .= '<meta property="product:sale_price:currency" content="' . $mdp['code'] . '">';
		        }else{
							$html .= '<meta property="product:price:amount" content="' . $mdp['price'] . '">';
							$html .= '<meta property="product:price:currency" content="' . $mdp['code'] . '">';
						}
		        if($mdp['products']){
		          foreach($mdp['products'] as $related){
		            $html .= '<meta property="og:see_also" content="' . $related['href'] . '">';
		          }
		        }
		      }
		      $html .= '<!--' . $this->ver . ' Open Graph end-->';
		    }

		    $data['header'] = str_replace('<title>', str_replace('><', '>' . PHP_EOL . '<', $html . PHP_EOL) . '<title>', $data['header']);
		  }
		//templateOg

		//templateOgPrefix
			private function templateOgPrefix(&$data, $mdp){
				$prefix = 'prefix="og: https://ogp.me/ns# fb: https://ogp.me/ns/fb# ' . ((in_array($data['function'],['product','category']))?'product: https://ogp.me/ns/product# ':'') . 'business: https://ogp.me/ns/business# place: https://ogp.me/ns/place#"';
				$data['header'] = str_replace('<html', '<html ' . $prefix, $data['header']);
			}
		//templateOgPrefix

		//templateBreadcrumbs
			private function templateBreadcrumbs(&$html, $mdp){
				if($mdp['breadcrumbs']){
					$html .= '<!--' . $this->ver . ' Breadcrumb start-->';
					$html .= '<script type="application/ld+json">';
					$html .= '{';
					$html .= '"@context": "https://schema.org",';
					$html .= '"@type": "BreadcrumbList",';
					$html .= '"itemListElement": [';
					foreach ($mdp['breadcrumbs'] as $key => $breadcrumb){
						$html .= '{';
						$html .= '"@type": "ListItem",';
						$html .= '"position": ' . $key . ',';
						$html .= '"item": {';
						$html .= '"@id": "' . $breadcrumb['href'] . '",';
						$html .= '"name": "' . $breadcrumb['text'] . '"';
						$html .= '}';
						$html .= '}';
						if($key != count($mdp['breadcrumbs'])){
							$html .= ',';
						}
					}
					$html .= ']';
					$html .= '}';
					$html .= '</script>';
					$html .= '<!--' . $this->ver . ' Breadcrumb end-->';
				}
			}
		//templateBreadcrumbs

		//templateProduct
		  private function templateProduct(&$data, $mdp, $html = ''){
		    $this->templateBreadcrumbs($html, $mdp);

	      $html .= '<!--' . $this->ver . ' Product start-->';
	      $html .= '<script type="application/ld+json">';
	      $html .= '{';
	      $html .= '"@context": "https://schema.org",';
	      $html .= '"@type": "Product",';
	      $html .= '"url": "' . $mdp['url'] . '",';
	      if($mdp['category']){
	        $html .= '"category": "' . $mdp['category'] . '",';
	      }
	      if($mdp['image']){
	        $html .= '"image": "' . $mdp['image'] . '",';
	      }
	      if($mdp['brand']){
	        $html .= '"brand": {"@type": "Brand","name": "' . $mdp['brand'] . '"},';
	        $html .= '"manufacturer": "' . $mdp['brand'] . '",';
	      }
	      if($mdp['model']){
	        $html .= '"model": "' . $mdp['model'] . '",';
	      }
	      if($mdp['upc']){
	        $html .= '"gtin12": "' . $mdp['upc'] . '",';
	      }
	      if($mdp['ean']){
	        $html .= '"gtin8": "' . $mdp['ean'] . '",';
	      }
	      if($mdp['isbn']){
	        $html .= '"productID": "' . $mdp['isbn'] . '",';
	      }
	      if($mdp['mpn']){
	        $html .= '"mpn": "' . $mdp['mpn'] . '",';
	      }
	      if($mdp['sku']){
	        $html .= '"sku": "' . $mdp['sku'] . '",';
	      }
	      if($mdp['reviews']){
	        $html .= '"aggregateRating": {';
	        $html .= '"@type": "AggregateRating",';
	        $html .= '"ratingValue": "' . $mdp['rating'] . '",';
	        $html .= '"reviewCount": "' . $mdp['reviewCount'] . '",';
	        $html .= '"bestRating": "5",';
	        $html .= '"worstRating": "1"';
	        $html .= '},';
	      }
	      $html .= '"description": "' . $mdp['description'] . '",';
	      $html .= '"name": "' . $mdp['title'] . '",';
				if($mdp['attributes']){
	        $html .= '"additionalProperty":[';
	        foreach($mdp['attributes'] as $key => $attribute){
	          $html .= '{';
	          $html .= '"@type": "PropertyValue",';
	          $html .= '"name": "' . $attribute['name'] . '",';
	          $html .= '"value": "' . $attribute['text'] . '"';
	          $html .= '}';
	          if($key != count($mdp['attributes'])){
	          $html .= ',';
	          }
	        }
	        $html .= '],';
	      }
	      $html .= '"offers": {';
	      $html .= '"@type": "Offer",';
	      $html .= '"availability": "https://schema.org/' . $mdp['stock'] . '",';
	      $html .= '"price": "' . $mdp['price'] . '",';
				if($mdp['price_old']){
					$html .= '"priceSpecification": {';
					$html .= '"@type": "UnitPriceSpecification",';
						$html .= '"priceType": "https://schema.org/StrikethroughPrice",';
						$html .= '"price": "' . $mdp['price_old'] . '",';
						$html .= '"priceCurrency": "' . $mdp['code'] . '"';
					$html .= '},';
				}
				$html .= '"priceCurrency": "' . $mdp['code'] . '",';
	      $html .= '"priceValidUntil": "' . $mdp['price_valid'] . '",';
	      $html .= '"url": "' . $mdp['url'] . '",';
				if($mdp['audience']){
					$html .= '"audience": {"@type": "PeopleAudience","suggestedGender": "' . $mdp['audience'] . '"},';
				}
				foreach(['color','material','size'] as $add_item){
					if($mdp[$add_item]){
						$html .= '"' . $add_item . '": "' . $mdp[$add_item] . '",';
					}
				}
	      $html .= '"shippingDetails": {';
	        $html .= '"@type": "OfferShippingDetails",';
	        $html .= '"shippingRate": {';
	          $html .= '"@type": "MonetaryAmount",';
	          $html .= '"value": ' . $mdp['shipping_cost'] . ',';
	          $html .= '"currency": "' . $mdp['code'] . '"';
	        $html .= '},';
	        $html .= '"shippingDestination": {';
	          $html .= '"@type": "DefinedRegion",';
	          $html .= '"addressCountry": "' . $mdp['shipping_country'] . '"';
	        $html .= '},';
	        $html .= '"deliveryTime": {';
	          $html .= '"@type": "ShippingDeliveryTime",';
	          $html .= '"handlingTime": {';
	            $html .= '"@type": "QuantitativeValue",';
	            $html .= '"minValue": ' . $mdp['shipping_time1_min'] . ',';
	            $html .= '"maxValue": ' . $mdp['shipping_time1_max'] . ',';
	            $html .= '"unitCode": "DAY"';
	          $html .= '},';
	          $html .= '"transitTime": {';
	            $html .= '"@type": "QuantitativeValue",';
	            $html .= '"minValue": ' . $mdp['shipping_time2_min'] . ',';
	            $html .= '"maxValue": ' . $mdp['shipping_time2_max'] . ',';
	            $html .= '"unitCode": "DAY"';
	          $html .= '}';
	        $html .= '}';
	      $html .= '},';
				$html .= '"hasMerchantReturnPolicy": {';
					$html .= '"@type": "MerchantReturnPolicy",';
					$html .= '"applicableCountry": "' . $mdp['shipping_country'] . '",';
					$html .= '"returnPolicyCountry": "' . $mdp['shipping_country'] . '",';
					$html .= '"returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",';
					$html .= '"merchantReturnDays": "' . $mdp['return_days'] . '",';
					$html .= '"returnMethod": "https://schema.org/ReturnByMail",';
					$html .= '"refundType": "https://schema.org/FullRefund",';
					$html .= '"returnFees": "https://schema.org/FreeReturn"';
				$html .= '},';
	      $html .= '"itemCondition": "https://schema.org/' . $mdp['condition'] . '"';
	      $html .= '}';
	      if ($mdp['reviews']){
	        $html .= ',"review": [';
	        foreach ($mdp['reviews'] as $key => $review_item){
	          $html .= '{';
	          $html .= '"@type": "Review",';
	          $html .= '"author": {"@type": "Person", "name": "' . $review_item['author'] . '"},';
	          $html .= '"datePublished": "' . $review_item['date_added'] . '",';
	          $html .= '"description": "' . $review_item['text'] . '",';
	          $html .= '"reviewRating": {';
	          $html .= '"@type": "Rating",';
	          $html .= '"bestRating": "5",';
	          $html .= '"ratingValue": "' . $review_item['rating'] . '",';
	          $html .= '"worstRating": "1"';
	          $html .= '}';
	          $html .= '}';
	          if($key != count($mdp['reviews'])){
	            $html .= ',';
	          }
	        }
	        $html .= ']';
	      }
	      if($mdp['products']){
	        $html .= ',"isRelatedTo": [';
	        foreach($mdp['products'] as $key => $product){
	          $html .= '{';
	          $html .= '"@type": "Product",';
	          $html .= '"image": "' . $product['thumb'] . '",';
	          $html .= '"url": "' . $product['href'] . '",';
	          $html .= '"name": "' . $product['name'] . '",';
	          $html .= '"offers": {';
	          $html .= '"@type": "Offer",';
	          $html .= '"price": "' . $product['price'] . '",';
	          $html .= '"priceCurrency": "' . $mdp['code'] . '"';
	          $html .= '}';
	        $html .= '}';
	          if($key != count($mdp['products'])){
	            $html .= ',';
	          }
	        }
	        $html .= ']';
	      }
	      $html .= '}';
	      $html .= '</script>';
	      $html .= '<!--' . $this->ver . ' Product end-->';
	      $html .= '<!--' . $this->ver . ' Image start-->';
	      $html .= '<script type="application/ld+json">';
	      $html .= '{';
	      $html .= '"@context": "https://schema.org",';
	      $html .= '"@type": "ImageObject",';
	      $html .= '"author": "' . $mdp['author'] . '",';
	      $html .= '"thumbnailUrl": "' . $mdp['thumb'] . '",';
	      $html .= '"contentUrl": "' . $mdp['image'] . '",';
	      $html .= '"creditText": "' . $mdp['author'] . '",';
	      $html .= '"creator": {"@type": "Person","name": "' . $mdp['author'] . '"},';
	      $html .= '"copyrightNotice": "' . $mdp['author'] . '",';
	      $html .= '"acquireLicensePage": "' . $mdp['url_contact'] . '",';
	      $html .= '"license": "' . $mdp['url_privacy'] . '",';
	      $html .= '"datePublished": "' . $mdp['date_added'] . '",';
	      $html .= '"description": "' . $mdp['title'] . '",';
	      $html .= '"name": "' . $mdp['title'] . '"';
	      $html .= '}';
	      $html .= '</script>';
	      $html .= '<!--' . $this->ver . ' Image end-->';
	      if($mdp['images']){
	        $html .= '<!--' . $this->ver . ' Gallery start-->';
	        $html .= '<script type="application/ld+json">';
	        $html .= '{';
	        $html .= '"@context": "https://schema.org",';
	        $html .= '"@type": "ImageGallery",';
	        $html .= '"associatedMedia":[';
	        $keyi = 1;
	        foreach($mdp['images'] as $image){
	          $html .= '{';
	          $html .= '"@type": "ImageObject",';
	          $html .= '"author": "' . $mdp['author'] . '",';
	          $html .= '"thumbnailUrl": "' . $image['thumb'] . '",';
	          $html .= '"contentUrl": "' . $image['popup'] . '",';
						$html .= '"creditText": "' . $mdp['author'] . '",';
						$html .= '"creator": {"@type": "Person","name": "' . $mdp['author'] . '"},';
						$html .= '"copyrightNotice": "' . $mdp['author'] . '",';
						$html .= '"acquireLicensePage": "' . $mdp['url_contact'] . '",';
						$html .= '"license": "' . $mdp['url_privacy'] . '",';
	          $html .= '"datePublished": "' . $mdp['date_added'] . '",';
	          $html .= '"description": "' . $mdp['title'] . '",';
	          $html .= '"name": "' . $mdp['title'] . '"';
	          $html .= '}';
	          if($keyi != count($mdp['images'])){
	            $html .= ',';
	          }
	          $keyi++;
	        }
	        $html .= ']';
	        $html .= '}';
	        $html .= '</script>';
	        $html .= '<!--' . $this->ver . ' Gallery end-->';
	      }

		    $this->microdataToTheme($data, $html);
		  }
		//templateProduct

		//templateCategory
			private function templateCategory(&$data, $mdp, $html = ''){
				$this->templateBreadcrumbs($html, $mdp);

			  if($mdp['total'] or $mdp['rating_count']){
		      $html .= '<!--' . $this->ver . ' Category start-->';
	        $html .= '<script type="application/ld+json">';
	        $html .= '{';
	        $html .= '"@context": "https://schema.org/",';
	        $html .= '"@type": "Product",';
	        $html .= '"name": "' . $mdp['title'] . '",';
	        $html .= '"image": "' . $mdp['image'] . '",';
	        $html .= '"brand": {"@type": "Brand","name": "' . $mdp['title'] . '"},';
	        $html .= '"description": "' . $mdp['description'] . '",';
	        $html .= '"sku": "' . $mdp['title'] . '",';
	        $html .= '"mpn": "' . $mdp['title'] . '"';
	        if($mdp['rating_count']){
	          $html .= ',"aggregateRating": {';
	          $html .= '"@type": "AggregateRating",';
	          $html .= '"bestRating": "5",';
	          $html .= '"ratingValue": "' . $mdp['rating_value'] . '",';
	          $html .= '"ratingCount": "' . $mdp['rating_count'] . '"';
	          $html .= '}';
	        }
	        if($mdp['total']){
	          $html .= ',"offers": {';
	          $html .= '"@type": "AggregateOffer",';
	          $html .= '"lowPrice": "' . $mdp['min'] . '",';
	          $html .= '"highPrice": "' . $mdp['max'] . '",';
	          $html .= '"offerCount": "' . $mdp['total'] . '",';
	          $html .= '"priceCurrency": "' . $mdp['code'] . '"';
	          $html .= '}';
	        }
	        $html .= '}';
	        $html .= '</script>';
					$html .= '<script type="application/ld+json">';
					$html .= '{';
					$html .= '"@context": "https://schema.org",';
					$html .= '"@type": "ImageObject",';
					$html .= '"author": "' . $mdp['author'] . '",';
					$html .= '"thumbnailUrl": "' . $mdp['image'] . '",';
					$html .= '"contentUrl": "' . $mdp['image'] . '",';
					$html .= '"creditText": "' . $mdp['author'] . '",';
					$html .= '"creator": {"@type": "Person","name": "' . $mdp['author'] . '"},';
					$html .= '"copyrightNotice": "' . $mdp['author'] . '",';
					$html .= '"acquireLicensePage": "' . $mdp['url_contact'] . '",';
					$html .= '"license": "' . $mdp['url_privacy'] . '",';
					$html .= '"datePublished": "' . $mdp['date_added'] . '",';
					$html .= '"description": "' . $mdp['title'] . '",';
					$html .= '"name": "' . $mdp['title'] . '"';
					$html .= '}';
					$html .= '</script>';
		      $html .= '<!--' . $this->ver . ' Category end-->';
			  }

				//gallery
				  if($mdp['images']){
				      $html .= '<!--' . $this->ver . ' Gallery start-->';
				      $html .= '<script type="application/ld+json">';
				      $html .= '{';
				      $html .= '"@context": "https://schema.org",';
				      $html .= '"@type": "ImageGallery",';
				      $html .= '"associatedMedia":[';
				      $key_i = 1;
				      foreach($mdp['images'] as $image){
				        $html .= '{';
				        $html .= '"@type": "ImageObject",';
				        $html .= '"author": "' . $mdp['author'] . '",';
				        $html .= '"thumbnailUrl": "' . $image['thumb'] . '",';
				        $html .= '"contentUrl": "' . $image['popup'] . '",';
								$html .= '"creditText": "' . $mdp['author'] . '",';
								$html .= '"creator": {"@type": "Person","name": "' . $mdp['author'] . '"},';
								$html .= '"copyrightNotice": "' . $mdp['author'] . '",';
								$html .= '"acquireLicensePage": "' . $mdp['url_contact'] . '",';
								$html .= '"license": "' . $mdp['url_privacy'] . '",';
				        $html .= '"datePublished": "' . $image['date_added'] . '",';
				        $html .= '"description": "' . $image['name'] . '",';
				        $html .= '"name": "' . $image['name'] . '"';
				        $html .= '}';
				        if($key_i != count($mdp['images'])){
				          $html .= ',';
				        }
				        $key_i++;
				      }
				      $html .= ']';
				      $html .= '}';
				      $html .= '</script>';
				      $html .= '<!--' . $this->ver . ' Gallery end-->';

				  }
				//gallery

				//list
				  if($mdp['list']){
				    $html .= '<!--' . $this->ver . ' List start-->';
				    $html .= '<script type="application/ld+json">';
				    $html .= '{';
				    $html .= '"@context":"https://schema.org",';
				    $html .= '"@type":"ItemList",';
				    $html .= '"itemListElement":[';
				    foreach($mdp['list'] as $list_key => $list_item){
				      $html .= '{';
								if($mdp['list_extra']){
									$html .= '"position":' . $list_key . ',';
									$html .= '"item": {';
										$html .= '"image": "' . $list_item['thumb'] . '",';
										$html .= '"url": "' . $list_item['url'] . '",';
										$html .= '"name": "' . $list_item['name'] . '",';
										if($list_item['reviewCount']){
											$html .= '"aggregateRating": {';
												$html .= '"@type": "AggregateRating",';
												$html .= '"bestRating": "5",';
												$html .= '"ratingValue": "' . $list_item['rating'] . '",';
												$html .= '"ratingCount": "' . $list_item['reviewCount'] . '"';
											$html .= '},';
										}
										$html .= '"offers": {';
											$html .= '"price": ' . $list_item['price'] . ',';
											$html .= '"priceCurrency": "' . $mdp['code'] . '",';
											$html .= '"name": "' . $list_item['name'] . '",';
											$html .= '"url": "' . $list_item['url'] . '",';
											$html .= '"itemCondition": "https://schema.org/NewCondition",';
											$html .= '"availability": "https://schema.org/' . $list_item['stock'] . '",';
											$html .= '"shippingDetails": {';
												$html .= '"shippingRate": {';
													$html .= '"value": ' . $mdp['shipping_cost'] . ',';
													$html .= '"currency": "' . $mdp['code'] . '",';
													$html .= '"@type": "MonetaryAmount"';
												$html .= '},';
												$html .= '"@type": "OfferShippingDetails"';
											$html .= '},';
											$html .= '"@type": "Offer"';
										$html .= '},';
										$html .= '"@type": "Product"';
									$html .= '},';
									$html .= '"@type": "ListItem"';
								}else{
									$html .= '"@type":"ListItem",';
						      $html .= '"position":' . $list_key . ',';
						      $html .= '"url":"' . $list_item['url'] . '"';
								}
				      $html .= '}';
				      if($mdp['list_count'] != $list_key){
				        $html .= ',';
				      }
				    }
				    $html .= ']';
				    $html .= '}';
				    $html .= '</script>';
				    $html .= '<!--' . $this->ver . ' List end-->';
				  }
				//list

				$this->microdataToTheme($data, $html);
			}
		//templateCategory

		//templateVideo
			private function templateVideo(&$data, $mdp, $html = ''){
				foreach($mdp['video'] as $video){
					$html .= '<!--' . $this->ver . ' Video start-->';
					$html .= '<script type="application/ld+json">';
					$html .= '{"@context": "https://schema.org",';
					$html .= '"@type": "VideoObject",';
					$html .= '"name": "' . $video['title'] . '",';
					$html .= '"description": "' . $video['description'] . '",';
					$html .= '"thumbnailUrl": "' . $video['thumb'] . '",';
					$html .= '"uploadDate": "' . $video['date_added'] . '",';
					$html .= '"contentUrl": "' . $video['video'] . '",';
					$html .= '"embedUrl": "' . $video['video_link'] . '"}'; //,
					//$html .= '"regionsAllowed": ["UA"]}';
					$html .= '</script>';
					$html .= '<!--' . $this->ver . ' Video end-->';
				}

				$this->microdataToTheme($data, $html);
			}
		//templateVideo

		//templateArticle
		  private function templateArticle(&$data, $mdp, $html = ''){
		    $this->templateBreadcrumbs($html, $mdp);

		    $html .= '<!--' . $this->ver . ' Article start-->';
		    $html .= '<script type="application/ld+json">';
		    $html .= '{';
		    $html .= '"@context": "https://schema.org",';
		    $html .= '"@type": "Article",';
		    $html .= '"mainEntityOfPage":{';
		    $html .= '"@type":"WebPage",';
		    $html .= '"@id": "' . $mdp['url'] . '"';
		    $html .= '},';
		    $html .= '"headline": "' . $mdp['title'] . '",';
				$html .= '"image": [';
				$imagei = 0;
				foreach($mdp['text_images'] as $image => $sizes){
				  $imagei++;
				  $html .= '{';
				  $html .= '"@type": "ImageObject",';
				  $html .= '"url": "' . $image . '",';
				  $html .= '"width": "' . $sizes['image_width'] . '",';
				  $html .= '"height": "' . $sizes['image_height'] . '"';
				  $html .= '}';
				  if($imagei != count($mdp['text_images'])){
				    $html .= ',';
				  }
				}
				$html .= '],';
		    $html .= '"datePublished": "' . $mdp['date'] . '",';
		    $html .= '"dateModified": "' . $mdp['date'] . '",';
		    $html .= '"author": {';
		    $html .= '"@type": "Person",';
		    $html .= '"name": "' . $mdp['author'] . '",';
		    $html .= '"url": "' . HTTP_SERVER . '"';
		    $html .= '},';
		    $html .= '"publisher": {';
		    $html .= '"@type": "Organization",';
		    $html .= '"image": "' . $mdp['logo'] . '",';
		    $html .= '"name": "' . $mdp['author'] . '",';
		    $html .= '"logo": {';
		    $html .= '"@type": "ImageObject",';
		    $html .= '"url": "' . $mdp['logo'] . '"';
		    $html .= '}';
		    $html .= '},';
		    $html .= '"description":"' . $mdp['description'] . '"';
		    $html .= '}';
		    $html .= '</script>';
		    $html .= '<!--' . $this->ver . ' Article end-->';

		    $this->microdataToTheme($data, $html);
		  }
		//templateArticle

		//templateBlog
		  private function templateBlog(&$data, $mdp, $html = ''){
		    $this->templateBreadcrumbs($html, $mdp);

		    $html .= '<!--' . $this->ver . ' Blog start-->';
        $html .= '<script type="application/ld+json">';
        $html .= '{';
        $html .= '"@context": "https://schema.org",';
        $html .= '"@type": "' . $mdp['blog_type'] . '",';
        $html .= '"mainEntityOfPage":{';
        $html .= '"@type":"WebPage",';
        $html .= '"@id": "' . $mdp['url'] . '"';
        $html .= '},';
        $html .= '"headline": "' . $mdp['title'] . '",';
				$html .= '"image": [';
				$imagei = 0;
				foreach($mdp['text_images'] as $image => $sizes){
					$imagei++;
					$html .= '{';
					$html .= '"@type": "ImageObject",';
					$html .= '"url": "' . $image . '",';
	        $html .= '"width": "' . $sizes['image_width'] . '",';
	        $html .= '"height": "' . $sizes['image_height'] . '"';
					$html .= '}';
					if($imagei != count($mdp['text_images'])){
						$html .= ',';
					}
				}
				$html .= '],';
        $html .= '"datePublished": "' . $mdp['date'] . '",';
        $html .= '"dateModified": "' . $mdp['date'] . '",';
        $html .= '"author": {';
        $html .= '"@type": "Person",';
        $html .= '"name": "' . $mdp['blog_author'] . '",';
        $html .= '"url": "' . $mdp['blog_author_url'] . '"';
        $html .= '},';
        $html .= '"publisher": {';
        $html .= '"@type": "Organization",';
        $html .= '"image": "' . $mdp['logo'] . '",';
        $html .= '"name": "' . $mdp['store_name'] . '",';
        $html .= '"description": "' . $mdp['blog_org_desc'] . '",';
        $html .= '"url": "' . HTTPS_SERVER . '",';
        $html .= '"logo": {';
        $html .= '"@type": "ImageObject",';
        $html .= '"url": "' . $mdp['logo'] . '"';
        $html .= '}';
        $html .= '},';
        $html .= '"description":"' . $mdp['blog_desciption'] . '"';
        $html .= '}';
        $html .= '</script>';
		    $html .= '<!--' . $this->ver . ' Blog end-->';

		    $this->microdataToTheme($data, $html);
		  }
		//templateBlog

		//templateLocalCompany
			private function templateLocalCompany(&$data, $mdp, $html = ''){
				$route = isset($this->request->get['route'])?$this->request->get['route']:'common/home';
				if($this->mdp->mdc('company') && $route == 'common/home'){
		      $html .= '<!--' . $this->ver . ' Search start-->';
		      $html .= '<script type="application/ld+json">';
		      $html .= '{';
		      $html .= '"@context": "https://schema.org",';
		      $html .= '"@type": "WebSite",';
		      $html .= '"url": "' . HTTPS_SERVER . '",';
					$html .= '"name": "' . $mdp['store_name'] . '",';
					$html .= '"alternateName": "' . $mdp['author'] . '",';
		      $html .= '"potentialAction": {';
		      $html .= '"@type": "SearchAction",';
		      $html .= '"target": {';
		      $html .= '"@type": "EntryPoint",';
		      $html .= '"urlTemplate": "' . HTTPS_SERVER . 'index.php?route=product/search&search={search_term_string}"';
		      $html .= '},';
		      $html .= '"query-input": "required name=search_term_string"';
		      $html .= '}';
		      $html .= '}';
		      $html .= '</script>';
		      $html .= '<!--' . $this->ver . ' Search end-->';

		      $html .= '<!--' . $this->ver . ' LocalBusiness start-->';
		      $html .= '<script type="application/ld+json">';
		      $html .= '{';
		      $html .= '"@context": "https://schema.org",';
		      $html .= '"@type": "' . $mdp['store'] . '",';
		      $html .= '"name": "' . $mdp['store_name'] . '",';
		      $html .= '"url": "' . HTTPS_SERVER . '",';
		      $html .= '"image": "' . $mdp['logo'] . '",';
		      $html .= '"logo": "' . $mdp['logo'] . '",';
		      if($mdp['telephone']){
		        $html .= '"telephone" : "' . $mdp['telephone'] . '",';
		      }
		      $html .= '"email": "' . $mdp['email'] . '",';
		      $html .= '"priceRange": "' . $mdp['code'] . '",';
					$html .= '"hasMerchantReturnPolicy": {';
		        $html .= '"@type": "MerchantReturnPolicy",';
		        $html .= '"applicableCountry": "' . $mdp['shipping_country'] . '",';
		        $html .= '"returnPolicyCountry": "' . $mdp['shipping_country'] . '",';
		        $html .= '"returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",';
		        $html .= '"merchantReturnDays": "' . $mdp['return_days'] . '",';
		        $html .= '"returnMethod": "https://schema.org/ReturnByMail",';
		        $html .= '"refundType": "https://schema.org/FullRefund",';
		        $html .= '"returnFees": "https://schema.org/FreeReturn"';
		      $html .= '},';
		      $html .= '"hasMap": "' . trim($this->mdp->mdc('map')) . '",';
		      if($mdp['address']){
	          $html .= '"address": {';
	          $html .= '"@type": "PostalAddress",';
	          $html .= '"addressLocality": "' . $mdp['address']['locality'] . '",';
						$html .= '"addressCountry": "' . $mdp['address']['country2'] . '",';
	          $html .= '"postalCode": "' . $mdp['address']['postal'] . '",';
	          $html .= '"streetAddress": "' . $mdp['address']['street'] . '"';
	          $html .= '},';
	          $html .= '"location": {';
	          $html .= '"@type": "Place",';
	          $html .= '"address": {';
	          $html .= '"@type": "PostalAddress",';
	          $html .= '"addressLocality": "' . $mdp['address']['locality'] . '",';
						$html .= '"addressCountry": "' . $mdp['address']['country2'] . '",';
	          $html .= '"postalCode": "' . $mdp['address']['postal'] . '",';
	          $html .= '"streetAddress": "' . $mdp['address']['street'] . '"';
	          $html .= '},';
	          $html .= '"geo": {';
	          $html .= '"@type": "GeoCoordinates",';
	          $html .= '"latitude": "' . $mdp['address']['latitude'] . '",';
	          $html .= '"longitude": "' . $mdp['address']['longitude'] . '"';
	          $html .= '}';
	          $html .= '},';
		      }
		      $html .= '"potentialAction": {';
		      $html .= '"@type": "SearchAction",';
		      $html .= '"target": "' . HTTPS_SERVER . 'index.php?route=product/search&search={search_term_string}",';
		      $html .= '"query-input": "required name=search_term_string"';
		      $html .= '}';
		      if($mdp['telephones']){
		        $html .= ',';
		        $html .= '"contactPoint" : [';
		        $pi = 1;
		        foreach($mdp['telephones'] as $phone){
		          $html .= '{';
		          $html .= '"@type" : "ContactPoint",';
		          $html .= '"telephone" : "' . $phone . '",';
		          $html .= '"contactType" : "customer service"';
		          $html .= '}';
		          if($pi != count($mdp['telephones'])){
		            $html .= ',';
		          }
		          $pi++;
		        }
		        $html .= ']';
		      }
		      if($mdp['groups']){
		        $html .= ',';
		        $html .= '"sameAs" : [';
		        $gi = 1;
		        foreach($mdp['groups'] as $group){
		          $html .= '"' . $group . '"';
		          if($gi != count($mdp['groups'])){
		            $html .= ',';
		          }
		          $gi++;
		        }
		        $html .= ']';
		      }
		      if($mdp['hours']){
		        $html .= ',';
		        $html .= '"openingHoursSpecification":[';
		        $ohi = 1;
		        foreach($mdp['hours'] as $day => $oh){
		          $html .= '{';
		          $html .= '"@type": "OpeningHoursSpecification",';
		          $html .= '"dayOfWeek": "' . $day . '",';
		          $html .= '"opens": "' . $oh['open'] . '",';
		          $html .= '"closes": "' . $oh['close'] . '"';
		          $html .= '}';
		          if($ohi != count($mdp['hours'])){
		            $html .= ',';
		          }
		          $ohi++;
		        }
		        $html .= ']';
		      }
		      $html .= '}';
		      $html .= '</script>';
		      $html .= '<!--' . $this->ver . ' LocalBusiness end-->';

		      //hcard
		        if($this->mdp->mdc('hcard')){
		          $html .= '<!--' . $this->ver . ' hCard start [hCard] -->';
		          $html .= '<span class="vcard">';
		          $html .= '<span class="fn org"><span class="value-title" title="' . $mdp['author'] . '"></span></span>';
		          $html .= '<span class="org"><span class="value-title" title="' . $mdp['author'] . '"></span></span>';
		          $html .= '<span class="url"><span class="value-title" title="' . HTTPS_SERVER . '"></span></span>';
		          if($mdp['address']){
	              $html .= '<span class="adr">';
	              $html .= '<span class="locality"><span class="value-title" title="' . $mdp['address']['locality'] . '"></span></span>';
	              $html .= '<span class="street-address"><span class="value-title" title="' . $mdp['address']['street'] . '"></span></span>';
	              $html .= '<span class="postal-code"><span class="value-title" title="' . $mdp['address']['postal'] . '"></span></span>';
	              $html .= '</span>';
	              $html .= '<span class="geo">';
	              $html .= '<span class="latitude"><span class="value-title" title="' . $mdp['address']['latitude'] . '"></span></span>';
	              $html .= '<span class="longitude"><span class="value-title" title="' . $mdp['address']['longitude'] . '"></span></span>';
	              $html .= '</span>';
		          }
		          if($mdp['telephones']){
		            foreach($mdp['telephones'] as $phone){
		              $html .= '<span class="tel"><span class="value-title" title="' . $phone . '"></span></span>';
		            }
		          }
		          $html .= '<span class="photo"><span class="value-title" title="' . $mdp['logo'] . '"></span></span>';
		          $html .= '</span>';
		          $html .= '<!--' . $this->ver . ' hCard end-->';
		        }
		      //hcard

		      $this->microdataToTheme($data, $html);
		    }
		  }
		//templateLocalCompany

		//microdataToTheme
			private function microdataToTheme(&$data, $html){
				if(isset($data['footer'])){
					$data['footer'] = str_replace('><', '>' . PHP_EOL . '<', $html . PHP_EOL) . $data['footer'];
				}
			}
		//microdataToTheme

	//template functions

	//getData functions

		//getStatus
			private function getStatus($data){
				$return = 1;
				$data['function'] = str_replace('article', 'information', $data['function']);

				if(!in_array($data['function'], ['ogcompany','category','product','article','blog','information','company','manufacturer']) || !$this->mdp->mdc('status') || !$this->mdp->a){$return = 0;}
				if(in_array($data['function'], ['category','product','information','article','blog','manufacturer']) && !$this->mdp->mdc($data['function'])){$return = 0;}
				if($data['function'] == 'product' && !isset($data['product_id'])){$return = 0;}

				return $return;
			}
		//getStatus

		//getLogo
			private function getLogo(){
				$logo = $this->config->get('config_logo');
				if(is_array($logo) && isset($logo[$this->config->get('config_language_id')])){
					$logo = $logo[$this->config->get('config_language_id')];
				}
				if($this->mdp->mdc('logoalt') && is_file(DIR_IMAGE . $this->mdp->mdc('logoalt'))){
					$logo = $this->mdp->mdc('logoalt');
				}

				return $logo;
			}
		//getLogo

		//getVideo
			private function getVideo(&$data, &$mdp){
				$mdp['video'] = [];

				if($this->mdp->mdc('video')){
					$video_from = '';
					foreach($data as $item){
						if(!is_array($item)){
							$video_from .= ' ' . $item;
						}else{ //array
							foreach($item as $item2){
								if(!is_array($item2)){
									$video_from .= ' ' . $item2;
								}else{
									foreach($item2 as $item3){
										if(!is_array($item3)){
											$video_from .= ' ' . $item3;
										}
									}
								}
							}
						}
					}

					if($video_from){
						//YT
							preg_match_all('/<iframe[^>]*src\s*=\s*"?\S*youtube.com(?:\/[^\s"]*)?"?[^>]*>.*?<\/iframe>/', $video_from, $iframes);
							foreach($iframes[0] as $iframe){
								$dom = new DOMDocument();
								libxml_use_internal_errors(true);
								$dom->loadHTML($iframe);
								libxml_clear_errors();
								$xpath = new DOMXpath($dom);
								foreach ($xpath->query("//iframe") as $node){
									$video_src = $node->getAttribute("src");
									$video_data = $this->getVideoData($video_src);
									if($video_data){
										$mdp['video'][$video_src] = $video_data;
									}
								}
							}
						//YT

						//VM
							preg_match_all('/<iframe[^>]*src\s*=\s*"?\S*vimeo.com(?:\/[^\s"]*)?"?[^>]*>.*?<\/iframe>/', $video_from, $iframes);
							foreach($iframes[0] as $iframe){
								$dom = new DOMDocument();
								libxml_use_internal_errors(true);
								$dom->loadHTML($iframe);
								libxml_clear_errors();
								$xpath = new DOMXpath($dom);
								foreach ($xpath->query("//iframe") as $node){
									$video_src = $node->getAttribute("src");
									$video_data = $this->getVideoData($video_src);
									if($video_data){
										$mdp['video'][$video_src] = $video_data;
									}
								}
							}
						//VM

						//links
							preg_match_all('/(https?:\/\/(?:www\.)?(?:youtube\.com\/(?:watch\?v=|playlist\?list=)|youtu\.be\/)[a-zA-Z0-9_-]+)/i', $video_from, $links);
							foreach($links[0] as $video_src){
								$video_data = $this->getVideoData($video_src);
								if($video_data){
									$mdp['video'][$video_src] = $video_data;
								}
							}
						//links
					}

					$this->templateVideo($data, $mdp);
				}
			}
		//getVideo

		//getVideoData
			public function getVideoData($video_src){
				$video = [];

				$video = $this->videoCache($video_src);

				if(!$video){
					$video_src_exploded = explode('/',$video_src);

					$video_id = end($video_src_exploded);
					$video_id = explode('?',$video_id)[0];

					if($video_id == 'watch'){
						$video_id_data = explode('?v=', end($video_src_exploded));
						if(isset($video_id_data[1])){
							$video_id = $video_id_data[1];
						}
					}

					if(strpos($video_src, 'youtu') !== FALSE){
						$video_data = @file_get_contents('https://www.googleapis.com/youtube/v3/videos?id=' . $video_id . '&key=' . $this->mdp->mdc('video_key') . '&part=snippet');
						if($video_data && is_array($video_data) && isset($video_data['items'][0]) && $video_data['items'][0]){
							$video_data = json_decode($video_data, true);
							$video_data = $video_data['items'][0]['snippet'];

							$description = $this->clearText($this->clearDiv($video_data['description']));
							if(!$description){
								$description = $this->clearText($video_data['title']);
							}

							$video = [
								'video' => 'https://www.youtube.com/embed/' . $video_id . '?autoplay=1',
								'thumb' => 'https://img.youtube.com/vi/'.$video_id.'/hqdefault.jpg',
								'video_link' => $video_src,
								'date_added' => $video_data['publishedAt'],
								'title' => $this->clearText($video_data['title']),
								'description' => $description,
							];
						}
					}else{ //vm
						$video_data = @file_get_contents('https://vimeo.com/api/v2/video/' . $video_id . '.json');
						if($video_data){
							$video_data = json_decode($video_data, true);
							$video_data = $video_data[0];

							$description = $this->clearText($this->clearDiv($video_data['description']));
							if(!$description){
								$description = $this->clearText($video_data['title']);
							}

							$video = [
								'video' => $video_src,
								'thumb' => 'https://vumbnail.com/'.$video_id.'.jpg',
								'video_link' => $video_src,
								'date_added' => str_replace('', 'T', $video_data['upload_date']) . 'Z',
								'title' => $this->clearText($video_data['title']),
								'description' => $description,
							];
						}
					}

					if($video){
						if(!$video['description']){
							$video['description'] = $video['title'];
						}

						if(!$video['title'] && !$video['description']){
							$video = [];
						}

						if($video){
							$this->videoCache($video_src, $video);
						}
					}
				}

				return $video;
			}
		//getVideoData

		//videoCache
			private function videoCache($video_src, $video_data = []){
				$video_from_file = '';
				$filedata = [];

			  if($video_src){
			    if(is_file(DIR_SYSTEM . $this->mdp->vidfile)){
			      if(date("m", filectime(DIR_SYSTEM . $this->mdp->vidfile)) == date('m')){
			        $filedata = json_decode(file_get_contents(DIR_SYSTEM . $this->mdp->vidfile), true);
			        if(isset($filedata[$video_src])){
			          $video_from_file = $filedata[$video_src];
			        }
			      }
			    }
			  }

				if($video_from_file == '' && $video_data){
					$filedata[$video_src] = $video_data;

					$fp = fopen(DIR_SYSTEM . $this->mdp->vidfile, "w");
					fwrite($fp, json_encode($filedata));
					fclose($fp);
				}

				return $video_from_file;
			}
		//videoCache

		//getCompany
			private function getCompany($data, &$mdp){
				$mdp['email'] = $this->mdp->mdc('email');
				if(!$mdp['email']){
					$mdp['email'] = $this->config->get('config_email');
				}
				if(is_array($mdp['email'])){
					$mdp['email'][(int)$this->config->get('config_language_id')];
				}

				$mdp['store'] = ($this->mdp->mdc('store_type') !== '')?$this->getSchemaStoreType($this->mdp->mdc('store_type')):'Store';
				$mdp['shipping_country'] = ($this->mdp->mdc('shipping_country') != '')?$this->mdp->mdc('shipping_country'):'UA';
				$mdp['return_days'] = ($this->mdp->mdc('return_days') != '')?$this->mdp->mdc('return_days'):'14';

				$this->getStoreId($data, $mdp);
				$this->getGroup($data, $mdp);
				$this->getHour($data, $mdp);
				$this->getAddress($data, $mdp);
				$this->getTelephone($data, $mdp);
			}
		//getCompany

		//getStoreId
			private function getStoreId($data, &$mdp){
				$mdp['store_id'] = '';

				if($this->mdp->mdc('front_stores')){
					foreach($this->mdp->mdc('front_stores') as $result){
						if(strpos('https://' . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'], $result['url']) !== false){
							$mdp['store_id'] = $result['store_id'];
						}
					}
				}
			}
		//getStoreId

		//getAddress
			private function getAddress($data, &$mdp, $first = true){
			  $mdp['address'] = [];
				$coordinates =[];

				if($this->mdp->mdc('locations_coordinates' . $mdp['store_id'])){
					$coordinates = explode(";", str_replace(",", ";", $this->mdp->mdc('locations_coordinates' . $mdp['store_id'])));
				}

				$country 	= $this->mdp->mdc('locations_country' . $mdp['store_id']);
				$locality = $this->mdp->mdc('locations_city' . $mdp['store_id']);
				$street 	= $this->mdp->mdc('locations_address' . $mdp['store_id']);
				$postal 	= $this->mdp->mdc('locations_code' . $mdp['store_id']);

				if(isset($coordinates[0]) && isset($coordinates[1]) && $country && $locality && $street && $postal){
					$mdp['address'] = [
						'latitude'	=> trim($coordinates[0]),
						'longitude' => trim($coordinates[1]),
						'locality' 	=> trim($locality),
						'street'   	=> trim($street),
						'postal' 	  => trim($postal),
						'country' 	=> trim($country),
						'country2' 	=> ($this->mdp->mdc('shipping_country') != '')?$this->mdp->mdc('shipping_country'):'UA'
					];
				}
			}
		//getAddress

		//getTelephone
			private function getTelephone($data, &$mdp){
				$mdp['telephone']  = '';
				$mdp['telephones'] = [];

				if($this->mdp->mdc('phones' . $mdp['store_id'])){
					$phones_data = $this->mdp->mdc('phones')?array_diff(array_map('trim', explode(",", $this->mdp->mdc('phones' . $mdp['store_id']))), ['']):false;
					if($phones_data){
						foreach($phones_data as $phone){
							$mdp['telephones'][] = $phone;
						}
					}
					$mdp['telephone'] = $mdp['telephones'][0];
				}
				if(!$mdp['telephone']){
					$mdp['telephone'] = '';
				}
			}
		//getTelephone

		//getGroup
			private function getGroup($data, &$mdp){
				$mdp['groups'] = [];

				if($this->mdp->mdc('groups' . $mdp['store_id'])){
					$groups_data = $this->mdp->mdc('phones')?array_diff(array_map('trim', explode(",", $this->mdp->mdc('groups' . $mdp['store_id']))), ['']):false;
					if($groups_data){
						foreach($groups_data as $phone){
							$mdp['groups'][] = $phone;
						}
					}
				}
			}
		//getGroup

		//getHour
			private function getHour($data, &$mdp){
				$mdp['hours'] = [];

				foreach(["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"] as $i => $day){$i++;
					if($this->mdp->mdc('oh_'.$i)){
						$pre_data = explode("-", $this->mdp->mdc('oh_'.$i));
						if(isset($pre_data[0]) && isset($pre_data[1])){
							$mdp['hours'][$day] = ['open' => trim($pre_data[0]),'close' => trim($pre_data[1])];
						}
					}
				}
			}
		//getHour

		//getAuthor
			private function getAuthor(&$data, &$mdp){
				$name = $this->config->get('config_name');
				if(is_array($name) && isset($name[$this->config->get('config_language_id')])){
					$name = $name[$this->config->get('config_language_id')];
				}

				$mdp['author'] = $name;
			}
		//getAuthor

		//getProductImages
			private function getProductImages(&$data, &$mdp){
				$mdp['images'] = [];
				$mdp['image'] = isset($data['popup'])?$this->clearImage($data['popup']):'';
				$mdp['thumb'] = isset($data['thumb'])?$this->clearImage($data['thumb']):'';

				if(isset($data['raw_data']['image'])){
					foreach(['image','thumb'] as $image){
						if(!$mdp[$image]){
							$mdp[$image] = $this->clearImage(HTTPS_SERVER . 'image/' . $data['raw_data']['image']);
						}
					}
				}

				if($this->mdp->mdc('product_gallery') && isset($data['images']) && is_array($data['images']) && $data['images']){
					foreach($data['images'] as $image){
						if(is_array($image) && isset($image['thumb']) && $image['popup']){
							$mdp['images'][] = [
								'thumb' => $this->clearImage($image['thumb']),
								'popup' => $this->clearImage($image['popup'])
							];
						}
					}
				}
			}
		//getProductImages

		//getThisUrl
			private function getThisUrl(&$data, &$mdp){
				$mdp['url'] = '';

				if(!isset($data['breadcrumbs'])){
					$data['breadcrumbs'] = ['ogcompany'];
				}

				$url = end($data['breadcrumbs']);

				if(isset($url['href'])){
					$mdp['url'] = $url['href'];
				}elseif(isset($data['share'])){
					$mdp['url'] = $data['share'];
				}

				if(isset($this->request->get['attrb']) || isset($this->request->get['ocfilter_page_id']) || !$mdp['url']){
					$mdp['url'] = 'https://' . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
				}
			}
		//getThisUrl

		//getOgImageSize
			private function getOgImageSize(&$data, &$mdp){
				$mdp['image_width']  = 0;
				$mdp['image_height'] = 0;

				if(!isset($mdp['image'])){
					if($this->mdp->mdc('logosize_1') && $this->mdp->mdc('logosize_2')){
						$this->load->model('tool/image');
						$mdp['image'] = $this->model_tool_image->resize($this->getLogo(), $this->mdp->mdc('logosize_1'), $this->mdp->mdc('logosize_2'));
						$mdp['image_width']  = $this->mdp->mdc('logosize_1');
						$mdp['image_height'] = $this->mdp->mdc('logosize_2');
					}else{
						$mdp['image'] = HTTPS_SERVER . 'image/' . $this->getLogo();
					}
				}

				$image_full = str_replace(HTTPS_SERVER . 'image/', DIR_IMAGE, $mdp['image']);
				if(is_file($image_full) && !$mdp['image_width']){
					$image_data = file_get_contents($image_full);
					if($image_data){
						$image_size = getimagesizefromstring($image_data);
						$mdp['image_width']  = (isset($image_size[0]) && $image_size[0])?$image_size[0]:1200;
						$mdp['image_height'] = (isset($image_size[1]) && $image_size[1])?$image_size[1]:600;
					}
				}
			}
		//getOgImageSize

		//getImageSize
			private function getImageSize($image){
				$sizes = ['image_width'=>false,'image_height'=>false];

				if($image){
					$image_full = str_replace(HTTPS_SERVER . 'image/', DIR_IMAGE, $image);
					if(substr($image_full, 0, strlen(DIR_IMAGE)) != DIR_IMAGE){
						$image_full = str_replace('/system/', '', DIR_SYSTEM) . $image;
					}
					if(is_file($image_full)){
						$image_data = @file_get_contents($image_full);
						if($image_data){
							$image_size = getimagesizefromstring($image_data);
							$sizes['image_width']  = (isset($image_size[0]) && $image_size[0])?$image_size[0]:false;
							$sizes['image_height'] = (isset($image_size[1]) && $image_size[1])?$image_size[1]:false;
						}
					}
				}

				return $sizes;
			}
		//getImageSize

		//getCategoryList
			private function getCategoryList($data, &$mdp){
				$mdp['list'] = [];
				$mdp['list_extra'] = $this->mdp->mdc('category_list_extended');

				foreach(['shipping_cost'=>'50','shipping_country'=>'UA','shipping_time1_min'=>'0','shipping_time1_max'=>'1','shipping_time2_min'=>'1','shipping_time2_max' => '3','return_days'=>'14'] as $gvar => $gdef){
					$mdp[$gvar] = ($this->mdp->mdc($gvar) != '')?$this->mdp->mdc($gvar):$gdef;
				}

				if($this->mdp->mdc('category_list')){
				  $item = 1;
					if(isset($data['products']) && $data['products']){
					  foreach($data['products'] as $product){
					    if(isset($product['href']) && $product['href']){

								if($mdp['list_extra']){
									$reviewCount = 0;
									$rating = 0;
									if($this->mdp->mdc('product_reviews')){
							      $reviews_query = $this->db->query("SELECT rating FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product['product_id'] . "' AND status = 1 AND rating > 0");
							      if($reviews_query->rows){
							        $all_rating = 0;
							        foreach($reviews_query->rows as $key => $review){
							          $all_rating += $review['rating'];
							        }
							        $reviewCount = $reviews_query->num_rows;
							        $rating = round($all_rating/$reviewCount, 2);
							      }
							    }

									if(!isset($product['quantity'])){
										$quantity_query = $this->db->query("SELECT quantity FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product['product_id'] . "'");
										$product['quantity'] = $quantity_query->row['quantity'];
									}

									$mdp['list'][$item] = [
										'thumb' => $product['thumb'],
										'url' => $product['href'],
										'name' => $this->clearText($product['name']),
										'price' => $this->clearPrice($product),
										'rating' => $rating,
										'reviewCount' => $reviewCount,
										'stock' => $product['quantity']?'InStock':'OutOfStock',
									];
								}else{
									$mdp['list'][$item] = ['url' => $product['href']];
								}

					      $item++;
					    }
					  }
				  }
				  $mdp['list_count'] = $item-1;
				}
			}
		//getCategoryList

		//getCategoryReviews
			private function getCategoryReviews($data, &$mdp){
				$mdp['rating_value'] = 0;
				$mdp['rating_count'] = 0;

				if($this->mdp->mdc('category_review') && $data['add_data']){
				  $rating_summ = 0;
				  foreach($data['add_data'] as $product){
				    if(isset($product['rating']) && (float)$product['rating']){
				      $mdp['rating_count']++;
				      $rating_summ += $product['rating'];
				    }
				  }
				  if($rating_summ && $mdp['rating_count']){
				    $mdp['rating_value'] = round($rating_summ/$mdp['rating_count'], 2);
				  }
				}
			}
		//getCategoryReviews

		//getPriceRange
			private function getPriceRange($data, &$mdp){
			  $this->getMinMaxCategory($mdp);

				if(isset($this->request->get['attrb']) || isset($this->request->get['ocfilter_page_id']) || !$mdp['total']){
					$prices = [];
					$mdp['total'] = 0;
					if(isset($data['products']) && $data['products']){
						foreach($data['products'] as $product){
							$prices[] = $this->clearPrice($product);
							$mdp['total']++;
						}
					}

					if($mdp['total'] > 1){
						$prices = array_filter($prices);
						if(!empty($prices)){
							$mdp['min'] = min($prices);
							$mdp['max'] = max($prices);
						}else{
							$mdp['min'] = 0;
							$mdp['max'] = 0;
						}
					}
				}
			}
		//getPriceRange

		//getMinMaxCategory
			private function getMinMaxCategory(&$mdp){
				$mdp['total'] = 0;
				$mdp['min'] 	= 0;
				$mdp['max'] 	= 0;
				$filedata 		= [];

				if(isset($this->request->get['path']) && $this->request->get['path'] && $this->mdp->mdc('category_range')){
					$parts = explode('_', (string)$this->request->get['path']);
					$category_id = array_pop($parts);

					if(is_file(DIR_SYSTEM . $this->mdp->datfile)){
						if(date("d", filectime(DIR_SYSTEM . $this->mdp->datfile)) == date('d')){
							$filedata = json_decode(file_get_contents(DIR_SYSTEM . $this->mdp->datfile), true);
							if(isset($filedata[$category_id])){
								$mdp['total'] = $filedata[$category_id]['total'];
								$mdp['min'] 	= $filedata[$category_id]['min'];
								$mdp['max'] 	= $filedata[$category_id]['max'];
							}
						}
					}

					if(!$mdp['total']){ //если нет количества товаров (означает что ранее не забрали с файла)
						$mm_query = $this->getMinMaxSQL($category_id);
						if($mm_query->num_rows > 1){
							$mdp['total'] = $mm_query->num_rows;

							$prices = [];
							foreach($mm_query->rows as $row){
								$prices[] = (isset($row['special']) && $row['special'])?$row['special']:$row['price'];
							}

							$mdp['min'] = $this->clearPriceItem($this->currency->format($this->tax->calculate(round(min($prices), 2), 0, $this->config->get('config_tax')), $this->session->data['currency']));
							$mdp['max'] = $this->clearPriceItem($this->currency->format($this->tax->calculate(round(max($prices), 2), 0, $this->config->get('config_tax')), $this->session->data['currency']));

							$filedata[$category_id]['total'] = $mdp['total'];
							$filedata[$category_id]['min'] 	 = $mdp['min'];
							$filedata[$category_id]['max']   = $mdp['max'];

							$fp = fopen(DIR_SYSTEM . $this->mdp->datfile, "w");
							fwrite($fp, json_encode($filedata));
							fclose($fp);
						}
					}
				}
			}
		//getMinMaxCategory

		//getMinMaxSQL
			private function getMinMaxSQL($category_id){
				$sub_category = false;

				$sql = "SELECT p.price,
								(SELECT price FROM " . DB_PREFIX . "product_special ps
									WHERE ps.product_id = p.product_id
									AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
									AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW())
									AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))
									ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

				if($sub_category){
					$sql .= " FROM " . DB_PREFIX . "category_path cp
										LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)
										LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)
										LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
										WHERE cp.path_id = '" . (int)$category_id . "'";
				}else{
					$sql .= " FROM " . DB_PREFIX . "product p
										LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
										LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
										WHERE p2c.category_id = '" . (int)$category_id . "'";
				}

				$sql .=	"AND p.status = 1
								 AND p.price > 0
								 AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
								 AND p.date_available <= '" . $this->db->escape(date('Y-m-d')) . "'";

				return $this->db->query($sql);
			}
		//getMinMaxSQL

		//getCategoryImages
			private function getCategoryImages(&$data, &$mdp){
				$mdp['images'] = [];

				$mdp['image'] = '';
				if(isset($data['thumb']) && $data['thumb']){
					$mdp['image'] = $this->clearImage($data['thumb']);
				}

				if($this->config->get('fv_hl_image')){
		      $data['raw_data']['image'] = $this->config->get('fv_hl_image');
		    }

				if(!$mdp['image'] && isset($data['raw_data']['image']) && $data['raw_data']['image']){
					$mdp['image'] = $this->clearImage(HTTPS_SERVER . 'image/' . $data['raw_data']['image']);
				}

				if(!$mdp['image'] && isset($data['products'][0]['thumb']) && $data['products'][0]['thumb']){
					$mdp['image'] = $this->clearImage($data['products'][0]['thumb']);
				}

				if($this->mdp->mdc('category_gallery') && isset($data['products']) && $data['products']){
					$thumb_width = $this->config->get(((substr(VERSION, 0, 1) == 3)?'theme_':'') . $this->config->get('config_theme') . '_image_product_width');
					$thumb_height = $this->config->get(((substr(VERSION, 0, 1) == 3)?'theme_':'') . $this->config->get('config_theme') . '_image_product_height');

					if((substr(VERSION, 0, 3) == '2.1')){
						$thumb_width = $this->config->get('config_image_product_width');
						$thumb_height = $this->config->get('config_image_product_height');
					}

					if((int)$this->mdp->mdc('category_thumb_1') && (int)$this->mdp->mdc('category_thumb_2')){
						$thumb_width = (int)$this->mdp->mdc('category_thumb_1');
						$thumb_height = (int)$this->mdp->mdc('category_thumb_2');
					}

					if($data['add_data']){
						foreach($data['add_data'] as $product){
							if(isset($product['image']) && $product['image']){
								$popup = HTTPS_SERVER . 'image/' . $product['image'];
								if((int)$this->mdp->mdc('category_popup_1') && (int)$this->mdp->mdc('category_popup_2')){
									$popup = $this->model_tool_image->resize($product['image'], (int)$this->mdp->mdc('category_popup_1'), (int)$this->mdp->mdc('category_popup_2'));
								}
								$thumb = $this->model_tool_image->resize($product['image'], $thumb_width, $thumb_height);

								$mdp['images'][] = [
									'name'  => $this->clearText($product['name']),
									'thumb' => $this->clearImage($thumb),
									'popup' => $this->clearImage($popup),
									'date_added' => date("Y-m-dT00:00:00P", strtotime($product['date_added']))
								];
							}
						}
					}
				}

			}
		//getCategoryImages

		//getDescription
			private function getDescription($data, &$mdp){
				if(!isset($data['description'])){
					$data['description'] = '';
				}
				$mdp['description'] = trim($this->clearText($data['description']));
				if(!$mdp['description']){
					$mdp['description'] = $this->clearText($this->document->getDescription()?$this->document->getDescription():$mdp['title']);
				}
				if(isset($this->request->get['attrb']) || isset($this->request->get['ocfilter_page_id'])){
				  if(isset($data['ocfilter_description_bottom']) && $data['ocfilter_description_bottom']){
				    $mdp['description'] = $this->clearText($data['ocfilter_description_bottom']);
				  }
				}
				if(is_string($this->config->get('h_descript'))){
					$mdp['description'] = $this->clearText($this->config->get('h_descript'));
				}

				$mdp['description'] = $this->clearDiv($mdp['description']);

				$mdp['date_added'] = date('Y-m-dT00:00:00P', filectime(DIR_SYSTEM . 'library/microdatapro.php'));
				if(isset($data['raw_data']['date_added']) && $data['raw_data']['date_added']){
					$mdp['date_added'] = date("Y-m-dT00:00:00P", strtotime($data['raw_data']['date_added']));
				}
			}
		//getDescription

		//getOgData
			private function getOgData($data, &$mdp){
				$mdp['code']	 = $this->session->data['currency'];
				$mdp['locale'] = strtolower($this->session->data['language']);
				$mdp['logo']   = $this->clearImage(HTTPS_SERVER . 'image/' . $this->getLogo());
				$mdp['date'] 	 = date('Y-m-dT00:00:00P', filectime(DIR_SYSTEM . 'library/microdatapro.php'));
				$mdp['title']	 = $this->clearText(isset($data['heading_title'])?$data['heading_title']:$this->document->getTitle());

				$mdp['url_contact'] = ($this->mdp->mdc('url_contact') == 'information/contact')?'information/contact':('information/information&information_id=' . $this->mdp->mdc('url_contact'));
				$mdp['url_privacy'] = ($this->mdp->mdc('url_privacy') == 'information/contact')?'information/contact':('information/information&information_id=' . $this->mdp->mdc('url_privacy'));
				$mdp['url_contact'] = $this->url->link($mdp['url_contact'], '', 'SSL');
				$mdp['url_privacy'] = $this->url->link($mdp['url_privacy'], '', 'SSL');

				if($this->mdp->mdc('name')){
					$store_name = $this->mdp->mdc('name');
					if(isset($store_name[$this->config->get('config_language_id')])){
						$mdp['store_name']  = $this->clearText($store_name[$this->config->get('config_language_id')]);
					}
				}

				if(!isset($data['raw_data']['meta_description'])){
					$data['raw_data']['meta_description'] = $this->document->getDescription();
				}

				if($this->mdp->mdc('opengraph_meta') && $data['raw_data']['meta_description']){
					$description = $data['raw_data']['meta_description'];
					if(is_array($description) && isset($description[(int)$this->config->get('config_language_id')])){
						$description = $description[(int)$this->config->get('config_language_id')];
					}
					if($description == ''){
						$description = $this->document->getDescription();
					}
				}elseif(isset($data['description']) && $data['description']){
					$description = $this->clearLongText($this->clearText($data['description']), 290);
				}else{
					$description = $mdp['title'];
				}
				$mdp['desc'] = $this->clearText($description);

				if($this->mdp->mdc('opengraph_meta') || $mdp['title'] == ''){
					$mdp['title'] = $this->document->getTitle();
				}

				$mdp['function'] = $data['function'];
				if(!in_array($data['function'],['product','category','acticle'])){
					$mdp['function'] = 'business.business';
				}
			}
		//getOgData

		//getProductData
			private function getProductData($data, &$mdp){
				$mdp['title'] 		  = isset($data['heading_title'])?$this->clearText($data['heading_title']):'';
				$mdp['model'] 		  = isset($data['model'])?$this->clearText($data['model']):'';
				$mdp['brand']			  = isset($data['manufacturer'])?$this->clearText($data['manufacturer']):'';
				$mdp['condition'] 	= 'NewCondition';
				$mdp['cond_small'] 	= 'new';

	      foreach(['sku','upc','ean','isbn','mpn'] as $item){
	        $mdp[$item] = ($data['raw_data'][$item] && $this->mdp->mdc($item))?$this->clearText($data['raw_data'][$item]):false;
	      }

				foreach(['shipping_cost'=>'50','shipping_country'=>'UA','shipping_time1_min'=>'0','shipping_time1_max'=>'1','shipping_time2_min'=>'1','shipping_time2_max' => '3','return_days'=>'14'] as $gvar => $gdef){
					$mdp[$gvar] = ($this->mdp->mdc($gvar) != '')?$this->mdp->mdc($gvar):$gdef;
				}
			}
		//getProductData

		//getBlogData
			private function getBlogData($data, &$mdp){
				$mdp['blog_type'] = $this->mdp->mdc('blog_type');
				$mdp['blog_author'] = $this->mdp->mdc('blog_type')?$this->mdp->mdc('blog_type'):'Article';
				$mdp['blog_author_url'] = $this->mdp->mdc('blog_author_url');
				$mdp['blog_type'] = $this->mdp->mdc('blog_type');
				$mdp['blog_org_desc'] = $this->config->get('config_meta_description');

				$mdp['blog_desciption'] = $mdp['title'];

				if(!isset($data['description'])){
					foreach(['description_short','description_full','description_bottom','description_top'] as $desc_item){
						if(isset($data[$desc_item])){
							$data['description'] = $data[$desc_item];
						}
					}
				}

				$this->getImageText($data, $mdp);

				$mdp['blog_desciption'] = $this->clearText($this->clearDiv($data['description']));

			}
		//getBlogData

		//getImageText
			private function getImageText($data, &$mdp, $product = false){
				$mdp['text_images'] = [];

				if(!isset($data['description'])){
					$data['description'] = '';
				}

				$matches = [];
				if(preg_match_all('/<img[^>]*src=["\'](.[^"\']+)["\'][^>]*>/i', $data['description'], $matches)){
					if($matches[1]){
						foreach($matches[1] as $image){
							if(substr($image, 0, 5) != 'data:'){
								$sizes = $this->getImageSize($image);
								$mdp['text_images'][$image] = $sizes;
							}
						}
					}
				}

				if(!$mdp['text_images'] && isset($mdp['image']) && $mdp['image']){
					$mdp['text_images'][$mdp['image']] = [
						'image_width'  => $mdp['image_width'],
						'image_height' => $mdp['image_height'],
					];
				}

				if($mdp['text_images'] && $product){
					foreach($mdp['text_images'] as $image => $sizes){
						$mdp['images'][] = [
							'thumb' => $image,
							'popup' => $image
						];
					}
				}
			}
		//getImageText

		//getBreadcrumbs
			private function getBreadcrumbs($data, &$mdp){
			  $mdp['breadcrumbs'] = [];
				$mdp['category'] = false;

				if(isset($data['breadcrumbs']) && $data['breadcrumbs'] && is_array($data['breadcrumbs'])){
					foreach($data['breadcrumbs'] as $breadcrumb){
				    if(isset($breadcrumb['href'])){
				      $breadcrumb_text = $this->clearText($breadcrumb['text']);
							if(in_array($breadcrumb_text, ['',' ','  ','   '])){
								$breadcrumb_text = $this->mdp->language('text_main_page', $this->session->data['language']);
				  		}

				      $mdp['breadcrumbs'][count($mdp['breadcrumbs'])+1] = [
				        'text' => $breadcrumb_text,
				        'href' => $breadcrumb['href'],
				      ];
				    }
				  }

					$count = count($mdp['breadcrumbs'])-2;
					if($count && isset($mdp['breadcrumbs'][$count]['text'])){
						$mdp['category'] = $this->clearText($mdp['breadcrumbs'][$count]['text']);
					}
				}
				//unset($mdp['breadcrumbs'][1]);
			}
		//getBreadcrumbs

		//getRelated
			private function getRelated($data, &$mdp){
				$mdp['products'] = [];

				if($this->mdp->mdc('product_related') && isset($data['products']) && $data['products'] && is_array($data['products'])){
					foreach($data['products'] as $related){
						$mdp['products'][count($mdp['products'])+1] = [
							'name' => $this->clearText($related['name']),
							'href' => $related['href'],
							'thumb'=> $related['thumb'],
							'price'=> $this->mdp->mdc('hide_price')?'':$this->clearPrice($related),
						];
					}
				}
			}
		//getRelated

		//getAttributes
			private function getAttributes($data, &$mdp){
				$mdp['size'] 		 	 = '';
				$mdp['color'] 	 	 = '';
				$mdp['material']	 = '';
				$mdp['attributes'] = [];
				$mdp['audience'] 	 = $this->mdp->mdc('target_gender');

				if($this->mdp->mdc('product_attribute') && isset($data['attribute_groups']) && is_array($data['attribute_groups']) && $data['attribute_groups']){
					foreach($data['attribute_groups'] as $attribute_group){
						if(isset($attribute_group['attribute'])){
							foreach($attribute_group['attribute'] as $attribute){
								$mdp['attributes'][count($mdp['attributes'])+1] = [
									'text' => $this->clearText($attribute['text']),
									'name' => $this->clearText($attribute['name'])
								];

								foreach(['color','size','material'] as $item){
									if($this->mdp->mdc('attr_' . $item) && $attribute['attribute_id'] == $this->mdp->mdc('attr_' . $item)){
										$mdp[$item] = $this->clearText($attribute['text']);
									}
								}
							}
						}
					}
				}
			}
		//getAttributes

		//getReviews
			private function getReviews($data, &$mdp){
				$mdp['reviews'] = [];

				if($this->mdp->mdc('product_reviews')){
					$reviews_query = $this->db->query("SELECT `author`, `date_added`, `rating`, `text` FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$data['product_id'] . "' AND status = 1 AND rating > 0 ORDER BY date_added DESC");
					if($reviews_query->rows){
						$all_rating = 0;
						foreach($reviews_query->rows as $key => $review){
							$mdp['reviews'][$key+1] = [
								'author'     => $this->clearText($review['author']),
								'date_added' => date("Y-m-dT00:00:00P", strtotime($review['date_added'])),
								'rating'     => $review['rating'],
								'text'       => $this->clearText($review['text'])
							];
							$all_rating += $review['rating'];
						}
						$mdp['reviewCount'] = $reviews_query->num_rows;
						$mdp['rating'] = round($all_rating/$mdp['reviewCount'], 2);
					}
				}
			}
		//getReviews

		//getStock
			private function getStock($data, &$mdp){
				$mdp['stock'] = 'InStock';

				if(!$this->mdp->mdc('product_in_stock')){ //если не всегда в наличии
					$stock_status_id = 0;
					$ss_id_query = $this->db->query("SELECT `stock_status_id` FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$data['product_id'] . "'");
					if($ss_id_query->row['stock_status_id']){
						$stock_status_id = $ss_id_query->row['stock_status_id'];
					}

					$mdp_ss = $this->mdp->mdc('stock_statuses');

					if($data['raw_data']['quantity'] > 0){
						$mdp['stock'] = 'InStock';
					}else{
						if(isset($mdp_ss[$stock_status_id])){
							$mdp['stock'] = $mdp_ss[$stock_status_id];
						}
					}
				}
			}
		//getStock

		//getPrice
			private function getPrice($data, &$mdp){
				$mdp['price'] = '';
				$mdp['price_valid'] = date('Y-m-dT00:00:00P', strtotime('+1 years'));

				if(!$this->mdp->mdc('hide_price')){
					$data['special'] = strip_tags(str_replace(",",".",$data['special']));
					$data['price'] = strip_tags(str_replace(",",".",$data['price']));

					$mdp['price'] = $this->clearPrice($data);

					$mdp['price_old'] = false;
					if($data['special']){
						$old['price'] = $data['price'];
						$mdp['price_old'] = $this->clearPrice($old);
					}

					$option_prices = [];
					if(!$mdp['price'] && $data['options']){
						foreach($data['options'] as $option){
							if(isset($option['product_option_value'])){
								foreach($option['product_option_value'] as $value){
									$option_prices[] = $this->clearPrice($value);
								}
							}
							if(isset($option['option_value'])){
								foreach($option['option_value'] as $value){
									$option_prices[] = $this->clearPrice($value);
								}
							}
						}
					}

					if(isset($data['ro_prices']) && $data['ro_prices']){
						foreach($data['ro_prices'] as $rprice){
							$option_prices[] = $this->clearPrice($rprice);
						}
					}

					if($option_prices){
						$mdp['price'] = min($option_prices);
					}

					$mdp['price'] = str_replace(',', '', $mdp['price']);

					if(!$mdp['price']){
						$mdp['price'] = 0;
					}

				}
			}
		//getPrice

		//getSchemaStoreType
			public function getSchemaStoreType($type){
				$types = ['OnlineStore','AutoPartsStore','BikeStore','BookStore','ClothingStore',
									'ComputerStore','ConvenienceStore','DepartmentStore','ElectronicsStore','Florist',
									'FurnitureStore','GardenStore','GroceryStore','HardwareStore','HobbyShop','HomeGoodsStore',
									'JewelryStore','LiquorStore','MensClothingStore','MobilePhoneStore','MovieRentalStore',
									'MusicStore','OfficeEquipmentStore','OutletStore','PawnShop','PetStore',
									'ShoeStore','SportingGoodsStore','TireShop','ToyStore','WholesaleStore'];

				return $types[$type];
			}
		//getSchemaStoreType

	//getData functions

	//clearData functions

		//clearPrice
			private function clearPrice($data){
				if(!isset($data['special'])){$data['special'] = false;}
				if(!isset($data['price'])){$data['price'] = 0;}
				return $this->clearPriceItem($data['special']?$data['special']:$data['price']);
			}
		//clearPrice

		//clearPriceItem
			private function clearPriceItem($price){
				if($price){
					$price = str_replace(['&#8381;', '&#8372;'], '', $price);
					$price = strip_tags(str_replace($this->language->get('thousand_point'), '', $price));
					$price = str_replace(',', '.', $price);
					$price = preg_replace('/[^\d.]/', '', $price);
					$price = rtrim($price, '.');
				}

				return $price;
			}
		//clearPriceItem

		//clearText
			public function clearText($text = '') {
				if($text){
					if(is_array($text)){foreach ($text as $str_item) {$text = $str_item;break;}}
					$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
					$text = str_replace("><", "> <", $text);
					$text = str_replace(["<br />", "<br>"], " ", $text);
					$text = strip_tags($text);
					$find = [PHP_EOL, "\r\n", "\r", "\n", "\t", '  ', '  ', '    ', '    ', '"', "'", "\\", '&varr;', '&nbsp;', '&pound;', '&euro;', '&para;', '&sect;', '&copy;', '&reg;', '&trade;', '&deg;', '&plusmn;', '&frac14;', '&frac12;', '&frac34;', '&times;', '&divide;', '&fnof;', '&Alpha;', '&Beta;', '&Gamma;', '&Delta;', '&Epsilon;', '&Zeta;', '&Eta;', '&Theta;', '&Iota;', '&Kappa;', '&Lambda;', '&Mu;', '&Nu;', '&Xi;', '&Omicron;', '&Pi;', '&Rho;', '&Sigma;', '&Tau;', '&Upsilon;', '&Phi;', '&Chi;', '&Psi;', '&Omega;', '&alpha;', '&beta;', '&gamma;', '&delta;', '&epsilon;', '&zeta;', '&eta;', '&theta;', '&iota;', '&kappa;', '&lambda;', '&mu;', '&nu;', '&xi;', '&omicron;', '&pi;', '&rho;', '&sigmaf;', '&sigma;', '&tau;', '&upsilon;', '&phi;', '&chi;', '&psi;', '&omega;', '&larr;', '&uarr;', '&rarr;', '&darr;', '&harr;', '&spades;', '&clubs;', '&hearts;', '&diams;', '&quot;', '&amp;', '&lt;', '&gt;', '&hellip;', '&prime;', '&Prime;', '&ndash;', '&mdash;', '&lsquo;', '&rsquo;', '&sbquo;', '&ldquo;', '&rdquo;', '&bdquo;', '&laquo;', '&raquo;'];
					$text = str_replace($find, ' ', $text);
				}

				return $text;
			}
		//clearText

		//clearDiv
			public function clearDiv($text = '') {
				$blocks = ['<div id="ocdw_form_builder','[ldev_question','<div id="ocd','[ocd','{ocd'];

				foreach($blocks as $block){
					$text = explode($block, $text);
					$text = $text[0];
				}

				return $text;
			}
		//clearDiv

		//clearImage
			public function clearImage($image) {
				if($image){
					$image = str_replace(" ", "%20", $image);
					$image = str_replace(['id=','"',"'",'mainimage','selector'], '', $image);
					$image = rtrim($image, '%20');
				}

				return $image;
			}
		//clearImage

		//clearLongText
			public function clearLongText($str, $length, $encoding='UTF-8'){
				if (function_exists('mb_strlen') && (mb_strlen($str, $encoding) <= $length)) {
					return $str;
				}
				if(function_exists('mb_substr')){
					$tmp = mb_substr($str, 0, $length, $encoding);
					return mb_substr($tmp, 0, mb_strripos($tmp, ' ', 0, $encoding), $encoding);
				}else{
					return $str;
				}
			}
		//clearLongText

	//clearData functions

	//Other functions
		public function __construct($registry){
			parent::__construct($registry);
			$this->mdp = new Microdatapro($this->registry);
			$this->mdp->status();
		}

		public function cron(){
			if(isset($this->request->get['key']) && $this->request->get['key'] == $this->mdp->mdc('cron_key')){
				$data = [];

				$categories_query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category WHERE status = 1 ORDER BY category_id");
				foreach($categories_query->rows as $row){
				  $category_id = $row['category_id'];
				  $mm_query = $this->getMinMaxSQL($category_id);

				  if($mm_query->num_rows > 1){
				    $prices = [];
						foreach($mm_query->rows as $row){
				      $prices[] = (isset($row['special']) && $row['special'])?$row['special']:$row['price'];
				    }

				    $data[$category_id]['total'] = $mm_query->num_rows;
				    $data[$category_id]['min'] = (float)rtrim(preg_replace('/[^\d.,]/', '', $this->currency->format($this->tax->calculate(round(min($prices), 2), 0, $this->config->get('config_tax')), $this->session->data['currency'])), ".");
				    $data[$category_id]['max'] = (float)rtrim(preg_replace('/[^\d.,]/', '', $this->currency->format($this->tax->calculate(round(max($prices), 2), 0, $this->config->get('config_tax')), $this->session->data['currency'])), ".");

				  }
				}

				$fp = fopen(DIR_SYSTEM . $this->mdp->datfile, "w");
				fwrite($fp, json_encode($data));
				fclose($fp);

				foreach($data as $category_id => $category_data){
				  echo "id - " . $category_id . ": total: " . $category_data['total'] . ", min: " . $category_data['min'] . ", max: " . $category_data['max'];
				  echo "<br>";
				}
			}else{
				exit();
			}
		}
	//Other functions

	//Off functions

		//checkVariable
		  public function checkVariable(&$microdatapro_data, $key) {
		    $opencart_variables = [
		      'category_manufacturer' => [
		        'breadcrumbs' => false,
		        'description' => '',
		        'results' => [],
		        'microdatapro_data' => [
		          'image' => '',
		        ],
		      ],
		      'product' => [
		        'breadcrumbs' => false,
		        'heading_title' => '',
		        'popup' => '',
		        'thumb' => '',
		        'share' => '',
		        'images' => [],
		        'manufacturer' => '',
		        'model' => '',
		        'description' => '',
		        'special' => 0,
		        'price' => 0,
		        'options' => false,
		        'microdatapro_data' => [
		          'quantity' => 0,
		          'reviews' => 0,
		          'rating' => 0,
		          'sku' => 0,
		          'upc' => 0,
		          'ean' => 0,
		          'isbn' => 0,
		          'date_added' => date('Y.m.d'),
		          'mpn' => 0,
		          'results' => []
		        ],
		        'product_id' => 0,
		        'attribute_groups' => false,
		        'products' => []
		      ],
		      'information' => [
		        'breadcrumbs' => false,
		        'heading_title' => '',
		        'description' => '',
		      ],
		      'tc_og' => [
		        'microdatapro_data' => [
		          'meta_description' => 0,
		          'image' => '',
		        ],
		        'description' => '',
		        'heading_title' => '',
		        'breadcrumbs' => false,
		      ]
		    ];

		    foreach($opencart_variables[$key] as $variable => $replacement){
		      if(is_array($replacement)){
		        foreach($replacement as $var => $rep){
		          if(!isset($microdatapro_data[$variable][$var])){
		            $microdatapro_data[$variable][$var] = $rep;
		          }
		        }
		      }else{
		        if(!isset($microdatapro_data[$variable])){
		          $microdatapro_data[$variable] = $replacement;
		        }
		      }
		    }
		  }
		//checkVariable

	//Off functions

}
