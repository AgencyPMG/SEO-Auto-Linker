<?php
/*
Plugin Name: SEO Auto Linker Migration
Description: Migrates your old SEO Auto Linker keywords to the new admin UI.  Just activate and you're done. Feed free to deactivate the plugin after activating once.
Version: Thereisnoversion
Author: Christopher Davis
Author URI: http://christopherdavis.me
License: GPL2

    Copyright 2012 Christopher Davis

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!class_exists('SEO_Auto_Linker_Base'))
{
    require_once(plugin_dir_path(__FILE__) . 'inc/base.php');
}

register_activation_hook(
    __FILE__,
    array('SEO_Auto_Linker_Migration', 'migrate')
);

class SEO_Auto_Linker_Migration extends SEO_Auto_Linker_Base
{
    public static function migrate()
    {
        $orig_opts = get_option(self::OLD_SETTING, false);
        $opts = $orig_opts;
        if(!$opts) return;
        $keywords = !empty($opts['kw']) ? $opts['kw'] : false;
        if(!$keywords) return;
        foreach($keywords as $index => $kw)
        {
            $url = isset($opts['url'][$index]) ? $opts['url'][$index] : false;
            if(!$url) continue;

            $post_id = wp_insert_post(array(
                'post_title'  => $url,
                'post_status' => 'publish',
                'post_type'   => self::POST_TYPE
            ));
            if(!$post_id) continue;
            
            $type = isset($opts['types'][$index]) ? $opts['types'][$index] : 'post';
            $bl = isset($opts['blacklist'][$index]) ? 
                explode(',', $opts['blacklist'][$index]) : array();
            $max =  isset($opts['max'][$index]) ? $opts['max'][$index] : 1;
            self::update($post_id, 'url', esc_url($url));
            self::update($post_id, 'blacklist', $bl);
            self::update($post_id, 'times', absint($max));
            self::update($post_id, "type_{$type}", 'on');
            self::update($post_id, 'keywords', $kw);
        }
        
        $blacklist = !empty($opts['site_wide_blacklist']) ?
            explode(',', $opts['site_wide_blacklist']) : false;
        if($blacklist)
        {
            $blacklist = implode("\n", $blacklist);
            update_option(self::SETTING, array(
                'blacklist' => $blacklist
            ));
        }
        delete_option(self::OLD_SETTING);
    }

    public static function update($post_id, $key, $val)
    {
        return update_post_meta(
            $post_id,
            self::get_key($key),
            $val
        );
    }
}
