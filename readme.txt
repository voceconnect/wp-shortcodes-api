=== Plugin Name ===
Contributors: markparolisi, smccafferty
Tags: shortcode
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 0.2

Stores information about available shortcodes as an option and provides a method
to easily add a media button with configurable options.

== Description ==

The plugin take all of the data registered and created a multi-dimensional 
array in an option called '_shortcodes'. Existing shortcodes that WP is away of
are also stored here with just their name and callback.

A few static methods for checking on shortcode info are provided.
`GetShortcodeAtts($shortcode_name)`
`ShortcodeInPost($shortcode_name, $post_id)`

Creating a new media button in the editor is easy with the `add_media_button()` 
method. Just pass your shortcode name, attributes, a page title, introductory 
text, and an icon image url.

== Installation ==

1. Upload `wp-shortcodes-api.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. example implementation:

    `if (class_exists('WP_Shortcodes_API')) {
        WP_Shortcodes_API::GetInstance()->
            add_shortcode($shortcode_name, $callback)->
            add_att($att_name)->
            add_att($att_name2)->
            add_media_button(array(
                'shortcode' => $shortcode_name,
                'title' => $thickbox_title,
                'icon_url' => $button_icon_url,
                'intro' => $introduction_to_form,
                'input_atts' => WP_Shortcodes_API::GetShortcodeAtts($shortcode_name)
            ));
    } else {
        // fallback if the plugin is unavailable.
        add_shortcode($shorcode_name, $callback);
    }`

== Changelog ==


= 0.1 =
Initial release.
