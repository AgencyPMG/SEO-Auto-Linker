<?php
class SEO_Auto_Linker_Post_Type extends SEO_Auto_Linker_Base
{
    /*
     * Nonce action
     */
    const NONCE = 'seoal_post_nonce';

    /*
     * Sets up all the actions and filters
     *
     * @uses add_action
     * @uses add_filter
     * @since 0.7
     */
    public static function init()
    {
        // register post type
        add_action(
            'init',
            array(get_class(), 'register')
        );

        // add our meta boxes
        add_action(
            'add_meta_boxes_' . self::POST_TYPE,
            array(get_class(), 'meta_boxes'),
            30
        );

        add_action(
            'save_post',
            array(get_class(), 'save')
        );

        add_action(
            'dbx_post_sidebar',
            array(get_class(), 'nonce_field')
        );
    }

    /*
     * Hooked into `init`.  Registers the post type
     *
     * @uses register_post_type
     * @since 0.7
     */
    public static function register()
    {
        $labels = array(
            'name'              => __('Automatic Links', 'seoal'),
            'singular_name'     => __('Automatic Link', 'seoal'),
            'add_new'           => __('Add New Link', 'seoal'),
            'all_items'         => __('All Links', 'seoal'),
            'add_new_item'      => __('Add New Link', 'seoal'),
            'edit_item'         => __('Edit Link','seoal'),
            'new_item'          => __('New Link', 'seoal'),
            'search_items'      => __('Search Links', 'seoal'),
            'not_found'         => __('No Links Found', 'seoal'),
            'not_found_in_trash' => __('No Links in the Trash', 'seoal'),
            'menu_name'         => __('SEO Auto Linker', 'seoal')
        );

        $args = array(
            'label'         => __('Automatic Links', 'seoal'),
            'labels'        => $labels,
            'description'   => __('A container for SEO Auto Linker', 'seoal'),
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'menu_position' => 110,
            'supports'      => array('title')
        );

        register_post_type(
            self::POST_TYPE,
            $args
        );
    }

    /*
     * Adds the SEO Auto Linker meta boxes.  If WordPress seo is installed,
     * this will remove that metabox from the SEO Auto Linker post type
     *
     * @uses add_meta_box
     * @uses remove_meta_box
     * @since 0.7
     */
    public static function meta_boxes($post)
    {
        // remove wpseo
        if(defined('WPSEO_BASENAME'))
        {
            remove_meta_box(
                'wpseo_meta',
                self::POST_TYPE,
                'normal'
            );
        }

        // remove the submit div, we'll roll our own
        remove_meta_box(
            'submitdiv',
            self::POST_TYPE,
            'side'
        );

        // add the submit div
        add_meta_box(
            'seoal-submitdiv',
            __('Save', 'seoal'),
            array(get_class(), 'submit_cb'),
            self::POST_TYPE,
            'side',
            'high'
        );

        // keywords & url box
        add_meta_box(
            'seoal-keywords',
            __('Keywords & URL', 'seoal'),
            array(get_class(), 'keyword_cb'),
            self::POST_TYPE,
            'normal',
            'high'
        );

        // blacklist box
        add_meta_box(
            'seoal-blacklist',
            __('Blacklist', 'seoal'),
            array(get_class(), 'blacklist_cb'),
            self::POST_TYPE,
            'normal',
            'low'
        );

        // post type box
        add_meta_box(
            'seoal-types',
            __('Allowed Post Types', 'seoal'),
            array(get_class(), 'type_cb'),
            self::POST_TYPE,
            'side',
            'low'
        );

        self::setup_meta($post);
    }

    /*
     * Save ALL the DATA
     *
     * @uses update_post_meta
     * @uses delete_post_meta
     * @uses wp_verify_nonce
     * @uses current_user_can
     * @since 0.7
     */
    public static function save($post_id)
    {
        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if(!current_user_can('manage_options')) return;
        if(!isset($_POST[self::NONCE]) || !wp_verify_nonce($_POST[self::NONCE], self::NONCE)) 
            return;

        $map = array(
            'keywords' => array('strip_tags', 'esc_attr'),
            'url'      => array('esc_url'),
            'times'    => array('absint')
        );
        foreach($map as $key => $escapers)
        {
            $key = self::get_key($key);
            if(isset($_POST[$key]) && $_POST[$key])
            {
                $val = $_POST[$key];
                foreach($escapers as $e)
                    $val = call_user_func($e, $val);
                update_post_meta($post_id, $key, $val);
            }
            else
            {
                delete_post_meta($post_id, $key);
            }
        }

        $bl = self::get_key('blacklist');
        if(isset($_POST[$bl]) && $_POST[$bl])
        {
            $blacklist = preg_split('/\r\n|\r|\n/', $_POST[$bl]);
            $blacklist = array_map('esc_url', $blacklist);
            update_post_meta($post_id, $bl, $blacklist);
        }
        else
        {
            delete_post_meta($post_id, $bl);
        }

        foreach(get_post_types() as $pt)
        {
            $key = self::get_key("type_{$pt}");
            $val = isset($_POST[$key]) && $_POST[$key] ? 'on' : 'off';
            update_post_meta($post_id, $key, $val);
        }
    }

    /*
     * Spits out a nonce field for use with our meta boxes
     *
     * @uses wp_nonce_field
     * @since 0.7
     */
    public static function nonce_field()
    {
        wp_nonce_field(
            self::NONCE,
            self::NONCE,
            false
        );
    }

    /********** Meta Box Callbacks **********/

    /*
     * Callback for the submitdiv
     *
     * @since 0.7
     */
    public static function submit_cb($post)
    {
        $typeobj = get_post_type_object($post->post_type);
        $can_edit = current_user_can('manage_options');
        if(!$can_edit)
        {
            echo '<p>' . __('You must be an admin to edit links', 'seoal') . '</p>';
            return;
        }
        ?>
        <div id="post-status-select">
            <input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr(('auto-draft' == $post->post_status) ? 'draft' : $post->post_status); ?>" />
            <label for="post_status"><?php _e('Status: ', 'seoal'); ?></label>
            <select name='post_status' id='post_status' tabindex='4'>
                <option<?php selected($post->post_status, 'publish'); ?> value='publish'><?php _e('Enabled', 'seoal') ?></option>
                <option<?php selected($post->post_status, 'draft'); ?> value='draft'><?php _e('Disabled', 'seoal') ?></option>
            </select>
        </div>
        <p id="major-publishing-action">
            <a class="button-secondary submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php _e('Delete', 'seoal'); ?></a>
            <input type="submit" name="save" class="button-primary" value="<?php esc_attr_e('Save', 'seoal'); ?>" />
        </p>
        <?php
    }

    /*
     * Callback for the keywords, link url, and allowed links meta box
     *
     * @since 0.7
     */
    public static function keyword_cb($post)
    {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="<?php self::key('keywords'); ?>">
                        <?php _e('Keywords', 'seoal'); ?>
                    </label>
                </th>
                <td>
                    <textarea class="widefat" name="<?php self::key('keywords'); ?>" id="<?php self::key('keywords'); ?>"><?php self::meta('keywords', 'textarea'); ?></textarea>
                    <p class="description">
                        <?php _e('Comman separated. These are the terms you want to link.', 'seoal'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="<?php self::key('url'); ?>">
                        <?php _e('URL', 'seoal'); ?>
                    <label>
                </th>
                <td>
                    <input type="text" class="widefat" name="<?php self::key('url'); ?>" id="<?php self::key('url'); ?>" value="<?php self::meta('url'); ?>" />
                    <p class="description">
                        <?php _e('The url to which you want to link.', 'seoal'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="<?php self::key('times'); ?>">
                        <?php _e('Links per Page', 'seoal'); ?>
                    <label>
                </th>
                <td>
                    <input type="text" class="widefat" name="<?php self::key('times'); ?>" id="<?php self::key('times'); ?>" value="<?php self::meta('times'); ?>" />
                    <p class="description">
                        <?php 
                        _e('The number of times per page (or post type) you want' .
                        'the above keyowrds to link to the url', 'seoal');
                        ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    /*
     * Callback for the blacklist meta box
     *
     * @since 0.7
     */
    public static function blacklist_cb($post)
    {
        $blacklist = self::get_meta('blacklist', array());
        $blacklist = maybe_unserialize($blacklist);
        if($blacklist) $blacklist = array_map('esc_url', $blacklist);
        // I have a hunch this is bad for windows machines?
        $blacklist = implode("\n", $blacklist);
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="<?php self::key('blacklist'); ?>">
                        <?php _e('Blacklist', 'seoal'); ?>
                    </label>
                </th>
                <td>
                    <textarea class="widefat" id="<?php self::key('blacklist'); ?>" name="<?php self::key('blacklist'); ?>" rows="15"><?php echo $blacklist; ?></textarea>
                    <p class="description">
                        <?php _e("URLs on which you don't want to have this link.", 'seoal'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    /*
     * Callback for the post type meta box
     *
     * @since 0.7
     */
    public static function type_cb($post)
    {
        foreach(get_post_types(array('public' => true)) as $post_type): 
        $typeobj = get_post_type_object($post_type);
        ?>
        <label for="<?php self::key("type_{$post_type}"); ?>">
            <input type="checkbox" name="<?php self::key("type_{$post_type}"); ?>" id="<?php self::key("type_{$post_type}"); ?>" <?php checked(self::get_meta("type_{$post_type}"), 'on'); ?> />
            <?php echo esc_attr($typeobj->label); ?>
        </label><br />
        <?php
        endforeach;
    }
} // end class

SEO_Auto_Linker_Post_Type::init();
