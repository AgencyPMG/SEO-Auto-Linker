<?php
/*
Plugin Name: SEO Auto Linker
Plugin URI: http://pmg.co/seo-auto-linker
Description: Allows you to automatically link terms with in your post, page or custom post type content.
Version: 0.5
Author: Christopher Davis at PMG
Author URI: http://pmg.co/people/chris
*/

define( 'SEOAL_PATH', plugin_dir_path( __FILE__ ) );
define( 'SEOAL_URL', plugin_dir_url( __FILE__ ) );

if( is_admin() )
{
    require_once( SEOAL_PATH . 'inc/admin.php' );
}
else
{
    require_once( SEOAL_PATH . 'inc/front.php' );
}
