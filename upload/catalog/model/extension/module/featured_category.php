<?php
class ModelExtensionModuleFeaturedCategory extends Model {

    public function getCategories($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "category_description WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'";
        if(!empty($data)) {
            if(is_array($data)) {
                $data = array_map(function($category_id){
                    return (int)$category_id;
                }, $data);
                $sql .= " AND category_id IN (". implode(',', $data) .")";
            } else {
                $sql .= " AND category_id = '". (int)$data ."'";
            }
        }
        $query = $this->db->query($sql);

        return $query->rows;
    }
	public function getProducts($data = array()) {
		$sql = "SELECT p.product_id, pd.name, pd.description, p.image, GROUP_CONCAT(p2c.category_id) AS categories, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, p.price, p.tax_class_id, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
            }
            $sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id) ";
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
            if(is_array($data['filter_category_id'])) {
                if (!empty($data['filter_sub_category'])) {
                    $sql .= " AND cp.path_id IN (" . implode(',', $data['filter_category_id']) . ")";
                } else {
                    $sql .= " AND p2c.category_id IN (" . implode(',', $data['filter_category_id']) . ")";
                }
            } else {
                if (!empty($data['filter_sub_category'])) {
                    $sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
                } else {
                    $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
                }
            }
        }

        if(!isset($data['limit'])) {
            $data['limit'] = 4;
        }

        $sql .= " GROUP BY p.product_id LIMIT 0," . (int)$data['limit'];

		$query = $this->db->query($sql);

		return $query->rows;
	}
}