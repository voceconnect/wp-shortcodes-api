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
        }

        public function add_shortcode($shortcode_name, $callback) {
            if (isset($this->shortcodes[$shortcode_name]))
                return false;
            $shortcode = new Add_Shortcode($shortcode_name, $callback);
            $this->shortcodes[$shortcode_name] = $shortcode;
            $this->register_shortcode($shortcode_name);
            return $shortcode;
        }

        private function get_callback($shortcode_name) {
            $shortcode_options = get_option(self::$shortcode_options_key);
            if (!$shortcode_options)
                return false;
            return $shortcode_options[$shortcode_name]['callback'];
        }

        public function register_shortcode($shortcode_name) {
            add_shortcode($shortcode_name, $this->get_callback($shortcode_name));
        }

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

        public static function GetShortcodeAtts($shortcode_name) {
            $shortcode_options = get_option(self::$shortcode_options_key);
            if ((isset($shortcode_options[$shortcode_name])) && (!empty($shortcode_options[$shortcode_name]['atts']))) {
                return $shortcode_options[$shortcode_name]['atts'];
            } else {
                return false;
            }
        }

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

    }

    class Add_Shortcode {

        private $name;
        private $callback;

        public function __construct($name, $callback) {
            $this->name = $name;
            $this->callback = $callback;
            $this->save_shortcode_data();
            $this->reset_shortcode_atts();
        }

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

        private function reset_shortcode_atts() {
            $shortcode_data = get_option(WP_Shortcodes_API::$shortcode_options_key);
            $shortcode_data[$this->name]['atts'] = array();
            $this->save_shortcode_data($shortcode_data);
        }

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

        public function add_media_button($args) {
            $shortcode_name = $args['shortcode'];
            $title = $args['title'];
            $intro = $args['intro'];
            $input_atts = $args['input_atts'];
            $media_button = new WP_Shortcodes_Media_Button($shortcode_name, $title, $intro, $input_atts);
            return $this;
        }

    }

}




require_once('demo_shortcode.php');
