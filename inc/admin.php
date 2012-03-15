<?php
class pmgSeoAutoLinkerAdmin
{
    protected $setting = 'pmg_autolinker_options';
    
    function __construct()
    {
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
        add_action( 'admin_init', array( &$this, 'register_setting' ) );
        add_action( 'edit_post', array( &$this, 'flush_cache' ) );
    }
    
    function admin_menu()
    {
        $page = add_options_page( __( 'SEO Auto Linker' ), __( 'SEO Auto Linker' ), 'manage_options', 'pmg-autolinker', array( &$this, 'menu_page' ) );
        
        add_action( "admin_print_scripts-{$page}", array( &$this, 'scripts' ) );
        add_action( "admin_print_styles-{$page}", array( &$this, 'styles' ) );
    }
    
    function menu_page()
    {   
        $opts = get_option( $this->setting );
        
        $keywords = isset( $opts['kw'] ) ? $opts['kw'] : array();
        $urls = isset( $opts['url'] ) ? $opts['url'] : array();
        $max_links = isset( $opts['max'] ) ? $opts['max'] : array();
        $types = isset( $opts['types'] ) ? $opts['types'] : array();
        $blacklist = isset( $opts['blacklist'] ) ? $opts['blacklist'] : array();
        
        $site_wide_blacklist = isset( $opts['site_wide_blacklist'] ) ? $opts['site_wide_blacklist'] : '';
        ?>
        <div class="wrap">
            <?php screen_icon( 'tools' ); ?>
            <h2><?php _e( 'SEO Auto Linker Settings' ); ?></h2>
            <form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
                <?php settings_fields( $this->setting ); ?>
                <table id="links-definitions" class="widefat">
                    <thead>
                        <tr>
                            <th class="pmg-kw">
                                <?php _e( 'Comma Separated Keywords' ); ?>
                            </th>
                            <th class="pmg-url">
                                <?php _e( 'URL' ); ?>
                            </th>
                            <th>
                                <?php _e( 'Links per Page' ); ?>
                            </th>
                            <th>
                                <?php _e( 'Post Types' ) ; ?>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    
                    <tbody id="link-definition-body">
                        <?php foreach( $keywords as $index => $kw ): ?>
                            <tr class="links-entry">
                                <td>
                                    <textarea name="<?php echo $this->setting; ?>[kw][<?php echo $index; ?>]"><?php echo esc_attr( $kw ); ?></textarea>
                                </td>
                                
                                <td>
                                    <label for="pmg-linksto-<?php echo $index; ?>"><?php _e( 'Link to...' ); ?></label>
                                    <input type="text" name="<?php echo $this->setting; ?>[url][<?php echo $index; ?>]" id="pmg-linksto-<?php echo $index; ?>" class="pmg-link-text" value="<?php echo $this->get_value( $index, $urls ); ?>" />
                                    <br clear="both" />
                                    <label for="pgm-blacklist-<?php echo $index; ?>"><?php _e( 'Comma Separated Blacklist URLs...' ); ?></label>
                                    <textarea id="pgm-blacklist-<?php echo $index; ?>" name="<?php echo $this->setting; ?>[blacklist][<?php echo $index; ?>]"><?php echo $this->get_value( $index, $blacklist ); ?></textarea>
                                </td>
                                
                                <td>
                                    <?php _e( 'Up to ' ); ?>
                                    <select name="<?php echo $this->setting; ?>[max][<?php echo $index; ?>]">
                                        <?php
                                            foreach( range( 1, 5 ) as $num )
                                                echo '<option value="' . $num . '" ' . selected( $num, $this->get_value( $index, $max_links ), false ) . '">' . $num . '</option>';
                                        ?>
                                    </select>
                                    <?php _e( ' times on each...' ); ?>
                                </td>
                                <td>
                                    <select name="<?php echo $this->setting; ?>[types][<?php echo $index; ?>]">
                                        <?php
                                            foreach( get_post_types() as $type )
                                            {
                                                if( in_array( $type, array( 'nav_menu_item', 'revision', 'attachment' ) ) ) continue;
                                                $typeobj = get_post_type_object( $type );
                                                $name = isset( $typeobj->labels->singular_name ) ? $typeobj->labels->singular_name : $typeobj->label;
                                                echo '<option value="' . $type . '" ' . selected( $type, $this->get_value( $index, $types ), false ) . '>' . esc_attr( $name ) . '</option>';
                                            }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <a href="javascript:void(null)" class="pmg-delete-row" title="delete"><img src="<?php echo SEOAL_URL; ?>images/delete.png" alt="delete" title="delete" width="25" height="25" /></a>
                                </td>
                            </tr>
                        
                        <?php endforeach; ?>
                    
                        <tr class="links-entry new-link">
                            <td>
                                <textarea name="<?php echo $this->setting; ?>[kw][]"></textarea>
                            </td>
                            <td>
                                <label for="pmg-linksto"><?php _e( 'Link to...' ); ?></label>
                                <input type="text" name="<?php echo $this->setting; ?>[url][]" id="pmg-linksto" class="pmg-link-text" />
                                <label for="pgm-blacklist"><?php _e( 'Comma Separated Blacklist URLs…' ); ?></label>
                                    <textarea id="pgm-blacklist" name="<?php echo $this->setting; ?>[blacklist][]"></textarea>
                            </td>
                            <td>
                                <?php _e( 'Up to ' ); ?>
                                <select name="<?php echo $this->setting; ?>[max][]">
                                    <?php
                                        foreach( range( 1, 5 ) as $num )
                                            echo '<option value="' . $num . '">' . $num . '</option>';
                                    ?>
                                </select>
                                <?php _e( ' times on each... ' ); ?>
                            </td>
                            <td>
                                <select name="<?php echo $this->setting; ?>[types][]">
                                <?php
                                    foreach( get_post_types() as $type )
                                    {
                                        if( in_array( $type, array( 'nav_menu_item', 'revision', 'attachment' ) ) ) continue;
                                        $typeobj = get_post_type_object( $type );
                                        $name = isset( $typeobj->labels->singular_name ) ? $typeobj->labels->singular_name : $typeobj->label;
                                        echo '<option value="' . $type . '">' . esc_attr( $name ) . '</option>';
                                    }
                                ?>
                                </select>
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <th colspan="4">
                            <a href="javascript:void(null)" id="pmg-add-more-rows" class="button-secondary"><?php _e( 'Add More Rows' ); ?></a>
                        </th>
                    </tfoot>
                </table>
                <table id="site-wide-blacklist" class="widefat">
                    <thead>
                        <tr>
                            <th>
                                <?php _e( 'Site Wide Comma Separated Blacklist URLs...' ); ?>
                            </th>
                        </tr>
                    </thead>
                    
                    <tbody id="blacklist-body">
                        <tr class="links-entry">
                            <td>
                                <textarea id="pgm-site-wide-blacklist" name="<?php echo $this->setting; ?>[site_wide_blacklist]"><?php echo esc_attr($site_wide_blacklist); ?></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <p>
                    <input type="submit" class="button-primary" value="<?php _e( 'Save Settings' ); ?>" />
                </p>
            </form>
        </div>
        <?php
    }
    
    function register_setting()
    {
        register_setting( $this->setting, $this->setting, array( &$this, 'clean_settings' ) );
    }
    
    function clean_settings( $in )
    {
        $out = array();
        $out['site_wide_blacklist'] = esc_attr($in['site_wide_blacklist']);
        if( isset( $in['kw'] ) ):
            foreach( $in['kw'] as $index => $kw )
            {
                if( ! $kw ) continue;
                $out['kw'][$index] = esc_attr( $kw );
                $out['url'][$index] = isset( $in['url'][$index] ) && $in['url'][$index] ? esc_url( $in['url'][$index] ) : esc_url( home_url() );
                $out['max'][$index] = isset( $in['max'][$index] ) ? absint( $in['max'][$index] ) : 1;
                $out['types'][$index] = isset( $in['types'][$index] ) ? esc_attr( $in['types'][$index] ) : 'post';
                $out['blacklist'][$index] = esc_attr( $in['blacklist'][$index] );
            }
        endif;
        return $out;
    }
    
    function flush_cache( $post_id )
    {
        wp_cache_delete( 'autolinker_content_' . $post_id );
    }
    
    function styles()
    {   
        wp_enqueue_style( 'pmg-auto-linker', SEOAL_URL . 'css/seo-auto-linker.css', array(), NULL, 'all' );
    }
    
    function scripts()
    {
        wp_enqueue_script( 'pmg-auto-linkers', SEOAL_URL . 'js/seo-auto-linker.js', array( 'jquery' ), NULL, true );
    }
    
    function get_value( $index, $array )
    {
        if( isset( $array[$index] ) ) return esc_attr( $array[$index] );
    }
} // end class

new pmgSeoAutoLinkerAdmin();
