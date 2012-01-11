<?php
/*
  Plugin Name: WP Shortcodes API
  Plugin URI: http://plugins.voceconnect.com/
  Description: Advanced Shortcodes.
  Author: markparolisi
  Contributors: smccafferty
  Version: 0.1
  Author URI: http://plugins.voceconnect.com/
 */


if (!class_exists('WP_Shortcodes_API')) {

    class WP_Shortcodes_API {

        public static $shortcode_options_key = "_shortcodes";
        private static $instance;
        private $shortcodes;

        public static function GetInstance() {
            if (!isset(self::$instance)) {
                self::$instance = new WP_Shortcodes_API();
            }
            return self::$instance;
        }

        private function __construct() {
            $this->shortcodes = array();
            $this->add_exisiting_shortcodes_to_storage();
            add_action('plugins_loaded', array(&$this, 'cleanup_shortcode_storage'), 100);
            add_action('wp_ajax_get_shortcode_names', array(__CLASS__, 'AjaxGetShortcodeNames'));
        }

        /**
         * Create the shortcode object
         * @param string $shortcode_name
         * @param string|array $callback
         * @return boolean|shortcode object 
         */
        public function add_shortcode($shortcode_name, $callback) {
            if (isset($this->shortcodes[$shortcode_name]))
                return false;
            $shortcode = new Add_Shortcode($shortcode_name, $callback);
            $this->shortcodes[$shortcode_name] = $shortcode;
            $this->register_shortcode($shortcode_name);
            return $shortcode;
        }

        /**
         * Get the callback function for the shortcode from the option field.
         * @param string $shortcode_name
         * @return string|array callback function 
         */
        private function get_callback($shortcode_name) {
            $shortcode_options = get_option(self::$shortcode_options_key);
            if (!$shortcode_options)
                return false;
            return $shortcode_options[$shortcode_name]['callback'];
        }

        /**
         * Sends the shortcode and callback to the native WP shortcode API
         * @param string $shortcode_name 
         */
        public function register_shortcode($shortcode_name) {
            add_shortcode($shortcode_name, $this->get_callback($shortcode_name));
        }

        /**
         * Add the shortcodes registered to our API into the DB option
         * @global array $shortcode_tags All of the shortcodes registered in WP
         */
        private function add_exisiting_shortcodes_to_storage() {
            global $shortcode_tags;
            $shortcode_options = get_option(self::$shortcode_options_key);
            if (is_array($shortcode_tags) && is_array($shortcode_options)) {
                foreach ($shortcode_tags as $key => $value) {
                    if (!in_array($key, $shortcode_options)) {
                        $shortcode_options[$key] = array();
                        $shortcode_options[$key]['callback'] = $value;
                    }
                }
            }
            update_option(self::$shortcode_options_key, $shortcode_options);
        }

        /**
         * Remove any shortcode entries in the DB that are no longer known by WP
         * @global array $shortcode_tags All of the shortcodes registered in WP
         */
        public function cleanup_shortcode_storage() {
            global $shortcode_tags;
            $shortcode_options = get_option(self::$shortcode_options_key);
            if (is_array($shortcode_tags) && is_array($shortcode_options)) {
                foreach ($shortcode_options as $key => $value) {
                    if (!array_key_exists($key, $shortcode_tags)) {
                        unset($shortcode_options[$key]);
                    }
                }
            }
            update_option(self::$shortcode_options_key, $shortcode_options);
        }

        /**
         * Return the array of attributes associated with the shortcode.
         * @param string $shortcode_name
         * @return boolean|array of shortcode attributes
         */
        public static function GetShortcodeAtts($shortcode_name) {
            $shortcode_options = get_option(self::$shortcode_options_key);
            if ((isset($shortcode_options[$shortcode_name])) && (!empty($shortcode_options[$shortcode_name]['atts']))) {
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
        public static function ShortcodeInPost($shortcode_name, $post_id) {
            $post = get_post($post_id);
            if ($post) {
                $pattern = "(.?)\[($shortcode_name)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)";
                preg_match('/' . $pattern . '/s', $post->post_content, $matches);
                if (is_array($matches) && isset($matches[2]) && $matches[2] == $shortcode_name) {
                    return true;
                }
            }
            return false;
        }

        public static function AjaxGetShortcodeNames() {
            global $shortcode_names;
            return $shortcode_names;
            die();
        }

    }

    class Add_Shortcode {

        private $name;
        private $callback;
        private $media_button = false;

        public function __construct($name, $callback) {
            $this->name = $name;
            $this->callback = $callback;
            $this->save_shortcode_data();
            $this->reset_shortcode_atts();
        }

        /**
         *
         * @param string $att Shortcode attribute
         * @return boolean|\Add_Shortcode 
         */
        public function add_att($att) {
            $shortcode_data = get_option(WP_Shortcodes_API::$shortcode_options_key);
            if (!isset($shortcode_data[$this->name]))
                return false;
            if (!isset($shortcode_data[$this->name]['atts']))
                $shortcode_data[$this->name]['atts'] = array();
            if (!in_array($att, $shortcode_data[$this->name]['atts']))
                $shortcode_data[$this->name]['atts'][] = $att;
            $this->save_shortcode_data($shortcode_data);
            return $this;
        }

        /**
         * 
         */
        private function reset_shortcode_atts() {
            $shortcode_data = get_option(WP_Shortcodes_API::$shortcode_options_key);
            $shortcode_data[$this->name]['atts'] = array();
            $this->save_shortcode_data($shortcode_data);
        }

        /**
         *
         * @param type $shortcode_data
         * @return type 
         */
        private function save_shortcode_data($shortcode_data = null) {
            if (!$shortcode_data) {
                $shortcode_data = get_option(WP_Shortcodes_API::$shortcode_options_key);
                if (!$shortcode_data)
                    $shortcode_data = array();
                if (!isset($shortcode_data[$this->name]))
                    $shortcode_data[$this->name] = array();
                $shortcode_data[$this->name]['callback'] = $this->callback;
            }
            update_option(WP_Shortcodes_API::$shortcode_options_key, $shortcode_data);
            return get_option(WP_Shortcodes_API::$shortcode_options_key);
        }

        /**
         *
         * @param array $args
         * @return \Add_Shortcode 
         */
        public function add_media_button($args) {
            if (!is_array($args))
                return false;
            $shortcode_name = $args['shortcode'];
            $title = $args['title'];
            $icon_url = $args['icon_url'];
            $intro = $args['intro'];
            $input_atts = $args['input_atts'];
            $this->media_button = new WP_Shortcodes_Media_Button($shortcode_name, $title, $icon_url, $intro, $input_atts);
            $this->store_shortcode_icon($icon_url);
            return $this;
        }

        /**
         *
         * @param string $icon_url 
         */
        private function store_shortcode_icon($icon_url) {
            $shortcode_data = get_option(WP_Shortcodes_API::$shortcode_options_key);
            if (empty($icon_url) && isset($shortcode_data[$this->name]['icon_url']))
                unset($shortcode_data[$this->name]['icon_url']);
            else
                $shortcode_data[$this->name]['icon_url'] = $icon_url;
            $this->save_shortcode_data($shortcode_data);
        }

    }

}

require_once('demo_shortcode.php');

class WP_Shortcodes_Media_Button {

    private $title;
    private $icon_url;
    private $intro_text;
    private $sc_args;
    private $shortcode;

    public function __construct($shortcode, $title, $icon_url, $intro_text = "", $sc_args = array()) {
        $this->shortcode = $shortcode;
        $this->title = $title;
        $this->icon_url = (isset($icon_url)) ? $icon_url : plugins_url(dirname(plugin_basename(__FILE__))) . '/shortcode-icon.png';
        $this->intro_text = $intro_text;
        $this->sc_args = $sc_args;

        $this->create_media_buttons();
    }

    /**
     * 
     */
    function create_media_buttons() {
        add_action('media_buttons', array($this, 'media_button'), 100);
        add_action('wp_ajax_shortcode_popup', array(&$this, 'popup'));
    }

    /**
     *
     * @global type $post_ID
     * @global type $temp_ID 
     */
    function media_button() {
        global $post_ID, $temp_ID;
        $title = __($this->title, 'wp_shortcodes_api');
        $iframe_post_id = (int) ( 0 == $post_ID ? $temp_ID : $post_ID );
        $site_url = admin_url("/admin-ajax.php?post_id=$iframe_post_id&amp;action=shortcode_popup&amp;TB_iframe=true&amp;width=768");
        echo "<a href=$site_url&id=add_form' onclick='return false;' id='popup' class='thickbox' title='$title'><img src='$this->icon_url' alt='$title' width='15' height='15' /></a>";
    }

    /**
     * 
     */
    function popup() {
        $script_url = plugins_url(dirname(plugin_basename(__FILE__))) . '/wp-shortcodes-api.js';
        wp_deregister_script('wp-shortcodes');
        wp_enqueue_script('wp-shortcodes', $script_url, 'jquery');
        wp_print_scripts(array('jquery', 'wp-shortcodes'));
        wp_enqueue_style( 'colors' );
        wp_enqueue_style( 'ie' );
        do_action('admin_print_styles');
        ?>
        <div class="wp-shortcode-popup wrap" style="padding: 10px 20px;">
            <h2 id="shortcode-title"><?php echo $this->title ?></h2>
            <p id="shortcode-intro"><?php echo $this->intro_text ?></p>
            <?php if ($this->sc_args) : ?>
                <table class="form-table">    
                    <form id="wp-shortcode" action="" >
                        <tbody>
                            <?php foreach ($this->sc_args as $arg) : ?>
                                <tr valign="top">        
                                    <th scopt="row">
                                        <label for="<?php echo $arg ?>"><?php echo ucwords($arg) ?></label>
                                    </th>
                                    <td>
                                        <input type="text" class="text" name="<?php echo $arg ?>" id="<?php echo $arg ?>" />
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <input type="hidden" id="shortcode-name" value="<?php echo $this->shortcode ?>" />
                    </form>
                    </tbody>
                </table>
            <?php endif; ?>
            <div class="submit">
                <input type="button" name="submit-shortcode-api" id="submit-shortcode-api" class="button" value="Insert into Post">
            </div>
        </div>
        <?php
        die();
    }

}

