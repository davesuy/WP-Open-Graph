<?php
/**
* Plugin Name: Lateral Pixel Open Graph
* Description: Open Graph for Facebook, Google Plus, Pinterest, Twitter etc.
* Version: 1.0.0
* Author: Dave Ramirez
*/


if ( !defined( 'WPINC' ) ) {
  die;
}

require_once 'includes/class-filters.php';
require_once 'includes/class-settings.php';
require_once 'includes/class-metabox.php';
require_once 'includes/class-opengraph.php';

class Lp_Og {

  private static $instance;
  public $version = '1.0.0';
	public $controllers = array();
  protected static $options_prefix = 'lateralpixel_open_graph';
  protected static $admin_settings_page_slug = 'lateralpixel_open_graph';
  protected static $options_short_prefix = 'lpog';
  protected static $options = null;
  protected static $post_options = null;
	protected static $post_decorator = null;

  public static function instance() {

		if (!isset($GLOBALS[static::class]) || is_null($GLOBALS[static::class])) {
			$GLOBALS[static::class] = new static();
		}

  }


  public function __construct() {
    $this->controllers['Settings'] = new Settings;
    $this->controllers['Metabox'] = new Metabox;
    $this->controllers['OpenGraph'] = new OpenGraph;
    $this->controllers['Filters'] = new Filters;

    add_action( 'admin_enqueue_scripts', array($this, 'enqueue_styles_and_scripts' ));
  }


  public static function delete_options_and_meta() {
    global $wpdb;
    delete_option(self::$options_prefix);
    $wpdb->delete( $wpdb->prefix . 'postmeta', array( 'meta_key' => self::$options_prefix) );
  }


  public function enqueue_styles_and_scripts() {
    wp_enqueue_style( 'lateralpixel-open-graph', plugin_dir_url( __FILE__ ) . 'includes/assets/css/style.css', array(), null);
    wp_enqueue_script( 'lateralpixel-open-graph', plugin_dir_url( __FILE__ ) . 'includes/assets/js/scripts.js', array('jquery'), null, true );
  }
}

Lp_Og::instance();


register_uninstall_hook( __FILE__, array('\LateralPixel\Lp_Og', 'delete_options_and_meta') );
