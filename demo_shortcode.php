<?php

if (class_exists('WP_Shortcodes_API')) {
    WP_Shortcodes_API::GetInstance()->
            add_shortcode('demo', 'shortcode_demo')->
            add_att('name')->
            add_att('adjective')->
            add_media_button(array(
                'shortcode' => 'demo',
                'title' => 'My Demo Title',
                'icon_url' => null,
                'intro' => 'This is my demo intro text',
                'input_atts' => WP_Shortcodes_API::GetShortcodeAtts('demo')
            ));
}

function shortcode_demo($atts) {
    extract(shortcode_atts(array(
                'name' => 'mark',
                'adjective' => 'awesome',
                    ), $atts));
    $name = (isset($atts['name'])) ? $atts['name'] : 'Mark';
    $adjective = (isset($atts['adjective'])) ? $atts['adjective'] : 'Awesome';
    return $name . " is " . $adjective;
}
