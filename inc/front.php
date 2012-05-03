<?php
class SEO_Auto_Linker_Front extends SEO_Auto_Linker_Base
{
    /*
     * Container for our post autolinker posts
     */
    protected static $links;

    /*
     * Container for our options
     */
    protected static $opts;

    /*
     * Container for the current post's permalink
     */
    protected static $permalink;

    /*
     * Adds actions and filters and such
     *
     * @since 0.7
     */
    public static function init()
    {
        add_filter(
            'the_content',
            array(get_class(), 'content'),
            1
        );
    }

    /*
     * Main event.  Filters the conntent to add links
     *
     * @since 0.7
     */
    public static function content($content)
    {
        global $post;
        if(!self::allowed($post)) return $content;

        // we're going to do a lot of counting...
        $counter = 0;

        $header_replacements = array();
        $link_replacements = array();
        $other_replacements = array();
        $shortcode_replacements = array();
        $filtered = $content;

        preg_match_all('/<h[1-6][^>]*>.+?<\/h[1-6]>/iu', $filtered, $headers);
        if(!empty($headers[0]))
        {
            $header_replacements = self::gen_replacements($headers[0], 'header');
            $filtered = self::replace($header_replacements, $filtered);
        }

        preg_match_all('/' . get_shortcode_regex() . '/', $filtered, $scodes);
        if(!empty($scodes[0]))
        {
            $shortcode_replacements = self::gen_replacements($scodes[0], 'shortcode');
            $filtered = self::replace($shortcode_replacements, $filtered);
        }

        preg_match_all('/<(img|input)(.*?) \/?>/iu', $filtered, $others);
        if(!empty($others[0]))
        {
            $other_replacements = self::gen_replacements($others[0], 'others');
            $filtered = self::replace($other_replacements, $filtered);
        }

        foreach(self::$links as $l)
        {
            preg_match_all(
                '/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/iu',
                $filtered,
                $links
            );
            if(!empty($links[0]))
            {
                $start = count($link_replacements);
                $tmp = self::gen_replacements($links[0], 'links', $start);
                $filtered = self::replace($tmp, $filtered);
                $link_replacements = array_merge(
                    $link_replacements,
                    $tmp
                );
            }

            $regex = self::get_kw_regex($l);
            $url = self::get_link_url($l);
            $max = self::get_link_max($l);
            $filtered = preg_replace(
                $regex,
                '$1<a href="' . esc_url( $url ) . '" title="$2">$2</a>$3',
                $filtered,
                $max
            );
        }

        $filtered = self::replace_bak($link_replacements, $filtered);
        $filtered = self::replace_bak($header_replacements, $filtered);
        $filtered = self::replace_bak($shortcode_replacements, $filtered);
        $filtered = self::replace_bak($other_replacements, $filtered);
        
        return $filtered;
    }

    /*
     * Determins whether or not a post can be editted
     */
    protected static function allowed($post)
    {
        $rv = true;
        if(!is_singular() || !in_the_loop()) $rv = false;

        self::setup_links($post);
        if(!self::$links) $rv = false;

        if(in_array(self::$permalink, self::$opts['blacklist'])) $rv = false;

        return apply_filters('seoal_allowed', $rv, $post);
    }

    /*
     * Fetch all of the links posts
     *
     * @since 0.7
     */
    protected static function setup_links($post)
    {
        self::$opts = get_option(self::SETTING, array());
        if(!isset(self::$opts['blacklist'])) self::$opts['blacklist'] = array();
        self::$permalink = get_permalink($post);
        $links = get_posts(array(
            'post_type'   => self::POST_TYPE,
            'numberposts' => -1,
            'meta_query'  => array(
                array(
                    'key'     => self::get_key("type_{$post->post_type}"),
                    'value'   => 'on',
                    'compare' => '='
                )
            )
        ));
        $rv = array();
        foreach($links as $l)
        {
            $blacklist = get_post_meta($l->ID, self::get_key('blacklist'), true);
            if(!$blacklist || !in_array(self::$permalink, $blacklist))
                $rv[] = $l;
        }
        self::$links = $rv;
    }

    /*
     * Get the regex for a link
     *
     * @since 0.7
     */
    protected static function get_kw_regex($link)
    {
        $keywords = self::get_keywords($link->ID);
        return sprintf('#(\b)(%s)(\b)#ui', implode('|', $keywords));
    }

    /*
     * fetch the clean and sanitied keywords
     *
     * @since 0.7
     */
    protected static function get_keywords($link_id)
    {
        $keywords = get_post_meta($link_id, self::get_key('keywords'), true);
        $kw_arr = explode(',', $keywords);
        $kw_arr = array_map('trim', $kw_arr);
        $kw_arr = array_map('preg_quote', $kw_arr);
        return $kw_arr;
    }

    /*
     * Get the link URL fro a keyword
     *
     * @since 0.7
     */
    protected static function get_link_url($link)
    {
        return get_post_meta($link->ID, self::get_key('url'), true);
    }

    /*
     * Get the maximum number of time a link can be replaced
     *
     * @since 0.7
     */
    protected static function get_link_max($link)
    {
        $meta = get_post_meta($link->ID, self::get_key('times'), true);
        return absint($meta) ? absint($meta) : 1;
    }

    /*
     * Loop through a an array of matches and create an associative array of 
     * key value pairs to use for str replacements
     *
     * @since 0.7
     */
    protected function gen_replacements($arr, $key, $start=0)
    {
        $rv = array();
        foreach($arr as $a)
        {
            $rv["<!--seo-auto-linker-{$key}-{$start}-->"] = $a;
            $start++;
        }
        return $rv;
    }

    /*
     * Wrapper around str_replace
     *
     * @since 0.7
     */
    protected static function replace($arr, $content)
    {
        return str_replace(
            array_values($arr),
            array_keys($arr),
            $content
        );
    }

    protected static function replace_bak($arr, $content)
    {
        return str_replace(
            array_keys($arr),
            array_values($arr),
            $content
        );
    }
}

SEO_Auto_Linker_Front::init();
