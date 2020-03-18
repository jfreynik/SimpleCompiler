<?php

if (!http_response_code()) {
    new SimpleCompiler(array (
        "js_options" => array (
            "root" => dirname(dirname(__FILE__))."/tests/js",
            // "WHITESPACE_ONLY", "SIMPLE_OPTIMIZATIONS", "ADVANCED_OPTIMIZATIONS",
            "compilation_level" => "SIMPLE_OPTIMIZATIONS", 
            "output_format" => "text",
            "output_info" => "compiled_code",
            "ext" => "js",
            "out" => dirname(dirname(__FILE__))."/bin/output.js",
        ),

        "css_options" => array (
            "root" => dirname(dirname(__FILE__))."/tests/css",
            "ext" => "css",
            "out" => dirname(dirname(__FILE__))."/bin/output.css",
        ),

    ));
}

class SimpleCompiler {

    protected $config;

    public function __construct ($config = array ()) {
        $this->config = $config;

        if (isset($this->config["js_options"])) {
            $this->compileJS();
        }

        if (isset($this->config["css_options"])) {
            $this->compileCSS();
        }
    }

    public function compileJS () {
        $cfg = $this->config;
        $opt = $cfg["js_options"];

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

        file_put_contents($opt["out"], $code);
        return true;
    }

    public function compileCSS () {
        $cfg = $this->config;
        $opt = $cfg["css_options"];

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

        $code = $this->httpPost("https://cssminifier.com/raw", array (
            "input" => implode(";", $code),
        ));

        file_put_contents($opt["out"], $code);
        return true;

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