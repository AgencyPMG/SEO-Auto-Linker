<?php
class pmgSeoAutoLinkerFront
{
    function __construct()
    {
        add_filter( 'the_content', array( &$this, 'content' ), 1, 1 );
    }
    
    function content( $content )
    {
        if( ! is_singular() ) return $content;
        global $post;
        
        // set up some more options
        $opts = get_option( 'pmg_autolinker_options' );
        
        $permalink = get_permalink( $post );
        $site_wide_exclude_urls = isset( $opts['site_wide_blackist'] ) ?
                        explode( ',', $opts['site_wide_blacklist'] ) : array();
        if( in_array( $permalink, $site_wide_exclude_urls ) )
        {
            return $content;
        }
        
        $kws = isset( $opts['kw'] ) ? (array) $opts['kw'] : array();
        if( empty( $kws ) ) return $content;
        
        // Find all of our <h> tags in the content and replace them with something
        preg_match_all( '/<h[1-6][^>]*>.+?<\/h[1-6]>/iu', $content, $headers );
        if( $headers[0] )
        {
            $headers_replacements = array();
            $counter = 0;
            foreach( $headers[0] as $h )
            {
                $headers_replacements["<!--seo-auto-links-header-{$counter}--!>"] = $h;
                $counter++;
            }
            $filtered_content = str_replace(
                array_values( $headers_replacements ),
                array_keys( $headers_replacements ),
                $content
            );
        }
        else
        {
            $filtered_content = $content;
        }

        $sc_counter = 0;
        $sc_replacements = array();
        $sc_regex = get_shortcode_regex();
        preg_match_all( 
            '/' . $sc_regex . '/iu',
            $filtered_content,
            $shortcodes
        );
        if( ! empty( $shortcodes[0] )  )
        {
            foreach( $shortcodes[0] as $sc )
            {
                $sc_replacements["<!--seo-auto-links-sc-{$sc_counter}-->"]= $sc;
                $sc_counter++;
            }
            $filtered_content = str_replace(
                array_values( $sc_replacements ),
                array_keys( $sc_replacements ),
                $filtered_content
            );
        }
        
        // Strip out out images, inputs and stuff which may have text
        // text we shouldn't overwrite
        $other_counter = 0;
        $other_replacements = array();
        preg_match_all( '/<(img|input)(.*?) \/?>/iu', $filtered_content, $others );
        if( $others[0] )
        {
            foreach( $others[0] as $i )
            {
                $other_replacements["<!--seo-auto-links-others-{$other_counter}-->"] = $i;
                $other_counter++;
            }
            $filtered_content = str_replace(
                array_values( $other_replacements ),
                array_keys( $other_replacements ), 
                $filtered_content 
            );
        }

        $link_counter = 0;
        $links_replacements = array();
        foreach( $kws as $index => $kw )
        {
            $exclude_urls = isset( $opts['blacklist'][$index] ) ? 
                            explode(',', $opts['blacklist'][$index]) : array();
            if( in_array( $permalink, $exclude_urls ) ) continue;

            $nope = isset( $opts['types'][$index] ) && $post->post_type != $opts['types'][$index] ? true : false;
            if( $nope ) continue;
            
            $url = isset( $opts['url'][$index] ) ? $opts['url'][$index] : false;
            if( ! $url || $url == $permalink ) continue;
            
            $max = isset( $opts['max'][$index] ) ? $opts['max'][$index] : 1;
            
            // Find all the links in the content so we don't overwrite them or get weird stuff
            preg_match_all( '/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/iu', $filtered_content, $links );
            if( $links[0] )
            {
                $temp_links = array();
                foreach( $links[0] as $l )
                {
                    $temp_links["<!--seo-auto-links-link-{$link_counter}--!>"] = $l;
                    $link_counter++;
                }
                $filtered_content = str_replace( 
                    array_values( $temp_links ),
                    array_keys( $temp_links ),
                    $filtered_content
                );
                $links_replacements = array_merge( $links_replacements, $temp_links );
            }
            
            // Finally! add our links via preg_replace
            $regex = implode( 
                '|', 
                array_map( 'esc_attr', array_map( 'trim', explode( ',', $kw ) ) )
            );
            $filtered_content = preg_replace( 
                '/(\b)(' . $regex . ')(\b)/iu',
                '$1<a href="' . esc_url( $url ) . '" title="$2">$2</a>$3',
                $filtered_content,
                absint( $max )
            );
        }
        
        // Put the original <h> tags back in
        if( $headers[0] )
        {
            $filtered_content = str_replace(
                array_keys( $headers_replacements ),
                array_values( $headers_replacements ),
                $filtered_content
            );
        }
        
        // Put links back in
        if( ! empty( $links_replacements ) )
        {
            $filtered_content = str_replace(
                array_keys( $links_replacements ),
                array_values( $links_replacements ), 
                $filtered_content
            );
        }

        if( ! empty( $sc_replacements ) )
        {
            $filtered_content = str_replace(
                array_keys( $sc_replacements ),
                array_values( $sc_replacements ),
                $filtered_content
            );
        }
        
        // Put other stuff back in
        if( $others[0] )
        {
            $filtered_content = str_replace(
                array_keys( $other_replacements ),
                array_values( $other_replacements ),
                $filtered_content
            );
        }
        
        $filtered_content = apply_filters(
            'pmg_seo_auto_linker_content',
            $filtered_content,
            $content
        );
        
        return $filtered_content;
    }
} // end class;

new pmgSeoAutoLinkerFront();
