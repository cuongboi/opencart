<?php
class ControllerExtensionModuleFeaturedCategory extends Controller {
	public function index($setting) {
		static $module = 0;
		$this->load->model('extension/module/featured_category');
		$this->load->model('tool/image');

		$this->document->addScript('catalog/view/javascript/jquery/mixitup/jquery.mixitup.min.js');

		$data['products'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		$data['heading_title'] = html_entity_decode($setting['display_name'], ENT_QUOTES, 'UTF-8');

		if (!empty($setting['category'])) {

			$data['categories'] = $this->model_extension_module_featured_category->getCategories($setting['category']);	

			$filter = array(
				'filter_category_id' 	=> $setting['category'], 
				'filter_sub_category' 	=> isset($setting['sub_category']) ? $setting['sub_category'] : 0,
				'limit'					=> $setting['limit']
			);

			$products = $this->model_extension_module_featured_category->getProducts($filter);

			foreach ($products as $product) {
				if ($product['image']) {
					$image = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product['special']) {
					$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $product['rating'];
				} else {
					$rating = false;
				}

				$category = array();

				if($product['categories']) {
					$category = array_map(function($category_id) {
						return "cat-" . $category_id;
					}, explode(',' ,$product['categories']));
				}

				$data['products'][] = array(
					'product_id'  => $product['product_id'],
					'thumb'       => $image,
					'name'        => $product['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'categories'  => implode(" ", $category),
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id'])
				);
			}
		}
		$data['module'] = $module++;
		if ($data['products']) {
			return $this->load->view('extension/module/featured_category', $data);
		}
	}
}