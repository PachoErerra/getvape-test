<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

/**
 * @category   OpenCart
 * @package    SEO Tags Generator
 * @copyright  © Serge Tkach, 2017-2021, http://sergetkach.com/
 */
class SeoTagsGenerator
{
    public function __construct()
    {
    }
    public function parse($string, $var_values)
    {
        
        $string = html_entity_decode($string, ENT_QUOTES, "UTF-8");
        preg_match_all("|<if>(.*)<\\/endif>|U", $string, $matches, PREG_SET_ORDER);
        $search = [];
        $replace = [];
        foreach ($matches as $item) {
            $search[] = $item[0];
            $if_condition = $this->parseIfCondition($item[0]);
            $replace[] = $this->parseCondition($item[0], $if_condition, $var_values);
        }
        $string = str_replace($search, $replace, $string);
        $search = [];
        $replace = [];
        foreach ($var_values as $key => $value) {
            $search[] = "[" . $key . "]";
            if ("price" == $key) {
                $replace[] = $var_values["price_formatted"];
            } else {
                if ("special" == $key) {
                    $replace[] = $var_values["special_formatted"];
                } else {
                    if ("discount" == $key) {
                        $replace[] = $var_values["discount_formatted"];
                    } else {
                        $replace[] = $value;
                    }
                }
            }
        }
        $string = str_replace($search, $replace, $string);
        $string = $this->parseFunctions($string);
        $string = preg_replace(["/\\s+/", "/\\s\\./"], [" ", "."], $string);
        return $string;
    }
    private function parseCondition($code_initial, $if_condition, $var_values)
    {
        if (!$if_condition) {
            return false;
        }
        if (!$if_condition["var"]) {
            return false;
        }
        $var_value = $this->getVarValue($if_condition["var"], $var_values);
        preg_match("|" . str_replace(["[", "]", "(", ")"], ["\\[", "\\]", "\\(", "\\)"], $if_condition["if_construction"]) . "(.*?)<else>|s", $code_initial, $match_if);
        if (count($match_if) < 1) {
            preg_match("|" . str_replace(["[", "]", "(", ")"], ["\\[", "\\]", "\\(", "\\)"], $if_condition["if_construction"]) . "(.*?)</endif>|s", $code_initial, $match_if);
        }
        if (!isset($match_if[1])) {
            return false;
        }
        if ($if_condition["char"]) {
            if ("==" == $if_condition["char"] && $var_value == $if_condition["value"]) {
                return trim($match_if[1]);
            }
            if ("!=" == $if_condition["char"] && $var_value != $if_condition["value"]) {
                return trim($match_if[1]);
            }
            if (">" == $if_condition["char"] && $if_condition["value"] < $var_value) {
                return trim($match_if[1]);
            }
            if ("<" == $if_condition["char"] && $var_value < $if_condition["value"]) {
                return trim($match_if[1]);
            }
            if (">=" == $if_condition["char"] && $if_condition["value"] <= $var_value) {
                return trim($match_if[1]);
            }
            if ("<=" == $if_condition["char"] && $var_value <= $if_condition["value"]) {
                return trim($match_if[1]);
            }
        } else {
            if ($var_value) {
                return trim($match_if[1]);
            }
        }
        preg_match("|<else>(.*?)</endif>|s", $code_initial, $match_else);
        if (isset($match_else[1])) {
            return trim($match_else[1]);
        }
        return false;
    }
    private function parseFunctions($string)
    {
        preg_match_all("|lower_case\\((.*?)\\)|s", $string, $matches_foo, PREG_SET_ORDER);
        if (0 < count($matches_foo)) {
            foreach ($matches_foo as $item) {
                $val = trim($item[1]);
                $string = str_replace($item[0], mb_strtolower($val), $string);
            }
        }
        preg_match_all("|lower_case_first\\((.*?)\\)|s", $string, $matches_foo, PREG_SET_ORDER);
        if (0 < count($matches_foo)) {
            foreach ($matches_foo as $item) {
                $val = trim($item[1]);
                $string = str_replace($item[0], mb_strtolower(mb_substr($val, 0, 1, "UTF-8")) . mb_substr($val, 1, mb_strlen($val), "UTF-8"), $string);
            }
        }
        preg_match_all("|upper_case\\((.*?)\\)|s", $string, $matches_foo, PREG_SET_ORDER);
        if (0 < count($matches_foo)) {
            foreach ($matches_foo as $item) {
                $val = trim($item[1]);
                $string = str_replace($item[0], mb_strtoupper($val), $string);
            }
        }
        preg_match_all("|upper_case_first\\((.*?)\\)|s", $string, $matches_foo, PREG_SET_ORDER);
        if (0 < count($matches_foo)) {
            foreach ($matches_foo as $item) {
                $val = trim($item[1]);
                $string = str_replace($item[0], mb_strtoupper(mb_substr($val, 0, 1, "UTF-8")) . mb_substr($val, 1, mb_strlen($val), "UTF-8"), $string);
            }
        }
        preg_match_all("|strip_tags\\((.*?)\\)|s", $string, $matches_foo, PREG_SET_ORDER);
        if (0 < count($matches_foo)) {
            foreach ($matches_foo as $item) {
                $val = trim($item[1]);
                $string = str_replace($item[0], strip_tags($val), $string);
            }
        }
        preg_match_all("|cut\\((.*?)\\)|s", $string, $matches_foo, PREG_SET_ORDER);
        if (0 < count($matches_foo)) {
            foreach ($matches_foo as $item) {
                $val = trim($item[1]);
                $pos = mb_strpos($val, "⋂");
                if (false !== $pos) {
                    $str_onstart = trim(mb_substr($val, 0, $pos, "UTF-8"));
                    $search = trim(mb_substr($val, $pos + 1, mb_strlen($val, "UTF-8"), "UTF-8"));
                    if (false !== strpos($search, "|")) {
                        $search = explode("|", $search);
                        foreach ($search as $key => $value) {
                            $search[$key] = trim($value);
                        }
                    }
                    $cut_res = str_replace($search, "", $str_onstart);
                    $string = str_replace($item[0], $cut_res, $string);
                }
            }
        }
        return $string;
    }
    private function parseIfCondition($code_initial)
    {
        $if_condition = [];
        preg_match("|<if>\\s*\\(\\s*(.*?)\\)|s", $code_initial, $matches);
        if (0 < count($matches)) {
            $if_condition["if_construction"] = $matches[0];
            if (!isset($matches[1])) {
                return false;
            }
            if (false !== strpos($matches[1], "==") || false !== strpos($matches[1], "!=") || false !== strpos($matches[1], ">") || false !== strpos($matches[1], "<") || false !== strpos($matches[1], ">=") || false !== strpos($matches[1], "<=")) {
                preg_match("|\\[\\s*(.*?)\\s*\\]|s", $matches[1], $matches_var);
                if (isset($matches_var[0])) {
                    $if_condition["var"] = $matches_var[0];
                    $str = trim(str_replace($matches_var[0], "", $matches[1]));
                    $a_chars = ["==", "!=", ">=", "<=", ">", "<"];
                    $if_condition["value"] = trim(str_replace($a_chars, "", $str));
                    $if_condition["char"] = trim(str_replace($if_condition["value"], "", $str));
                } else {
                    return false;
                }
            } else {
                $if_condition["var"] = $matches[1];
                $if_condition["char"] = false;
                $if_condition["value"] = false;
            }
            return $if_condition;
        }
        return false;
    }
    private function getVarValue($var, $var_values)
    {
        if (!$var) {
            return false;
        }
        $var_key = trim(str_replace(["[", "]"], ["", ""], $var));
        return isset($var_values[$var_key]) ? $var_values[$var_key] : false;
    }
    public function findCategoryNestedIndexes($array)
    {
        $category_keys_exist = [];
        $category_levels = [];
        $string = implode($array);
        preg_match_all("|\\[category_nested sort=\"(.*?)\"\\s*\\]|s", $string, $matches_foo, PREG_SET_ORDER);
        if (0 < count($matches_foo)) {
            foreach ($matches_foo as $key => $item) {
                if (!in_array($item[0], $category_keys_exist)) {
                    $category_keys_exist[] = $item[0];
                    $categories_keys[$key] = str_replace(["[", "]"], ["", ""], $item[0]);
                    $category_levels[$key]["key"] = $categories_keys[$key];
                    $category_levels[$key]["sort"] = $this->getIndexesArray($item[1]);
                }
            }
        }
        return $category_levels;
    }
    private function getIndexesArray($string)
    {
        $a_sort = explode(",", trim($string));
        foreach ($a_sort as $key => $value) {
            $value = trim($value);
            if (!empty($value)) {
                $a_sort[$key] = trim($value);
            } else {
                unset($a_sort[$key]);
            }
        }
        return $a_sort;
    }
    public function getCategoriesLevels($category_indexes)
    {
        $categories_levels = array_column($category_indexes, "sort");
        $array_new = [];
        foreach ($categories_levels as $item) {
            $array_new = array_merge($array_new, $item);
        }
        $categories_levels = array_unique($array_new, SORT_STRING);
        sort($categories_levels);
        return $categories_levels;
    }
    public function getCategoriesKeysForVars($category_indexes)
    {
        return array_column($category_indexes, "key");
    }
    public function getCategoryNestedSortedValue($categories_names, $sort = false)
    {
        $out = "";
        if (false !== $sort) {
            $i = 0;
            foreach ($sort as $item) {
                $out .= $i ? " " : "";
                $out .= isset($categories_names[$item]) ? $categories_names[$item] : "";
                $i++;
            }
        } else {
            $i = 0;
            foreach ($categories_names as $item) {
                $out .= $i ? " " : "";
                $out .= $item;
                $i++;
            }
        }
        return $out;
    }
    public function getCategoriesList($categories, $categories_selected, $categories_inactive, $level = 1, $input_name = "categories")
    {
        
        if (7 < $level) {
            return false;
        }
        $content = "";
        $content .= "<ul class=\"category-list_level-" . $level . "\">";
        foreach ($categories as $category_item) {
            if (!(isset($this->request->get["category_id"]) && $this->request->get["category_id"] == $category_item["category_id"])) {
                $checked = "";
                $data_checked = "notchecked";
                if ($categories_selected && in_array($category_item["category_id"], $categories_selected)) {
                    $checked = " checked";
                    $data_checked = "checked";
                }
                if ($category_item["has_children"]) {
                    $add_class_li = " has-children";
                    $add_class_i = " toggle-item closed";
                    $add_char_i = "+";
                    $add_class_all_selector = "<i class=\"fa fa-link fa-rotate-90 all-subcategories-selector\" data-status=\"" . $data_checked . "\"></i> ";
                } else {
                    $add_class_li = "";
                    $add_class_i = "";
                    $add_char_i = " ";
                    $add_class_all_selector = "";
                }
                $add_class_inactive = "";
                $add_disabled = "";
                if ($categories_inactive && in_array($category_item["category_id"], $categories_inactive)) {
                    $add_class_inactive = " _inactive";
                    $add_disabled = " disabled";
                }
                $content .= "<li class=\"category-item_" . $level . $add_class_li . "\">" . "<i class=\"li-space " . $add_class_i . "\">" . $add_char_i . "</i> " . "<label class=\"" . $add_class_inactive . "\"><input type=\"checkbox\" name=\"" . $input_name . "[]\" value=\"" . $category_item["category_id"] . "\"" . $checked . $add_disabled . " class=\"" . $add_class_inactive . "\" /> " . $category_item["category_name"] . "</label>" . $add_class_all_selector;
                if ($category_item["has_children"]) {
                    $content .= $this->getCategoriesList($category_item["children"], $categories_selected, $categories_inactive, $level + 1, $input_name);
                }
                $content .= "</li>";
            }
        }
        $content .= "</ul>";
        return $content;
    }
    
    
    
    private function get_sitename($site)
    {
        $site = $this->get_site_domain_name($site);
        if (!preg_match("/[a-z.-]+\$/", $site)) {
            require_once DIR_SYSTEM . "library/seo_tags_generator/idna_convert.class.php";
            $IDN = new idna_convert();
            $input = mb_convert_encoding($site, "utf-8", mb_detect_encoding($site));
            $site = $IDN->encode($input);
        }
        return $site;
    }
    private function get_site_domain_name($site)
    {
        $site = str_replace(["http:", "https:", "//", "www."], ["", "", "", ""], $site);
        $site = $this->get_sitename_without_dirs($site);
        return $site;
    }
    private function get_sitename_without_dirs($site)
    {
        $n = mb_strlen($site);
        $last_char = mb_substr($site, $n - 1, $n, "UTF-8");
        if ("/" == $last_char) {
            $site = mb_substr($site, 0, $n - 1, "UTF-8");
        }
        if (stristr($site, "/")) {
            $array = explode("/", $site);
            $site = $array[0];
        }
        return $site;
    }
    private function get_site_without_subdomain($site)
    {
        $a_site = explode(".", $site, 2);
        return $a_site[1];
    }
}

?>