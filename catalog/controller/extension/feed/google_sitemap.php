<?php
// Sitemap perf optimization 2026-07-15: lightweight product/category queries
class ControllerExtensionFeedGoogleSitemap extends Controller {
	public function index() {
		ob_end_clean();
		if ($this->config->get('feed_google_sitemap_status')) {
			$output  = '<?xml version="1.0" encoding="UTF-8"?>';
			$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

			$this->load->model('catalog/product');
			$this->load->model('tool/image');

			$products = $this->model_catalog_product->getProductsForSitemap();

			foreach ($products as $product) {
				if ($product['image']) {
					$output .= '<url>';
					$output .= '  <loc>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</loc>';
					$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])) . '</lastmod>';
				//	$output .= '  <image:image>';
				//	$output .= '  <image:loc>' . $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')) . '</image:loc>';
				//	$output .= '  <image:caption>' . $product['name'] . '</image:caption>';
				//	$output .= '  <image:title>' . $product['name'] . '</image:title>';
				//	$output .= '  </image:image>';
					$output .= '</url>';
				}
			}

			$this->load->model('catalog/category');

			$output .= $this->getCategories(0);

			$this->load->model('catalog/manufacturer');

			$manufacturers = $this->model_catalog_manufacturer->getManufacturers();

			foreach ($manufacturers as $manufacturer) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']) . '</loc>';
				$output .= '</url>';

			}

			$this->load->model('catalog/information');

			$informations = $this->model_catalog_information->getInformations();

			foreach ($informations as $information) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->url->link('information/information', 'information_id=' . $information['information_id']) . '</loc>';
				$output .= '</url>';
			}
			
			$this->load->model('blog/category');
			$this->load->model('blog/article');
			$article_data = array();
			$articles = $this->model_blog_article->getArticles($article_data);
			foreach ($articles as $article) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->url->link('blog/article', '&article_id=' . $article['article_id']) . '</loc>';
				$output .= '</url>';
			}


			$categorys = $this->model_blog_category->getCategories(0);
			foreach ($categorys as $category) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->url->link('blog/category', '&blog_category_id=' . $category['blog_category_id']) . '</loc>';
				$output .= '</url>';
			}

				$output .= '<url>';
				$output .= '  <loc>https://getvape.com.ua/</loc>';
				$output .= '</url>';
				/*
				$output .= '<url>';
				$output .= '  <loc>https://getvape.com.ua/blog/</loc>';
				$output .= '</url>';
				*/
				$output .= '<url>';
				$output .= '  <loc>https://getvape.com.ua/contact-us/</loc>';
				$output .= '</url>';
			//	$output .= '<url>';
			//	$output .= '  <loc>https://getvape.com.ua/compare-products/</loc>';
			//	$output .= '  <changefreq>weekly</changefreq>';
			//	$output .= '  <priority>0.5</priority>';
			//	$output .= '</url>';
				$output .= '<url>';
				$output .= '  <loc>https://getvape.com.ua/specials/</loc>';
				$output .= '</url>';				
				$output .= '<url>';
				$output .= '  <loc>https://getvape.com.ua/latest/</loc>';
				$output .= '</url>';	
			$output .= '</urlset>';

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);
		}
	}

	protected function getCategories($parent_id, $current_path = '') {
		$output = '';

		$results = $this->model_catalog_category->getCategories($parent_id);

		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}

			if ($this->model_catalog_product->categoryHasProducts($result['category_id'])) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->url->link('product/category', 'path=' . $new_path) . '</loc>';
				$output .= '</url>';
			}
			
			$output .= $this->getCategories($result['category_id'], $new_path);
		}

		return $output;
	}
}
