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
            //global $shortcode_tags;
            //var_dump($shortcode_tags);
        }


    }

    class Add_Shortcode {

        private $name;
        private $callback;

        public function __construct($name, $callback) {
            $this->name = $name;
            $this->callback = $callback;
            $this->save_shortcode_data();
            $this->reset_shortcode_args();
        }

        public function add_arg($arg) {
            $shortcode_data = get_option(WP_Shortcodes_API::$shortcode_options_key);
            if (!isset($shortcode_data[$this->name]))
                return false;
            if (!isset($shortcode_data[$this->name]['args']))
                $shortcode_data[$this->name]['args'] = array();
            if (!in_array($arg, $shortcode_data[$this->name]['args']))
                $shortcode_data[$this->name]['args'][] = $arg;
            $this->save_shortcode_data($shortcode_data);
            return $this;
        }

        private function reset_shortcode_args() {
            $shortcode_data = get_option(WP_Shortcodes_API::$shortcode_options_key);
            $shortcode_data[$this->name]['args'] = array();
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
            //call Caffs class
            
            return $this;
        }

    }

}




require_once('demo_shortcode.php');
