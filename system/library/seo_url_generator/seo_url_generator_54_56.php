<?php
/**
 * @category   OpenCart
 * @package    SEO URL Generator PRO
 * @copyright  © Serge Tkach, 2018-2021, http://sergetkach.com/
 */
class SeoUrlGenerator
{
    public function translitDo($path, $string, $translit_function, $licence_code)
    {
        if (!$this->isValidLicence($licence_code)) {
            $this->log->write("ERROR -- SEO URL Generator: translit function() :: licence code is not valid. Skip operation!");
            return false;
        }
        $inc_file = $path . str_replace("sug_translit_", "", $translit_function) . ".php";
        if (is_file($inc_file)) {
            require_once $inc_file;
            if (is_callable($translit_function)) {
                return $translit_function($string);
            }
            $this->log->write("ERROR -- SEO URL Generator: function  " . $translit_function . "() is not calable" . " in " . basename(__FILE__) . " on line " . (27 - 4) . ". Code: if (is_callable(\$translit_function)) {");
        } else {
            $this->log->write("ERROR -- SEO URL Generator: No file  " . $inc_file . " required" . " in " . basename(__FILE__) . " on line " . (31 - 11) . ". Code: require_once \$inc_file;");
        }
        return "error_undefined";
    }
    public function clearString($string, $setting, $licence_code)
    {
        if (!$this->isValidLicence($licence_code)) {
            $this->log->write("ERROR -- SEO URL Generator: clearString() :: licence code is not valid. Skip operation!");
            return $string;
        }
        return str_replace(array("ʼ", "`", "~", "@", "#", "№", "\$", "%", "^", "&", "*", "(", ")", "+", "-", "=", ".", ":", ",", ";", "!", "?", "—", "'", "\"", "\\", "/", "{", "}", "[", "]", "<", ">", "°", "•", "″", "×", "÷", "ø", "²"), " ", $string);
    }
    public function isValidLicence($licence)
    {
        $a_licence = $this->decodeLicence($licence);
        $licenced_site = str_replace(array("http:", "https:", "//", "www."), array("", "", "", ""), mb_strtolower($a_licence["site"], "UTF-8"));
        $this_site = $this->get_sitename(HTTPS_SERVER);
        // if ($this_site != $licenced_site) {
        //     return false;
        // }
        return true;
    }
    private function decodeLicence($licence)
    {
        $res = array();
        $real_licence_code = base64_decode($licence);
        preg_match("/2MzJNQwNmezM50(.*)MzcyYmFlGY2YmIwM/siU", $real_licence_code, $matches);
        if ($matches) {
            $res["site"] = base64_decode(base64_decode($matches[1]));
            unset($matches);
            preg_match("/dmMQxYj1GEx9x3Yz(.*)2UzJlwYzEwY/siU", $real_licence_code, $matches);
            if ($matches) {
                $res["time"] = base64_decode($matches[1]);
                unset($matches);
                if (count($res) < 2) {
                    return false;
                }
                return $res;
            }
            return false;
        }
        return false;
    }
    private function get_sitename($site)
    {
        $site = $this->get_site_domain_name($site);
        if (!preg_match("/[a-z.-]+\$/", $site)) {
            require_once DIR_SYSTEM . "library/seo_url_generator/idna_convert.class.php";
            $IDN = new idna_convert();
            $input = mb_convert_encoding($site, "utf-8", mb_detect_encoding($site));
            $site = $IDN->encode($input);
        }
        return $site;
    }
    private function get_site_domain_name($site)
    {
        $site = str_replace(array("http:", "https:", "//", "www."), array("", "", "", ""), $site);
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
    private function get_site_subdomain($site)
    {
        $a_site = explode(".", $site);
        return $a_site[0];
    }
}

?>