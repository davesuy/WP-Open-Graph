<?php


require_once 'class-utilities.php';

class Settings extends Lp_Og {


	public function __construct() {
		add_action( 'admin_init', array($this, 'ensure_attachment_exists'), 1);
		add_action( 'admin_init', array($this, 'register_main_setting'));
		add_action( 'admin_init', array($this, 'register_settings'));
		add_action( 'admin_init', array($this, 'register_facebook_settings'));
		add_action( 'admin_init', array($this, 'register_twitter_settings'));
		add_action( 'admin_menu', array($this, 'open_graph_settings_page'));
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_settings_page_media'));
	}

	public function enqueue_settings_page_media() {
		if(!isset($_REQUEST['page']) || $_REQUEST['page'] !== 'lateralpixel-open-graph') return;

		wp_enqueue_media();
		wp_enqueue_script( 'media-upload' );
	}

	public function ensure_attachment_exists () {
		if(!isset($_POST['lateralpixel_open_graph']) || !isset($_POST['lateralpixel_open_graph']['og:image'])) {
			return;
		}

		$image = $_POST['lateralpixel_open_graph']['og:image'];
		$imagePath = get_attached_file($image);

		if(is_numeric($image) && !file_exists($imagePath)) {
			unset($_POST['lateralpixel_open_graph']['og:image']);
		}
	}


	public function open_graph_settings_page() {
		add_submenu_page('options-general.php', 'Open Graph Settings', 'Lateralpixel Open Graph', 'edit_posts', 'lateralpixel-open-graph', array($this, 'open_graph_settings_page_cb'));
	}


	public function open_graph_settings_page_cb() {
	?>
		<div
			id="lpSettingsPage"
			class="
				wrap
				<?php echo Utilities::get_option('og:image') ? '' : 'has-no-image '; ?>
				<?php echo Utilities::get_option('force_all') ? 'is-forcing-all ' : '' ?>"
			>
			<h1>Lateralpixel Open Graph Settings</h1>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-1">
					<div id="post-body-content" class="postbox-container">
						<form method="post" action="options.php">
							<?php wp_nonce_field('update-options'); ?>
							<?php
								settings_fields( 'lateralpixel_open_graph_settings' );
								do_settings_sections( self::$admin_settings_page_slug );
								submit_button();
							?>
						</form>
					</div>

				</div>
			</div>
		</div>

	<?php
	}


	public function register_main_setting() {
		register_setting( 'lateralpixel_open_graph_settings', self::$options_prefix);
	}


	public function register_facebook_settings() {
		add_settings_section( self::$options_prefix . '_facebook_parameters', 'Facebook Parameters', array( $this, 'cb_facebook_parameters_section' ), self::$admin_settings_page_slug );

		add_settings_field( self::$options_short_prefix . '_facebook_admin_ids', 'Facebook Admin IDs', array( $this, 'cb_field_facebook_admin_ids' ), self::$admin_settings_page_slug, self::$options_prefix . '_facebook_parameters' );

		add_settings_field( self::$options_short_prefix . '_facebook_app_id', 'Facebook App ID', array( $this, 'cb_field_facebook_app_id' ), self::$admin_settings_page_slug, self::$options_prefix . '_facebook_parameters' );
	}



	public function register_twitter_settings() {
		add_settings_section( self::$options_prefix . '_twitter_parameters', 'Twitter Parameters', array( $this, 'cb_twitter_parameters_section' ), self::$admin_settings_page_slug );

		add_settings_field( self::$options_short_prefix . '_twitter_card', 'Default Twitter Card', array( $this, 'cb_field_twitter_card' ), self::$admin_settings_page_slug, self::$options_prefix . '_twitter_parameters' );

		add_settings_field( self::$options_short_prefix . '_twitter_description', 'Default Twitter Description', array( $this, 'cb_field_twitter_description' ), self::$admin_settings_page_slug, self::$options_prefix . '_twitter_parameters' );

		add_settings_field( self::$options_short_prefix . '_twitter_creator', 'Default Twitter Creator', array( $this, 'cb_field_twitter_creator' ), self::$admin_settings_page_slug, self::$options_prefix . '_twitter_parameters' );

		add_settings_field( self::$options_short_prefix . '_twitter_site', 'Default Twitter Site', array( $this, 'cb_field_twitter_site' ), self::$admin_settings_page_slug, self::$options_prefix . '_twitter_parameters' );
	}


	public function register_settings () {

		add_settings_field( self::$options_short_prefix . '_force_all', 'Force All', array( $this, 'cb_field_force_all' ), self::$admin_settings_page_slug, self::$options_prefix . '_fallbacks' );

		add_settings_section( self::$options_prefix . '_fallbacks', 'Default Fallbacks', array( $this, 'cb_fallbacks_section' ), self::$admin_settings_page_slug );

		//add_settings_field( self::$options_short_prefix . '_type', 'Default Type', array( $this, 'cb_field_type' ), self::$admin_settings_page_slug, self::$options_prefix . '_fallbacks' );

		add_settings_field( self::$options_short_prefix . '_title', 'Default Title', array( $this, 'cb_field_title' ), self::$admin_settings_page_slug, self::$options_prefix . '_fallbacks' );

		add_settings_field( self::$options_short_prefix . '_description', 'Default Description', array( $this, 'cb_field_description' ), self::$admin_settings_page_slug, self::$options_prefix . '_fallbacks' );

		add_settings_field( self::$options_short_prefix . '_image', 'Default Image', array( $this, 'cb_field_image' ), self::$admin_settings_page_slug, self::$options_prefix . '_fallbacks' );
	}


	public function checked($key) {
		return Utilities::get_option($key) === 'on' ? 'checked' : '';
	}


	public function cb_fallbacks_section() {
		echo '<p>These settings will serve as fallbacks in case individual pages/posts do not have information supplied. If you wish to force these values to always override individual posts/pages, check the box under each option.</p>';
	}


	public function cb_facebook_parameters_section() {
		echo '<p>Optionally modify Default / fallback settings for Facebook-related tags, including those that tie your site to a partiular Facebook page or account.</p>';
	}


	public function cb_twitter_parameters_section() {
		echo '<p>Optionally modify global/fallback settings for Twitter-related Open Graph tags.</p>';
	}


	public function cb_field_type() {
		?>
		<fieldset class="LP_Box LP_Box--standOut">
			<p>If left blank, 'website' will be used.</p>
			<input type="text" value="<?php echo Utilities::get_option('og:type'); ?>" name="<?php echo Utilities::get_field_name('og:type'); ?>" id="ogType" />

			<div class="LP_Box-checkboxGroup">
				<input type="checkbox" <?php echo $this->checked('og:type_force'); ?> name="<?php echo Utilities::get_field_name('og:type_force'); ?>" id="ogTypeForce">
				<label for="ogTypeForce">Force Default Type</label>
			
			</div>

		</fieldset>
		<?php
	}


	public function cb_field_force_all() {
		?>
		<fieldset class="LP_Box LP_Box--standOut">
		

			<div class="LP_Box-checkboxGroup is-immune">
				<input type="checkbox" <?php echo $this->checked('force_all'); ?> name="<?php echo Utilities::get_field_name('force_all'); ?>" id="ogForceAll">
				<label for="ogForceAll"><strong>Force All Fallback Settings</strong></label>
					
			</div>

		</fieldset>
		<?php
	}


	public function cb_field_title() {
		?>
		<fieldset class="LP_Box LP_Box--standOut">
			<p>If left blank, the site title will be used.</p>
			<input type="text" value="<?php echo Utilities::get_option('og:title'); ?>" name="<?php echo Utilities::get_field_name('og:title'); ?>" id="ogDescription" />

			<div class="LP_Box-checkboxGroup">
				<input type="checkbox" <?php echo $this->checked('og:title_force'); ?> name="<?php echo Utilities::get_field_name('og:title_force'); ?>" id="ogTitleForce">
				<label for="ogTitleForce">Force Default Title</label>
			
			</div>
		</fieldset>
		<?php
	}


	public function cb_field_description() {
		?>
		<fieldset class="LP_Box LP_Box--standOut">
			<p>If left blank, the site description will be used.</p>
			<input type="text" value="<?php echo Utilities::get_option('og:description'); ?>" name="<?php echo Utilities::get_field_name('og:description'); ?>" id="ogDescription" />

			<div class="LP_Box-checkboxGroup">
				<input type="checkbox" <?php echo $this->checked('og:description_force'); ?> name="<?php echo Utilities::get_field_name('og:description_force'); ?>" id="ogDescriptionForce">
				<label for="ogDescriptionForce">Force Default Description</label>
				
			</div>
		</fieldset>
		<?php
	}

	public function cb_field_twitter_card() {
		?>
		<fieldset class="LP_Box LP_Box--standOut">
			<p>The type of Twitter card that will be generated for Open Graph. To learn about what these types mean, see <a target="_blank" href="https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/abouts-cards">Twitter's documentation</a>.</p>

			<?php $cardValue = Utilities::get_option('twitter:card'); ?>

			<select name="<?php echo Utilities::get_field_name('twitter:card'); ?>" id="ogTwitterCard">
				<option <?php selected($cardValue, 'summary'); ?> value="summary">Summary</option>
				<option <?php selected($cardValue, 'summary_large_image'); ?> value="summary_large_image">Large Summary</option>
				<option <?php selected($cardValue, 'app'); ?> value="app">App</option>
				<option <?php selected($cardValue, 'player'); ?> value="player">Player</option>
			</select>

			<div class="LP_Box-checkboxGroup">
				<input type="checkbox" <?php echo $this->checked('twitter:card_force'); ?> name="<?php echo Utilities::get_field_name('twitter:card_force'); ?>" id="ogTwitterCardForce">
				<label for="ogTwitterCardForce">Force Default Twitter Card</label>
				
			</div>
		</fieldset>
		<?php
	}


	public function cb_field_twitter_description() {
		?>
		<fieldset class="LP_Box LP_Box--standOut">
			<p>If left blank, the description will be used.</p>
			<input type="text" value="<?php echo Utilities::get_option('twitter:description'); ?>" name="<?php echo Utilities::get_field_name('twitter:description'); ?>" id="ogTwitterDescription" />

			<div class="LP_Box-checkboxGroup">
				<input type="checkbox" <?php echo $this->checked('twitter:description_force'); ?> name="<?php echo Utilities::get_field_name('twitter:description_force'); ?>" id="ogTwitterDescriptionForce">
				<label for="ogTwitterDescriptionForce">Force Default Twitter Description
				</label>
				
			</div>
		</fieldset>
		<?php
	}


	public function cb_field_twitter_creator() {
		?>
		<fieldset class="LP_Box LP_Box--standOut">
			<p>Enter the Twitter handle for the primary author. If left blank, the tag will be omitted, unless it's set at the post/page level.</p>
			<input type="text" value="<?php echo Utilities::get_option('twitter:creator'); ?>" name="<?php echo Utilities::get_field_name('twitter:creator'); ?>" id="ogTwitterCreator" />

			<div class="LP_Box-checkboxGroup">
				<input type="checkbox" <?php echo $this->checked('twitter:creator_force'); ?> name="<?php echo Utilities::get_field_name('twitter:creator_force'); ?>" id="ogTwitterCreatorForce">
				<label for="ogTwitterCreatorForce">Force Default Twitter Creator
				</label>
				
			</div>
		</fieldset>
		<?php
	}

	
	public function cb_field_twitter_site() {
		?>
		<fieldset class="LP_Box LP_Box--standOut">
			<p>Enter the Twitter handle for the site itself. It doesn't matter if the '@' symbol is included. If left blank, the tag will be omitted.</p>
			<input type="text" value="<?php echo Utilities::get_option('twitter:site'); ?>" name="<?php echo Utilities::get_field_name('twitter:site'); ?>" id="ogTwitterSite" />
		</fieldset>
		<?php
	}


	public function cb_field_image() {
		$imageID = Utilities::get_option('og:image');

		//-- Ensure the image exists before getting the URL.
		if(empty($imageID)) {
			$imageURL = '';
		} else {
			$imageAttachment = wp_get_attachment_image_src($imageID, 'medium');
			$imageURL = isset($imageAttachment[0]) ? $imageAttachment[0] : '';
		}

		?>
		<fieldset class="LP_Box LP_Box--standOut">
			<p>If left blank, the featured image on the home page will be used.</p>
			<div class="LP_ImageHolder"
				id="lpImageHolder"
				style="background-image: url('<?php echo $imageURL; ?>')">
				<span class="LP_ImageHolder-remove" id="ogRemoveImage">x</span>
			</div>
			<span class="howto" id="lpUploadedFileName"><?php echo basename($imageURL); ?></span>
			<div class="LP_Box-buttonWrapper">
				<a class="button button-primary button-large" id="lpImageSelectButton">Choose File</a>
				<span>No image selected.</span>
			</div>
			<input id="lpImage" type="hidden" name="<?php echo Utilities::get_field_name('og:image'); ?>" value="<?php echo Utilities::get_option('og:image'); ?>" />

			<div class="LP_Box-checkboxGroup">
				<input type="checkbox" <?php echo $this->checked('og:image_force'); ?> name="<?php echo Utilities::get_field_name('og:image_force'); ?>" id="ogImageForce">
				<label for="ogImageForce">Force Default Image</label>
	
			</div>
		</fieldset>
		<?php
	}


	public function cb_field_facebook_app_id() {
		?>
		<fieldset class="LP_Box LP_Box--standOut">
			<p>Enter the ID of the Facebook app you'd like to grant access to this URL.</p>
			<input type="text" value="<?php echo Utilities::get_option('fb:app_id'); ?>" name="<?php echo Utilities::get_field_name('fb:app_id'); ?>" id="ogFacebookAppID" />
		</fieldset>
		<?php
	}


	public function cb_field_facebook_admin_ids() {
		?>
		<fieldset class="LP_Box LP_Box--standOut">
			<p>Enter the user ID of a person you'd like to give admin access to view insights about this URL.</p>
			<input type="text" value="<?php echo Utilities::get_option('fb:admins'); ?>" name="<?php echo Utilities::get_field_name('fb:admins'); ?>" id="ogFacebookAdminIDs" />
		</fieldset>
		<?php
	}
}
