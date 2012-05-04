<?php
/*
Plugin Name: SEO Auto Linker
Plugin URI: http://pmg.co/seo-auto-linker
Description: Allows you to automatically link terms with in your post, page or custom post type content.
Version: 0.7
Author: Christopher Davis at PMG
Author URI: http://christopherdavis.me
*/

define('SEOAL_PATH', plugin_dir_path(__FILE__));
define('SEOAL_URL', plugin_dir_url(__FILE__));

require_once(SEOAL_PATH . 'inc/base.php');
require_once(SEOAL_PATH . 'inc/post-type.php');
if(is_admin())
{
    require_once(SEOAL_PATH . 'inc/admin.php');
}
else
{
    require_once(SEOAL_PATH . 'inc/front.php');
}


add_action('init', 'seoal_load_textdomain');
/*
 * Loads the plugin's text domain for translation
 *
 * @uses load_plugin_textdomain
 * @since 0.7
 */
function seoal_load_textdomain()
{
    load_plugin_textdomain(
        'seoal',
        false,
        dirname(plugin_basename(__FILE__)) . '/lang/'
    );
}
