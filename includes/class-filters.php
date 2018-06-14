<?php

class Filters extends Lp_Og {

  public function __construct() {
    add_filter(self::$options_prefix . '_twitter:description', array($this, 'append_space_after_period'), 10, 2);
    add_filter(self::$options_prefix . '_og:description', array($this, 'append_space_after_period'), 10, 2);
    add_filter(self::$options_prefix . '_twitter:site', array($this, 'append_at_symbol'), 10, 2);
    add_filter(self::$options_prefix . '_twitter:creator', array($this, 'append_at_symbol'), 10, 2);
    add_filter(self::$options_prefix . '_og:image', array($this, 'process_image'), 10, 2);
    add_filter(self::$options_prefix . '_twitter:image', array($this, 'process_image'), 10, 2);
  }


  public function process_image($value, $field_name) {

    $width = '';
    $height = '';

    //-- Get image data, including dimensions.
    if(is_numeric($value)) {

      //-- If this attachment doesn't actually exist or isn't an image, just get out of here.
      if(!wp_attachment_is_image($value)) return '';

      $meta = array_key_exists('lateralpixel_open_graph', wp_get_attachment_metadata($value)['sizes']) ?
              wp_get_attachment_image_src($value, 'lateralpixel_open_graph') :
              wp_get_attachment_image_src($value, 'large');

      //-- If, for some reason, no image is returned, just get out of here.
      if(is_null($meta)) return '';

      $value = $meta[0];
      $width = $meta[1];
      $height = $meta[2];
    } elseif ($imageData = @getimagesize($value)) {
      $width = $imageData[0];
      $height = $imageData[1];
    }

    //-- Set image sizes.
    add_filter(self::$options_prefix . '_og:image:width', function ($value, $key) use ($width) {
      return $width;
    }, 10, 2);

    add_filter(self::$options_prefix . '_og:image:height', function ($value, $key) use ($height) {
      return $height;
    }, 10, 2);

    return $value;
  }


  public function append_at_symbol($value, $key) {
    if(!$value) return $value;

    return '@' . str_replace('@', '', $value);
  }


  public static function append_space_after_period($value) {
    if(!$value) return $value;

    $value = preg_replace( '/\.([^, ])/', '. $1', $value);
    $value = preg_replace( '/\?([^, ])/', '? $1', $value);
    return preg_replace( '/\!([^, ])/', '! $1', $value);
  }

}
