<pre>
<?php

if (class_exists('WP_Shortcodes_API')) {
    WP_Shortcodes_API::GetInstance()->add_shortcode('demo', 'shortcode_demo')->add_arg('height');
    var_dump(get_option('_shortcodes'));
}

function demo_shortcode($args) {
    extract(shortcode_atts(array(
                'name' => 'mark',
                'adjective' => 'awesome',
                    ), $atts));

    return $atts['name'] . " is " . $atts['adjective'];
}