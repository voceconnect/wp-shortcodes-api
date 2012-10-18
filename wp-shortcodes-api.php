<?php
/*
  Plugin Name: WP Shortcodes API
  Plugin URI: http://plugins.voceconnect.com/
  Description: Advanced Shortcodes.
  Author: markparolisi, voceplatforms
  Contributors: smccafferty
  Version: 0.1
  Author URI: http://plugins.voceconnect.com/
 */

if ( !class_exists( 'WP_Shortcodes_API' ) ) {

	class WP_Shortcodes_API {

		public static $shortcode_options_key = "_shortcodes";
		private static $instance;
		private $shortcodes;

		public static function GetInstance() {
			if ( !isset( self::$instance ) ) {
				self::$instance = new WP_Shortcodes_API();
			}
			return self::$instance;
		}

		private function __construct() {
			$this->shortcodes = array( );
			$this->add_exisiting_shortcodes_to_storage();
			add_action( 'plugins_loaded', array( &$this, 'cleanup_shortcode_storage' ), 100 );
		}

		/**
		 * Create the shortcode object
		 * @param string $shortcode_name
		 * @param string|array $callback
		 * @return boolean|shortcode object 
		 */
		public function add_shortcode( $shortcode_name, $callback ) {
			if ( isset( $this->shortcodes[$shortcode_name] ) )
				return false;
			$shortcode = new Add_Shortcode( $shortcode_name, $callback );
			$this->shortcodes[$shortcode_name] = $shortcode;
			$this->register_shortcode( $shortcode_name );
			return $shortcode;
		}

		/**
		 * Get the callback function for the shortcode from the option field.
		 * @param string $shortcode_name
		 * @return string|array callback function 
		 */
		private function get_callback( $shortcode_name ) {
			$shortcode_options = get_option( self::$shortcode_options_key );
			if ( !$shortcode_options )
				return false;
			return $shortcode_options[$shortcode_name]['callback'];
		}

		/**
		 * Sends the shortcode and callback to the native WP shortcode API
		 * @param string $shortcode_name 
		 */
		public function register_shortcode( $shortcode_name ) {
			add_shortcode( $shortcode_name, $this->get_callback( $shortcode_name ) );
		}

		/**
		 * Add the shortcodes registered to our API into the DB option
		 * @global array $shortcode_tags All of the shortcodes registered in WP
		 */
		private function add_exisiting_shortcodes_to_storage() {
			global $shortcode_tags;
			$shortcode_options = get_option( self::$shortcode_options_key );
			if ( is_array( $shortcode_tags ) && is_array( $shortcode_options ) ) {
				foreach ($shortcode_tags as $key => $value) {
					if ( !in_array( $key, $shortcode_options ) ) {
						$shortcode_options[$key] = array( );
						$shortcode_options[$key]['callback'] = $value;
					}
				}
			}
			update_option( self::$shortcode_options_key, $shortcode_options );
		}

		/**
		 * Remove any shortcode entries in the DB that are no longer known by WP
		 * @global array $shortcode_tags All of the shortcodes registered in WP
		 */
		public function cleanup_shortcode_storage() {
			global $shortcode_tags;
			$shortcode_options = get_option( self::$shortcode_options_key );
			if ( is_array( $shortcode_tags ) && is_array( $shortcode_options ) ) {
				foreach ($shortcode_options as $key => $value) {
					if ( !array_key_exists( $key, $shortcode_tags ) ) {
						unset( $shortcode_options[$key] );
					}
				}
			}
			update_option( self::$shortcode_options_key, $shortcode_options );
		}

		/**
		 * Return the array of attributes associated with the shortcode.
		 * @param string $shortcode_name
		 * @return boolean|array of shortcode attributes
		 */
		public static function GetShortcodeAtts( $shortcode_name ) {
			$shortcode_options = get_option( self::$shortcode_options_key );
			if ( (isset( $shortcode_options[$shortcode_name] )) && (!empty( $shortcode_options[$shortcode_name]['atts'] )) ) {
				return $shortcode_options[$shortcode_name]['atts'];
			} else {
				return false;
			}
		}

		/**
		 * Check to see if the shortcode exists in the post content
		 * @param string $shortcode_name
		 * @param int $post_id
		 * @return boolean 
		 */
		public static function ShortcodeInPost( $shortcode_name, $post_id ) {
			$post = get_post( $post_id );
			if ( $post ) {
				$pattern = "(.?)\[($shortcode_name)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)";
				preg_match( '/' . $pattern . '/s', $post->post_content, $matches );
				if ( is_array( $matches ) && isset( $matches[2] ) && $matches[2] == $shortcode_name ) {
					return true;
				}
			}
			return false;
		}

	}

	// end WP_Shortcodes_API class

	class Add_Shortcode {

		private $name;
		private $callback;
		private $media_button = false;

		public function __construct( $name, $callback ) {
			$this->name = $name;
			$this->callback = $callback;
			$this->save_shortcode_data();
			$this->reset_shortcode_atts();
		}

		/**
		 * Add the registered shortcode attribute to the database
		 * @param string $att Shortcode attribute
		 * @return boolean|\Add_Shortcode 
		 */
		public function add_att( $att ) {
			$shortcode_data = get_option( WP_Shortcodes_API::$shortcode_options_key );
			if ( !isset( $shortcode_data[$this->name] ) )
				return false;
			if ( !isset( $shortcode_data[$this->name]['atts'] ) )
				$shortcode_data[$this->name]['atts'] = array( );
			if ( !in_array( $att, $shortcode_data[$this->name]['atts'] ) )
				$shortcode_data[$this->name]['atts'][] = $att;
			$this->save_shortcode_data( $shortcode_data );
			return $this;
		}

		/**
		 * Clear the shortcode attributes
		 */
		private function reset_shortcode_atts() {
			$shortcode_data = get_option( WP_Shortcodes_API::$shortcode_options_key );
			$shortcode_data[$this->name]['atts'] = array( );
			$this->save_shortcode_data( $shortcode_data );
		}

		/**
		 * Add the shortcode data to the database
		 * @param array $shortcode_data
		 * @return type 
		 */
		private function save_shortcode_data( $shortcode_data = null ) {
			if ( !$shortcode_data ) {
				$shortcode_data = get_option( WP_Shortcodes_API::$shortcode_options_key );
				if ( !$shortcode_data )
					$shortcode_data = array( );
				if ( !isset( $shortcode_data[$this->name] ) )
					$shortcode_data[$this->name] = array( );
				$shortcode_data[$this->name]['callback'] = $this->callback;
			}
			update_option( WP_Shortcodes_API::$shortcode_options_key, $shortcode_data );
			return get_option( WP_Shortcodes_API::$shortcode_options_key );
		}

		/**
		 * Create the media button object
		 * @param array $args
		 * @return \Add_Shortcode 
		 */
		public function add_media_button( $args ) {
			if ( !is_array( $args ) )
				return false;
			$shortcode_name = $args['shortcode'];
			$title = esc_attr( $args['title'] );
			$icon = esc_attr( $args['icon'] );
			$intro = esc_attr( $args['intro'] );
			$input_atts = $args['input_atts'];
			$this->media_button = new WP_Shortcodes_Media_Button( $shortcode_name, $title, $icon, $intro, $input_atts );
			$this->store_shortcode_icon( $icon );
			return $this;
		}

		/**
		 * Save the icon URL in the database array
		 * @param string $icon 
		 */
		private function store_shortcode_icon( $icon ) {
			$shortcode_data = get_option( WP_Shortcodes_API::$shortcode_options_key );
			if ( empty( $icon ) && isset( $shortcode_data[$this->name]['icon'] ) )
				unset( $shortcode_data[$this->name]['icon'] );
			else
				$shortcode_data[$this->name]['icon'] = $icon;
			$this->save_shortcode_data( $shortcode_data );
		}

	}

	// end Add_Shortcode class

	class WP_Shortcodes_Media_Button {

		private $title;
		private $icon;
		private $intro_text;
		private $sc_atts;
		private $shortcode;

		public function __construct( $shortcode, $title, $icon, $intro_text = "", $sc_atts = array( ) ) {
			$this->shortcode = $shortcode;
			$this->title = $title;
			$this->icon = (!empty( $icon )) ? $icon : plugins_url( dirname( plugin_basename( __FILE__ ) ) ) . '/shortcode-icon.png';
			$this->intro_text = esc_attr( $intro_text );
			$this->sc_atts = $sc_atts;
			$this->create_media_buttons();
		}

		/**
		 * Handles the creation of the icon and page displayed when the icon is clicked
		 */
		function create_media_buttons() {
			add_action( 'media_buttons', array( $this, 'media_button' ), 100 );
			add_action( 'wp_ajax_shortcode_popup-' . $this->shortcode, array( $this, 'popup' ) );
		}

		/**
		 * Create the media button icon
		 * @global int $post_ID
		 * @global int $temp_ID 
		 */
		function media_button() {
			global $post_ID, $temp_ID;
			$title = $this->title;
			$iframe_post_id = (int) ( 0 == $post_ID ? $temp_ID : $post_ID );
			$site_url = admin_url( "/admin-ajax.php?post_id=$iframe_post_id&amp;action=shortcode_popup-$this->shortcode&amp;TB_iframe=true&amp;width=768" );
			$ext = pathinfo( $this->icon, PATHINFO_EXTENSION );
			if ( in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif' ) ) ) {
				$icon = "<img src='$this->icon' alt='$title' width='15' height='15' />";
			} else {
				$icon = $this->icon;
			}
			echo "<a href=$site_url&id=add_form' onclick='return false;' id='popup' class='thickbox' title='$title'>$icon</a>";
		}

		/**
		 * Page displayed in the ThickBox utilitized by the media icon
		 */
		function popup() {
			$script_url = plugins_url( dirname( plugin_basename( __FILE__ ) ) ) . '/wp-shortcodes-api.js';

			//register scripts
			wp_deregister_script( 'wp-shortcodes' );
			wp_enqueue_script( 'wp-shortcodes', $script_url, 'jquery' );
			wp_print_scripts( array( 'jquery', 'wp-shortcodes' ) );

			//register wp styles
			wp_enqueue_style( 'colors' );
			wp_enqueue_style( 'ie' );
			do_action( 'admin_print_styles' );
			echo $this->popup_contents( $this->shortcode, $this->title, $this->intro_text, $this->sc_atts );
			die();
		}

		/**
		 * The contents generated in the ThickBox
		 * @param str $shortcode
		 * @param str $title
		 * @param str $intro_text
		 * @param array $sc_atts 
		 */
		function popup_contents( $shortcode, $title, $intro_text, $sc_atts ) {
			?>
			<div class="wp-shortcode-popup wrap" style="padding: 10px 20px;">
				<h2 id="shortcode-title"><?php echo $title ?></h2>
				<p id="shortcode-intro"><?php echo $intro_text ?></p>
				<?php if ( $sc_atts ) : ?>
					<form id="wp-shortcode" action="" >
						<table class="form-table">    
							<tbody>
								<?php foreach ($sc_atts as $att) : ?>
									<tr valign="top">        
										<th scopt="row">
											<label for="<?php echo $att ?>"><?php echo ucwords( $att ) ?></label>
										</th>
										<td>
											<input type="text" class="text" name="<?php echo $att ?>" id="<?php echo $att ?>" />
										</td>
									</tr>
								<?php endforeach; ?>
							<input type="hidden" id="shortcode-name" value="<?php echo $shortcode ?>" />
							</tbody>
						</table>
					</form>
				<?php endif; ?>
				<p>Preview: <code id="shortcode-preview"></code></p>
				<div class="submit">
					<input type="button" name="submit-shortcode-api" id="submit-shortcode-api" class="button" value="Insert into Post">
				</div>
			</div>
			<?php
		}

	}

	// end WP_Shortcodes_Media_Button Class

	/**
	 * Template Tags 
	 */
	function has_shortcode( $shortcode_name, $post_id = 0 ) {
		$post = &get_post( $post_id );
		$post_id = isset( $post->ID ) ? $post->ID : (int) $post_id;
		return WP_Shortcodes_API::ShortcodeInPost( $shortcode_name, $post_id );
	}

} // end if class_exists condition

