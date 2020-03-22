<?php

use hydra\compiler\SimpleCompiler;

$comp = new SimpleCompiler();
$opts = getopt("", array (
    "js_in::", 
    "js_out::", 
    "js_sep::",
    "js_lvl::",
    "js_ext::",

    "css_in::", 
    "css_out::",
    "css_ext::",
));

if (!empty($opts["js_in"])) {
    // compile javascript code
    $js_opts = array ("root" => $opts["js_in"]);

    $code = $comp->compileJS($js_opts);
}


if (!empty($opts["incss"])) {

}