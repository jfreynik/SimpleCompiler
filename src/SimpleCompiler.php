<?php

namespace hydra\compiler;

/**
 * 
 * @author jfreynik
 * @version 0.0.1
 */

class AssetCompiler {

    protected $jsOptions;

    protected $cssOptions;

    public function __construct () {

        // Closure Compiler Options
        $this->jsOptions = array (
            // "root" => dirname(dirname(__FILE__))."/tests/js",
            // "WHITESPACE_ONLY", "SIMPLE_OPTIMIZATIONS", "ADVANCED_OPTIMIZATIONS",
            "compilation_level" => "SIMPLE_OPTIMIZATIONS", 
            "output_format" => "text",
            "output_info" => "compiled_code",
            "separator" => ";",
            "ext" => "js",
            // "out" => dirname(dirname(__FILE__))."/tests/bin/output.js",
        );

        // CSS Minifier Options
        $this->cssOptions = array (
            // "root" => dirname(dirname(__FILE__))."/tests/css",
            "ext" => "css",
            // "out" => dirname(dirname(__FILE__))."/tests/bin/output.css",
        );
    }

    public function compileJS ($jsOptions = array ()) {
        $opt = array_merge($this->jsOptions, $jsOptions);

        if (!isset($opt["out"])) {
            return false;
        }

        $dir = $this->scan($opt["root"]);
        $code = array();
        while (count($dir)) {
            $file = array_pop($dir);
            if (is_dir($file)) {
                $temp = $this->scan($file);
                foreach ($temp as $tmp) {
                    $dir[] = $tmp;
                }
                continue;
            }

            $ext = explode(".", $file);
            $ext = array_pop($ext);
            if ($ext == $opt["ext"]) {
                $code[] = file_get_contents($file);
            }
        }

        $code = $this->httpPost("https://closure-compiler.appspot.com/compile", array (
            "js_code" => implode(";", $code),
            "compilation_level" => $opt["compilation_level"],
            "output_format" => $opt["output_format"],
            "output_info" => $opt["output_info"],
        ));

        // file_put_contents($opt["out"], $code);
        return $code;
    }

    public function compileCSS ($cssOptions = array ()) {
        $opt = array_merge($this->cssOptions, $cssOptions);

        $dir = $this->scan($opt["root"]);
        $code = array();
        while (count($dir)) {
            $file = array_pop($dir);
            if (is_dir($file)) {
                $temp = $this->scan($file);
                foreach ($temp as $tmp) {
                    $dir[] = $tmp;
                }
                continue;
            }

            $ext = explode(".", $file);
            $ext = array_pop($ext);
            if ($ext == $opt["ext"]) {
                $code[] = file_get_contents($file);
            }
        }

        $code = $this->httpPost("https://cssminifier.com/raw", array (
            "input" => implode(";", $code),
        ));

        // file_put_contents($opt["out"], $code);
        return $code;

    }

    public function httpGet ($url = "", $params = array ()) {
        $ch = curl_init();
        if (!empty($params)) {
            $params = http_build_query($params);
            if (strpos($url, "?") === false) {
                $url = "{$url}?{$params}";
            } else {
                $url = "{$url}&{$params}";
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $rsp = curl_exec($ch);
        curl_close ($ch);
        return $rsp; 
    }

    public function httpPost ($url = "", $data = array ()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $rsp = curl_exec($ch);
        curl_close ($ch);
        return $rsp; 
    }

    public function scan ($dir = "") {
        $files = scandir($dir);
        $ret = array();
        foreach ($files as $i => $file) {
            if ($file == "." || $file == "..") {
                continue;
            }
            $ret[] = "{$dir}/{$file}";
        }
        return $ret;
    }
}