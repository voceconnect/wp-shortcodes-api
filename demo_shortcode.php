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
} else {
    // fallback if the plugin is unavailable.
    add_shortcode('demo', 'shortcode_demo');
}

