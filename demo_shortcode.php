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

	WP_Shortcodes_API::GetInstance()->
            add_shortcode('demo2', 'shortcode_demo2')->
            add_att('noun')->
            add_att('adjective')->
            add_media_button(array(
                'shortcode' => 'demo2',
                'title' => 'My Demo Title 2',
                'icon_url' => null,
                'intro' => 'This is my demo intro text 2',
                'input_atts' => WP_Shortcodes_API::GetShortcodeAtts('demo2')
            ));
} else {
    // fallback if the plugin is unavailable.
    add_shortcode('demo', 'shortcode_demo');
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


if (class_exists('WP_Shortcodes_API')) {
    WP_Shortcodes_API::GetInstance()->
            add_shortcode('newtest', 'shortcode_new')->
            add_att('test')->
            add_att('junk')->
            add_media_button(array(
                'shortcode' => 'newtest',
                'title' => 'My new Title',
                'icon_url' => "newPath",
                'intro' => 'This is my new intro text',
                'input_atts' => WP_Shortcodes_API::GetShortcodeAtts('newtest')
            ));
} else {
    // fallback if the plugin is unavailable.
    add_shortcode('newtest', 'shortcode_new');
}

function shortcode_new($atts) {
    extract(shortcode_atts(array(
                'test' => 'mark',
                'junk' => 'awesome',
                    ), $atts));
    $name = (isset($atts['name'])) ? $atts['name'] : 'Mark';
    $adjective = (isset($atts['adjective'])) ? $atts['adjective'] : 'Awesome';
    return $name . " is " . $adjective;
}
