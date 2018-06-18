<?php


class OpenGraph extends Lp_Og {


	protected $forceAll = false;


	protected $isPostListingPage = false;


  public function __construct() {
		add_action('wp', array($this, 'set_useful_variables'));
    add_action('plugins_loaded', array($this, 'add_og_image_size'));
    add_action('wp_head', array($this, 'open_graph_tag_generation'), 40);
    add_filter('image_size_names_choose', array($this, 'add_og_image_size_to_uploader'));
		add_filter('language_attributes', array($this, 'add_open_graph_prefix'), 10, 2 );
	}

	public function set_useful_variables() {
		$this->forceAll = Utilities::get_option('force_all');
		$this->isPostListingPage = is_home() || is_archive();
	}


  public function add_open_graph_prefix( $output, $doctype ) {
    return $output . ' prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# website: http://ogp.me/ns/website#"';
  }


  public function add_og_image_size() {
    add_theme_support('post-thumbnails');
    add_image_size('lateralpixel_open_graph', 1200, 1200, false);
  }


  public function add_og_image_size_to_uploader($sizes) {
    $sizes['lateralpixel_open_graph'] = __( 'Open Graph');
    return $sizes;
  }


  public function open_graph_tag_generation() {


  
		$startTime = microtime(true);

    foreach($this->get_open_graph_values() as $key => $data) {

			if(empty($data['value'])) continue;

      $content = preg_replace( "/\r|\n/", "", $data['value']);
			$content = htmlentities($content, ENT_QUOTES, 'UTF-8', false);

			if($data['attribute'] === 'property') {
				?><meta property="<?php echo $key; ?>" content="<?php echo $content; ?>" /><?php
				echo "\n";
				continue;
			}

			if($data['attribute'] === 'name') {
				?><meta name="<?php echo $key; ?>" content="<?php echo $content; ?>" /><?php
				echo "\n";
				continue;
			}

      
     
    }

 

  }


  public function get_open_graph_processed_value($field_name, $progression = array()) {

		$option = Utilities::get_option( $field_name );

		//-- Check for explicit option to use global options, or if it's an archive page.
		$useGlobal = $this->forceAll === 'on'
			|| $this->isPostListingPage
			|| Utilities::get_option( $field_name . '_force' ) === 'on';

    if( $useGlobal ) {
			$value = Utilities::process_content(Utilities::get_option($field_name));
      return apply_filters(self::$options_prefix . '_processed_value', $value, $field_name);
    }

    if(!empty($progression)) {
      foreach ($progression as $progressionValue) {
        if(!empty($progressionValue)) {
          $value = Utilities::process_content($progressionValue);
          return apply_filters(self::$options_prefix . '_processed_value', $value, $field_name);
        }
      }
    }

    return '';
  }


  public function get_open_graph_values() {

    $frontPageID = (int) get_option('page_on_front');

    $data = array(
      'og:site_name' => array(
        'attribute' => 'property',
        'value' => $site_name = get_bloginfo('name')
      ),

      'og:url' => array(
        'attribute' => 'property',
        'value' => $url = get_permalink(Utilities::get_post_decorator()->ID)
      ),

      'og:locale' => array(
        'attribute' => 'property',
        'value' => get_locale()
      ),

      'og:description' => array(
        'attribute' => 'property',
        'value' => $this->get_open_graph_processed_value( 'og:description',
          array(
            Utilities::get_post_option('og:description'),
            Utilities::get_post_decorator()->post_excerpt,
            Utilities::get_post_decorator()->post_content,
            Utilities::get_option('og:description'),
            get_bloginfo('og:description')
          )
        )
      ),

      'og:title' => array(
        'attribute' => 'property',
        'value' => $theTitle = $this->get_open_graph_processed_value( 'og:title',
          array(
            Utilities::get_post_option('og:title'),
            get_the_title(),
            Utilities::get_option('og:title'),
            $site_name
          )
        )
      ),

      'og:type' => array(
        'attribute' => 'property',
        'value' => $this->get_open_graph_processed_value( 'og:type',
          array(
            Utilities::get_post_option('og:type'),
            is_single() ? 'article' : 'website',
            Utilities::get_option('og:type')
          )
        )
      ),

      //-- Might be a string, might be an ID. Will be filtered to account for both.
      'og:image' => array(
        'attribute' => 'property',
        'value' => $image = $this->get_open_graph_processed_value( 'og:image',
          array(
            Utilities::get_post_option('og:image'),
            get_post_thumbnail_id(Utilities::get_post_decorator()->ID),
            Utilities::get_first_image(),
            Utilities::get_option('og:image'),
            !empty($frontPageID) && has_post_thumbnail($frontPageID) ?
            get_post_thumbnail_id( $frontPageID ) :
            false
          )
        )
      ),

      'og:image:width' => array(
        'attribute' => 'property',
        'value' => ''
      ),

      'og:image:height' => array(
        'attribute' => 'property',
        'value' => ''
      ),

      'fb:admins' => array(
        'attribute' => 'property',
        'value' => Utilities::get_option('fb:admins')
      ),

      'fb:app_id' => array(
        'attribute' => 'property',
        'value' => Utilities::get_option('fb:app_id')
      ),

      'twitter:card' => array(
        'attribute' => 'name',
        'value' => $this->get_open_graph_processed_value('twitter:card',
          array(
            Utilities::get_post_option('twitter:card'),
            Utilities::get_option('twitter:card')
          )
        )
      ),

      'twitter:creator' => array(
        'attribute' => 'name',
        'value' => $this->get_open_graph_processed_value('twitter:creator',
          array(
            Utilities::get_post_option('twitter:creator'),
            Utilities::get_option('twitter:creator')
          )
        )
      ),

      'twitter:site' => array(
        'attribute' => 'name',
        'value' => Utilities::get_option('twitter:site')
      ),

      'twitter:title' => array(
        'attribute' => 'name',
        'value' => $theTitle
      ),

      'twitter:image' => array(
        'attribute' => 'name',
        'value' => $image
      ),

      'twitter:description' => array(
        'attribute' => 'name',
        'value' => $this->get_open_graph_processed_value( 'twitter:description',
          array(
            Utilities::get_post_option('twitter:description'),
            Utilities::get_post_decorator()->post_excerpt,
            Utilities::get_post_decorator()->post_content,
            Utilities::get_option('twitter:description'),
            $this->get_open_graph_processed_value( 'og:description',
              array(
                Utilities::get_post_decorator()->post_content,
                get_bloginfo('og:description')
              )
            )
          )
        )
      ),

      'twitter:player' => array(
        'attribute' => 'name',
        'value' =>  $this->get_open_graph_processed_value('twitter:playerurl',
          array(
            Utilities::get_post_option('twitter:playerurl'),
            Utilities::get_option('twitter:playerurl')
          )
        )


      ),

         'twitter:player:width' => array(
        'attribute' => 'name',
        'value' =>  $this->get_open_graph_processed_value('twitter:videowidth',
          array(
            Utilities::get_post_option('twitter:videowidth'),
            Utilities::get_option('twitter:videowidth')
          )
        )
         //Utilities::get_option('twitter:videowidth')
      ),
       'twitter:player:height' => array(
        'attribute' => 'name',
        'value' =>  $this->get_open_graph_processed_value('twitter:videoheight',
          array(
            Utilities::get_post_option('twitter:videoheight'),
            Utilities::get_option('twitter:videoheight')
          )
        )
      )
     
    );


    foreach($data as $key=>$item) {
      $data[$key]['value'] = apply_filters(self::$options_prefix . '_' . $key, $data[$key]['value'], $key);
    }

    return apply_filters(self::$options_prefix . '_all_data', $data);
  }
}
