<?php

class ModelMarketingCoupon extends Model
{
    public function addCoupon($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', conditions = '" . (isset($data['conditions']) ? $this->db->escape($data['conditions']) : '') . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

        $coupon_id = $this->db->getLastId();

        if (isset($data['coupon_product'])) {
            foreach ($data['coupon_product'] as $product_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$product_id . "'");
            }
        }

        if (isset($data['coupon_category'])) {
            foreach ($data['coupon_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category SET coupon_id = '" . (int)$coupon_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        if (isset($data['coupon_category_required'])) {
            foreach ($data['coupon_category_required'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category_required SET coupon_id = '" . (int)$coupon_id . "', category_id = '" . (int)$category_id . "'");
            }
        }
        return $coupon_id;
    }

    public function generateCode()
    {

        $string = '';

        $chars = 'abdefhiknrstyz1234567890';

        $numChars = strlen($chars);

        for ($k = 0; $k < 7; $k++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }

        return $string;

    }

}