<?php

function lp_wishpond_func($attrs) {
  extract(shortcode_atts(array(
    'id' => ''
  ), $attrs));

  // Default to 100% width for Wordpress.
  if (!isset($attrs["width"])) {
    $attrs["width"] = "100%";
  }

  $options_name = 'wpsc_landing_page'.$attrs["id"];

  // Backwards compatibility.
  if (isset($attrs["mid"])) {
    $attrs["merchant_id"] = $attrs["mid"];
  }

  // Check to see if we have a campaign or merchant
  if ($attrs["id"] == "" && $attrs["merchant_id"] == "") {
    return "<h1>WPSC Widget needs either id='' or merchant_id='' to function.</h1>";
  }

  // Build the config options.
  $js = "var ".$options_name." = ".$options_name." || {};\n";

  if (isset($attrs["dom_id"])) {
    $js = $js."$options_name.id='".$attrs["dom_id"]."';\n";
  }

  if (isset($attrs["width"])) {
    $js = $js."$options_name.w='".$attrs["width"]."';\n";
  }

  if (isset($attrs["height"])) {
    $js = $js."$options_name.h='".$attrs["height"]."';\n";
  }

  if (isset($attrs["container"])) {
    $js = $js."$options_name.c='".$attrs["container"]."';\n";
  }

  if (isset($attrs["frameborder"])) {
    $js = $js."$options_name.fb='".$attrs["frameborder"]."';\n";
  }

  if (isset($attrs["class"])) {
    $js = $js."$options_name.cl='".$attrs["class"]."';\n";
  }

  if (isset($attrs["style"])) {
    $js = $js."$options_name.s='".$attrs["style"]."';\n";
  }

  $js = $js."// hey hey";

  // Where do we go to render this campaign?
  $host = WISHPOND_SITE_URL;

  if ($attrs["id"] != "") {
    $src = "$host/sc/".$attrs["id"].".js";
  }
  else {
    $src = "$host/sc/m/".$attrs["merchant_id"].".js";
  }

  // We've got our completed JS. Let's compile everything and call it a day.
  $html = "<script type=\"text/javascript\">\n$js</script>\n";
  $html = $html."<script type=\"text/javascript\" src=\"$src\"></script>";

  return $html;
}

add_shortcode('wpsc_landing_page', 'lp_wishpond_func');

#used for backwards compatibility mostly; might conflict with shortcode from wishpond-social-contests plugin
add_shortcode('wpsc', 'lp_wishpond_func');

?>