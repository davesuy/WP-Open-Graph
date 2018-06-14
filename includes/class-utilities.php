<?php


require_once 'class-postdecorator.php';

class Utilities extends Lp_Og {

  public static function get_current_post_type() {
    global $post, $typenow, $current_screen;

    if ( $post && $post->post_type ) {
      return $post->post_type;
    }

    elseif ( $typenow ) {
      return $typenow;
    }

    elseif ( $current_screen && $current_screen->post_type ) {
      return $current_screen->post_type;
    }

    elseif ( isset( $_REQUEST['post_type'] ) ) {
      return sanitize_key( $_REQUEST['post_type'] );
    }

    elseif ( isset( $_REQUEST['post'] ) ) {
      return get_post_type( $_REQUEST['post'] );
    }

    return null;
  }


  public static function get_options() {
    if(is_null(self::$options)) {
      self::$options = get_option(self::$options_prefix);
    }

    return self::$options;
  }


  public static function get_option($key) {
    if(isset(self::get_options()[$key])) {
      return self::get_options()[$key];
    }

    return false;
  }

  public static function get_post_decorator() {
    global $post;

    if(is_null(self::$post_decorator)) {
      self::$post_decorator = new PostDecorator($post);
    }

    return self::$post_decorator;
  }


  public static function get_post_options() {
    if(is_null(self::$post_options)) {
      self::$post_options = get_post_meta(self::get_post_decorator()->ID, self::$options_prefix);
    }

    if(empty(self::$post_options)) {
      return array();
    }

    return self::$post_options[0];
  }


  public static function get_post_option($key) {
    $post_options = self::get_post_options();
    return !empty($post_options[$key]) ? $post_options[$key] : false;
  }


  public static function get_field_name($name) {
    return self::$options_prefix . '[' . $name . ']';
  }


  public static function get_first_image() {
    $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', self::get_post_decorator()->post_content, $matches);
    return !empty($matches[1][0]) ? $matches[1][0] : false;
  }


  public static function strip_all_tags($text) {
    if(!$text) return $text;

    $text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $text);
    $text = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $text);

    return strip_tags($text);
  }

  public static function process_content($content) {
    $value = strip_shortcodes($content);
    $value = self::strip_all_tags($value);
    $value = trim($value);
    $value = substr($value, 0, 300);
    return $value;
  }

}
