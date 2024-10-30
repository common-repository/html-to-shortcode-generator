<?php
#******************************************************************************
#                       HTML to Shortcode Generator v1.0
#
#	Author: avaib.com
#	http://www.avaib.com
#	Version: 1.0
#
#******************************************************************************


/*
 * Plugin Enabler Class
 */

class wp_plugin_enabler {

    // private property to hold post type
    private $post_type = false;
    // hold if any post is enabled already of not.
    static $any_post = false;
    //hold plugin id
    private $plugin_id = false;
    // hold fields of the plugin
    public $fields = array();
    //hold plugin assets path
    public $plugin_assets_path = '';
    //hold plugin keys
    public $keys = '';

    /*
     * Primary function to enable the plugin
     */

    public function htsg_enable($plugin) {

        $this->htsg_create_mainmenu();
        $meta = get_post_meta($plugin->ID);
        $post_type = str_replace(' ', '_', strtolower($meta['_wp_pluginname'][0]));
        $this->post_type = $post_type;
        $this->plugin_id = $plugin->ID;

        register_post_type('wp_' . $this->post_type, array(
            'labels' => array(
                'name' => __($meta['_wp_pluginname'][0], 'wp_' . $this->post_type),
                'singular_name' => __($meta['_wp_pluginname'][0], 'wp_' . $this->post_type)
            ),
            'public' => true,
            'menu_position' => 55,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'supports' => array('title'),
            'show_in_menu' => 'wp_plugins'
                )
        );
        add_action('add_meta_boxes', array($this, 'htsg_add_wp_plugins_metaboxes'));
        add_action('save_post', array($this, 'htsg_wp_save_plugin_fields'), 1, 2);
        $this->htsg_extractassets();
        add_shortcode($this->post_type, array($this, 'htsg_call_shortcode'));
        add_action('wp', array($this, 'htsg_wp_init'));
        add_action('admin_enqueue_scripts', array($this, 'htsg_load_wp_media_files'));


        add_action('admin_enqueue_scripts', array($this, 'htsg_load_custom_wp_admin_style'));
    }

    function htsg_load_custom_wp_admin_style($hook) {
        $post_type = get_post_type();
        if ($post_type == 'wp_plugin') {
            return;
        }


        $this_path = plugin_dir_url(__FILE__);
        $plugin_path = str_replace('class/', '', $this_path);
        $plugin_path = str_replace('class\\', '', $plugin_path);
        $path = $plugin_path . "/assets/";
        wp_register_style('htsg_wp_admin_css', $path . 'css/custom.css', false, '1.0.0');
        wp_enqueue_style('htsg_wp_admin_css');
        wp_enqueue_script('htsg_custom_script', $path . 'js/custom.js', false, null, true);
    }

    /*
     * this function is extracting the uploaded assets folder.
     */

    private function htsg_extractassets() {
        $current = get_post_meta($this->plugin_id, 'wp_plugin_assets_attachment', true);
        $zip = new ZipArchive;
        if (isset($current['file']))
            if ($zip->open($current['file']) === TRUE) {
                $this_path = plugin_dir_path(__FILE__);
                $plugin_path = str_replace('class/', '', $this_path);
                $plugin_path = str_replace('class\\', '', $plugin_path);
                $path = $plugin_path . "/assets/" . $this->post_type;
                $this->plugin_assets_path = plugins_url("", dirname(__FILE__)) . "/assets/" . $this->post_type;
                if (!file_exists($path))
                    mkdir($path);
                $zip->extractTo($path);
                $zip->close();
            }
    }

    /*
     * this function is checking if wordpress post content has any shortcode of this plugin then include it header and footer part.
     */

    public function htsg_wp_init() {
        global $post;
        $content = '';
        if (isset($post->post_content))
            $content = $post->post_content;
        $re = "/(\\[" . $this->post_type . "])/";
        $match = preg_match($re, $content);
        if ($match === 1) {
            $this->keys = get_post_meta($this->plugin_id);
            $this->htsg_enqueue();
            add_action('wp_head', array($this, 'htsg_generate_head'));
            add_action('wp_footer', array($this, 'htsg_generate_footer'));
        }
    }

    private function htsg_enqueue($keys) {
        $keys = $this->keys;
        $scripts = json_decode((string) $keys['wp_plugin_assets_scripts'][0]);

        $header_scripts = htsg_array_search_r('head', $scripts, 0);
        $num = 0;
        foreach ($header_scripts as $header_script) {
            wp_enqueue_script('htsg_script_' . $num++, $this->plugin_assets_path . "/" . $header_script, array(), null, false);
        }

        $styles = json_decode((string) $keys['wp_plugin_assets_styles'][0]);
        $header_styles = htsg_array_search_r('head', $styles, 0);
        $num = 0;
        foreach ($header_styles as $header_style) {
            wp_enqueue_style('htsg_style_' . $num++, $this->plugin_assets_path . '/' . $header_style);
        }



        $escripts = json_decode((string) $keys['wp_plugin_assets_external_scripts'][0]);
        $header_escripts = htsg_array_search_r('head', $escripts, 0);
        $num = 0;
        foreach ($header_escripts as $header_escript) {
            wp_enqueue_script('htsg_escript_' . $num++, $header_escript, array(), null, true);
        }

        $estyles = json_decode((string) $keys['wp_plugin_assets_external_styles'][0]);
        $header_estyles = htsg_array_search_r('head', $estyles, 0);
        $num = 0;
        foreach ($header_estyles as $header_estyle) {
            wp_enqueue_style('htsg_estyle_' . $num++, $header_estyle);
        }



        $scripts = json_decode((string) $keys['wp_plugin_assets_scripts'][0]);
        $header_scripts = htsg_array_search_r('foot', $scripts, 0);
        $num = 0;
        foreach ($header_scripts as $header_script) {
            wp_enqueue_script('htsg_script_' . $num++, $this->plugin_assets_path . "/" . $header_script, array(), null, true);
        }
        $styles = json_decode((string) $keys['wp_plugin_assets_styles'][0]);
        $header_styles = htsg_array_search_r('foot', $styles, 0);
        $num = 0;
        foreach ($header_styles as $header_style) {
            wp_enqueue_style('htsg_style_' . $num++, $this->plugin_assets_path . '/' . $header_style);
        }
        $escripts = json_decode((string) $keys['wp_plugin_assets_external_scripts'][0]);
        $header_escripts = htsg_array_search_r('foot', $escripts, 0);
        $num = 0;
        foreach ($header_escripts as $header_escript) {
            wp_enqueue_script('htsg_escript_' . $num++, $header_escript, array(), null, true);
        }
        $estyles = json_decode((string) $keys['wp_plugin_assets_external_styles'][0]);
        $header_estyles = htsg_array_search_r('foot', $estyles, 0);
        $num = 0;
        foreach ($header_estyles as $header_estyle) {
            wp_enqueue_style('htsg_estyle_' . $num++, $header_estyle);
        }
    }

    /*
     * This function generate footer.
     */

    public function htsg_generate_footer() {
        $keys = $this->keys;
        $foot_replace = '';
        $custom = json_decode((string) $keys['wp_plugin_assets_external_custom'][0]);
        $footer_customs = htsg_array_search_r('foot', $custom, 0);
        foreach ($footer_customs as $footer_custom) {
            $foot_replace.= $footer_custom;
        }
        echo $foot_replace;
    }

    /*
     * this function generate header
     */

    public function htsg_generate_head() {

        $keys = $this->keys;
        $head_replace = '';
        $custom = json_decode((string) $keys['wp_plugin_assets_external_custom'][0]);
        $header_customs = htsg_array_search_r('head', $custom, 0);
        foreach ($header_customs as $header_custom) {
            $head_replace.= $header_custom;
        }
        echo $head_replace;
    }

    /*
     * Shortcode function
     */

    public function htsg_call_shortcode() {
        $before = get_post_meta($this->plugin_id, '_wp_plugin_define_before', true);
        $after = get_post_meta($this->plugin_id, '_wp_plugin_define_after', true);
        $body1 = get_post_meta($this->plugin_id, '_wp_plugin_define_repeater', true);
        $args = array('posts_per_page' => -1, 'post_type' => 'wp_' . $this->post_type);
        $items = get_posts($args);
        echo  html_entity_decode($before,ENT_QUOTES);
        $body = $body1;
        foreach ($items as $item) {
            $keys = get_post_meta($item->ID);
            foreach ($keys as $key => $value) {
                $pattern = '/#--[a-z|A-Z]*--' . str_replace('_wp_plugin_', '', $key) . '--#/i';
                $body = preg_replace($pattern, $value[0], $body);
            }
            echo  html_entity_decode($body,ENT_QUOTES);
            $body = $body1;
        }
        echo  html_entity_decode($after,ENT_QUOTES);
    }

    /*
     * gets attribute for image fields define in the plugin
     */

    public function htsg_get_img_attributes($fields, $start) {
        $attr = array();
        for ($a = $start; $a < count($fields); $a++) {
            if (preg_match("/(#--imgalt--#)|(#--imgtitle--#)/", $fields[$a]) == 1) {
                $attr[] = $fields[$a];
            } else {
                return $attr;
            }
        }
        return $attr;
    }

    /*
     * add meta boxes to the item add page.
     */

    public function htsg_add_wp_plugins_metaboxes() {
        add_meta_box('wp_' . $this->post_type . '_data', 'Data', array($this, 'htsg_wp_plugin_metabox'), 'wp_' . $this->post_type, 'normal', 'default');
    }

    /*
     * generate available fields in the plugin added by the user
     */

    public function htsg_wp_generate_fields() {
        $fields_contents = get_post_meta($this->plugin_id, '_wp_plugin_define_repeater', true);
        $img = "/(#--img--[a-z|A-z|0-9| ]*--#)|(#--imgalt--#)|(#--imgtitle--#)/";
        $matches = array();
        preg_match_all($img, $fields_contents, $matches);
        $fields = $matches[0];
        $img_tags = array();
        $img_count = 0;
        for ($a = 0; $a < count($fields); $a++) {
            if (preg_match("/(#--img--[a-z|A-z|0-9| ]*--#)/", $fields[$a]) == 1) {
                $img_tags[$img_count][] = $fields[$a];
                $img_tags[$img_count][] = $this->htsg_get_img_attributes($fields, $a + 1);
                $img_count++;
            }
        }

        $txt = "/(#--txt--[a-z|A-z|0-9| ]*--#)/";
        $matches = array();
        preg_match_all($txt, $fields_contents, $matches);
        $txts = $matches[0];
        $txtarea = "/(#--txtarea--[a-z|A-z|0-9| ]*--#)/";
        $matches = array();
        preg_match_all($txtarea, $fields_contents, $matches);
        $txtareas = $matches[0];
        foreach ($img_tags as $img) {
            $field_name = ucfirst(str_replace(array('#--img--', '--#'), array('', ''), $img[0]));
            $input_name = "_wp_plugin_" . strtolower(str_replace(' ', "_", $field_name));
            $this->fields[] = $input_name;
            if (isset($img[1]))
                foreach ($img[1] as $attr) {
                    $attr_name = ucfirst(str_replace(array('#--img', '--#'), array('', ''), $attr));
                    $input_name = "_wp_plugin_" . strtolower(str_replace(' ', "_", $field_name . "_" . $attr_name));
                    $this->fields[] = $input_name;
                }
        }

        foreach ($txts as $field) {
            $field_name = ucfirst(str_replace(array('#--txt--', '--#'), '', $field));
            $input_name = "_wp_plugin_" . strtolower(str_replace(' ', "_", $field_name));
            $this->fields[] = $input_name;
        }
        foreach ($txtareas as $field) {
            $field_name = ucfirst(str_replace(array('#--txtarea--', '--#'), '', $field));
            $input_name = "_wp_plugin_" . strtolower(str_replace(' ', "_", $field_name));
            $this->fields[] = $input_name;
        }
    }

    /*
     * Metabox HTML outpout
     */

    public function htsg_wp_plugin_metabox() {
        global $post;
        $fields_contents = get_post_meta($this->plugin_id, '_wp_plugin_define_repeater', true);
        $img = "/(#--img--[a-z|A-z|0-9| ]*--#)|(#--imgalt--#)|(#--imgtitle--#)/";
        $matches = array();
        preg_match_all($img, $fields_contents, $matches);
        $fields = $matches[0];
        $img_tags = array();
        $img_count = 0;
        for ($a = 0; $a < count($fields); $a++) {
            if (preg_match("/(#--img--[a-z|A-z|0-9| ]*--#)/", $fields[$a]) == 1) {
                $img_tags[$img_count][] = $fields[$a];
                $img_tags[$img_count][] = $this->htsg_get_img_attributes($fields, $a + 1);
                $img_count++;
            }
        }

        $txt = "/(#--txt--[a-z|A-z|0-9| ]*--#)/";
        $matches = array();
        preg_match_all($txt, $fields_contents, $matches);
        $txts = $matches[0];
        $txtarea = "/(#--txtarea--[a-z|A-z|0-9| ]*--#)/";
        $matches = array();
        preg_match_all($txtarea, $fields_contents, $matches);
        $txtareas = $matches[0];


        wp_enqueue_script('jquery');
        wp_enqueue_media();



        foreach ($img_tags as $img) {
            $field_name = ucfirst(str_replace(array('#--img--', '--#'), array('', ''), $img[0]));
            $input_name = "_wp_plugin_" . strtolower(str_replace(' ', "_", $field_name));
            $this->fields[] = $input_name;
            $field_value = get_post_meta($post->ID, $input_name, true);
            ?>
            <div class="wp_field"><label for="<?php echo $input_name; ?>"><?php echo $field_name; ?> URL</label><input type="text" class="wp_input_fields" id="<?php echo $input_name; ?>" name="<?php echo $input_name; ?>" value="<?php echo $field_value; ?>"   /><input type="button" class=" upload-btn button-secondary"  value="Upload"></div>
            <?php
            if (isset($img[1]))
                foreach ($img[1] as $attr) {
                    $attr_name = ucfirst(str_replace(array('#--img', '--#'), array('', ''), $attr));
                    $input_name = "_wp_plugin_" . strtolower(str_replace(' ', "_", $field_name . "_" . $attr_name));
                    $this->fields[] = $input_name;
                    $field_value = get_post_meta($post->ID, $input_name, true);
                    ?>
                    <div class="wp_field"><label for="<?php echo $input_name; ?>"><?php echo $field_name . " " . $attr_name; ?></label><input type="text" class="wp_input_fields image_url" id="<?php echo $input_name; ?>" name="<?php echo $input_name; ?>" value="<?php echo $field_value; ?>"  /></div>
                    <?php
                }
        }

        foreach ($txts as $field) {
            $field_name = ucfirst(str_replace(array('#--txt--', '--#'), '', $field));
            $input_name = "_wp_plugin_" . strtolower(str_replace(' ', "_", $field_name));
            $this->fields[] = $input_name;
            $field_value = get_post_meta($post->ID, $input_name, true);
            ?>
            <div class="wp_field"><label for="<?php echo $input_name; ?>"><?php echo $field_name; ?></label><input type="text" class="wp_input_fields" id="<?php echo $input_name; ?>" name="<?php echo $input_name; ?>" value="<?php echo $field_value; ?>"  /></div>
            <?php
        }
        foreach ($txtareas as $field) {
            $field_name = ucfirst(str_replace(array('#--txtarea--', '--#'), '', $field));
            $input_name = "_wp_plugin_" . strtolower(str_replace(' ', "_", $field_name));
            $this->fields[] = $input_name;
            $field_value = get_post_meta($post->ID, $input_name, true);
            ?>
            <div class="wp_field"><label for="<?php echo $input_name; ?>"><?php echo $field_name; ?></label><textarea   class="wp_input_fields" id="<?php echo $input_name; ?>" name="<?php echo $input_name; ?>" ><?php echo $field_value; ?></textarea></div>
            <?php
        }
    }

    function htsg_load_wp_media_files() {
        wp_enqueue_media();
    }

    /*
     * Create main menu
     */

    private function htsg_create_mainmenu() {
        if (!wp_plugin_enabler::$any_post) {
            add_action('admin_menu', array($this, 'htsg_mainmenu'), 1000);
            wp_plugin_enabler::$any_post = true;
        }
    }

    /*
     * main menu function
     */

    public function htsg_mainmenu() {
        add_menu_page('WP Plugins', 'WP Plugins', 'manage_options', 'wp_plugins', '', '', 30);
    }

    /*
     * function to save fields in the database
     */

    public function htsg_wp_save_plugin_fields($post_id, $post) {
        if (!$_POST)
            return;
        if ($post->post_type != 'wp_' . $this->post_type)
            return;
        global $wpdb;

        $this->htsg_wp_generate_fields();
// Is the user allowed to edit the post or page?
        if (!current_user_can('edit_post', $post->ID))
            return $post->ID;




        foreach ($this->fields as $key) {
            if ($post->post_type == 'revision')
                return; // Don't store custom data twice
            if (isset($_POST[$key]))
                update_post_meta($post->ID, $key, sanitize_text_field($_POST[$key]));


            if (!$_POST[$key])
                delete_post_meta($post->ID, $key); // Delete if blank
        }
    }

}
