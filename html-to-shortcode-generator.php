<?php
/*

  Plugin Name: HTML to Shortcode Generator

  Plugin URI: http://www.avaib.com

  Description: HTML to Shortcode Generator for WordPress

  Author: Avaib.com

  Version: 1.0

  Release Date: 01 June 2016

  Author URI: http://www.avaib.com

 */




/*
 * Includes Plugin Enabler Class File.
 */
require_once( plugin_dir_path(__FILE__) . 'class/plugin-enabler.php' );

/*
 * Plugin Initialization action
 * 
 */

function htsg_myplugin_activate() {
    $args = array('post_type' => 'wp_plugin');
    $wp_plugins = get_posts($args);
    $plugins = current($wp_plugins);
    if (empty($plugins)) {
        wp_insert_post(
                array(
                    'post_status' => 'publish',
                    'post_type' => 'wp_plugin',
                    'post_title' => 'Untitled Plugin'
                )
        );
    }
}


 function htsg_load_compose_wp_admin_style($hook) {


        $this_path = plugin_dir_url(__FILE__);
        $plugin_path = str_replace('class/', '', $this_path);
        $plugin_path = str_replace('class\\', '', $plugin_path);
        $path = $plugin_path . "/assets/";
        wp_register_style('htsg_wp_admin_compose_css', $path . 'css/compose.css', false, '1.0.0');
        wp_enqueue_style('htsg_wp_admin_compose_css');
        wp_enqueue_script('htsg_compose_script', $path . 'js/compose.js', false, null, true);
    }

register_activation_hook(__FILE__, 'htsg_myplugin_activate');



add_action('init', 'htsg_create_wp_plugin_post_type', 1000);
/*
 * Plugin Initialization Function
 */

function htsg_create_wp_plugin_post_type() {
    add_action('admin_enqueue_scripts', 'htsg_load_compose_wp_admin_style');
    register_post_type('wp_plugin', array(
        'labels' => array(
            'name' => __('HTML Shortcode Generator', "wp_plugin_builder"),
            'singular_name' => __('Shortcode', "wp_plugin_builder"),
            'add_new' => __('Compose Shortcode', "wp_plugin_builder"),
            'edit_item' => __('Edit Shortcode', "wp_plugin_builder"),
            'add_new_item' => __('Compose Shortcode', "wp_plugin_builder"),
            'all_items' => __('All Shortcodes', "wp_plugin_builder"),
        ),
        'public' => true,
        'menu_position' => 65,
        'has_archive' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'menu_icon' => 'dashicons-admin-plugins',
        'supports' => array('title'),
        'register_meta_box_cb' => 'htsg_add_wp_plugin_metaboxes',
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
        ),
        'map_meta_cap' => true,
            )
    );
    add_action('post_submitbox_start', 'htsg_enable_button');

    $args = array('post_type' => 'wp_plugin', 'posts_per_page' => 1, 'post_status' => 'publish', 'meta_query' => array(
            array(
                'key' => '_wp_pluginenable',
                'value' => '1',
            )
    ));
    $wp_plugins = get_posts($args);

    $plugins = current($wp_plugins);
    if (!empty($plugins)) {

        $wp_plugin = new wp_plugin_enabler();
        $wp_plugin->htsg_enable($plugins);
    }
    add_action('wp_ajax_delete_asset', 'htsg_delete_assets');
    add_action('post_edit_form_tag', 'htsg_update_edit_form');
    add_filter('post_updated_messages', 'htsg_wp_plugin_updated_messages');
    add_action('save_post', 'htsg_wp_save_wp_plugin_information', 1, 2); // save the custom fields
}

/*
 * Enable Button Output
 */

function htsg_enable_button() {
    global $post;
    $title = get_post_meta($post->ID, '_wp_pluginname', true);
    if ($title != '') {

        $post_type = "wppm_preview_" . str_replace(" ", "_", strtolower($title));
        $preview_backend = post_type_exists($post_type) ? "Uninstall" : 'Install';
        $install = post_type_exists($post_type) ? 0 : 1;
        $plugin_enable = get_post_meta($post->ID, '_wp_pluginenable', true);
        ?>
        <div class="enable">
            <input type="checkbox" name="_wp_pluginenable" value="1"  id="enable" style="margin-top:1px;" <?php
            if (isset($plugin_enable) && $plugin_enable == 1) {
                echo 'checked';
            }
            ?> /><label for="enable">Enable</label>
        </div>
        <?php
    }
}

/*
 * Metaboxes of post type wp_plugin
 */

function htsg_add_wp_plugin_metaboxes() {
    add_meta_box('wp_plugin_information', 'Plugin Information', 'htsg_wp_plugin_information', 'wp_plugin', 'normal', 'default');
    add_meta_box('wp_plugin_define', 'PLUGIN HTML', 'htsg_wp_plugin_define', 'wp_plugin', 'normal', 'default');
    add_meta_box('wp_plugin_assets_attachment', 'Assets  <div class="tooltip"><span class="askTooltip">?</span><span class="tooltiptext" style="left: -178px;width: 350px;">Assets are files (CSS, JS, etc.) that you need for your plugin. There are options to place these files within the HTML above or in the head or the footer of the page.<img src="' . plugin_dir_url(__FILE__) . '/assets/img/arrow.png" style="top: 86px;"></span></div>', 'htsg_wp_plugin_assets_attachment', 'wp_plugin', 'normal', 'default');
}

/*
 * Delet assets file function
 */

function htsg_delete_assets() {
    $id = sanitize_text_field($_REQUEST['id']);
    $path = get_post_meta($id, 'wp_plugin_assets_attachment', true);

    unlink($path['file']);


    delete_post_meta($id, 'wp_plugin_assets_attachment');
    header('Location: ' . admin_url() . '/post.php?post=' . $id . '&action=edit');
    die();
}

/*
 * Assets Section Output
 */

function htsg_wp_plugin_assets_attachment() {
    wp_nonce_field(plugin_basename(__FILE__), 'wp_plugin_assets_attachment_nonce');
    global $post;
    $current = get_post_meta($post->ID, 'wp_plugin_assets_attachment', true);
    ?>
    <p class="description">
        <?php
        if (isset($current['url'])) {
            ?>

            Current Asset File URL. <a href="<?php echo $current['url'] ?>">Download</a>&nbsp;<a href="<?php echo admin_url() . "admin-ajax.php?action=delete_asset&id=" . $post->ID; ?>" onclick="if (confirm('Warning: all files will be deleted and all settings related to your assets will be lost. Are you sure you want to proceed?'))
                        return true;
                    else
                        return false;">Delete</a><br>Upload your assets folder in zip here. (Uploading a new asset file will delete your old file and all settings related to your old asset files.)

            <?php
        }
        ?>
    </p>
    <input type="file" id="wp_plugin_assets_attachment" name="wp_plugin_assets_attachment" value="" size="25">

    <?php
    if ($current) {
        ?>
      

        <table class="wp-list-table widefat striped posts">


            <tbody >

                <?php
                $count = 0;
                get_post_meta($post->ID, 'wp_plugin_assets_scripts', true);


                $scripts = json_decode((string) get_post_meta($post->ID, 'wp_plugin_assets_scripts', true));
                $styles = json_decode((string) get_post_meta($post->ID, 'wp_plugin_assets_styles', true));
                $images = json_decode((string) get_post_meta($post->ID, 'wp_plugin_assets_images', true));
                $others = json_decode((string) get_post_meta($post->ID, 'wp_plugin_assets_others', true));
                ?>

                <?php
                if (empty($scripts)) {
                    
                } else {
                    ?>
                    <tr><th colspan="2" class="cat_head">Scripts</th></tr>
                    <?php
                    foreach ($scripts as $file) {
                        ?>

                        <tr  class="hentry">

                            <td class="title column-title has-row-actions column-primary page-title" data-colname="File Path">
                                <strong>
                                    <a class="row-title" href="#" title=""><?php echo $file[0]; ?></a>
                                </strong>
                            </td>
                            <td class="date column-date" data-colname="Dateinpu"><div class="radiobtn"><input type="radio" id="file_scripts_no_<?php echo $count; ?>" name="scriptsinclude[<?php echo $count; ?>]" <?php echo htsg_value("no", $file[1], "checked"); ?> /><label title="The code will not be included anywhere (in the head or footer). This option is useful when this asset is being used by another asset and does not require to be explicitly included anywhere in the page" for="file_scripts_no_<?php echo $count; ?>">&nbsp;Don't Include</label></div>&nbsp;<div class="radiobtn"><input type="radio" id="file_scripts_head_<?php echo $count; ?>" name="scriptsinclude[<?php echo $count; ?>]" <?php echo htsg_value("head", $file[1], "checked"); ?> /><label for="file_scripts_head_<?php echo $count; ?>">&nbsp;Head</label></div><div class="radiobtn"><input type="radio" id="file_scripts_foot_<?php echo $count; ?>" name="scriptsinclude[<?php echo $count; ?>]" <?php echo htsg_value("foot", $file[1], "checked"); ?>/><label for="file_scripts_foot_<?php echo $count; ?>">&nbsp;Footer</label></div> <div class="radiobtn"><input type="radio" id="file_scripts_html_<?php echo $count; ?>" name="scriptsinclude[<?php echo $count; ?>]" <?php echo htsg_value("html", $file[1], "checked"); ?> onchange="prompt('URL', '<?php echo $file[0]; ?>')" /><label for="file_scripts_html_<?php echo $count++; ?>">&nbsp;HTML Code</label></div>  </td>
                        </tr>
                        <?php
                    }
                }
                $count = 0;
                ?>



                <?php
                if (empty($styles)) {
                    
                } else {
                    ?>
                    <tr><th colspan="2" class="cat_head">Styles</th></tr>
                    <?php
                    foreach ($styles as $file) {
                        ?>

                        <tr  class="hentry">

                            <td class="title column-title has-row-actions column-primary page-title" data-colname="File Path">
                                <strong>
                                    <a class="row-title" href="#" title=""><?php echo $file[0]; ?></a>
                                </strong>
                            </td>
                            <td class="date column-date" data-colname="Dateinpu"><div class="radiobtn"><input type="radio" id="file_styles_no_<?php echo $count; ?>" name="stylesinclude[<?php echo $count; ?>]" <?php echo htsg_value("no", $file[1], "checked"); ?> /><label  title="The code will not be included anywhere (in the head or footer). This option is useful when this asset is being used by another asset and does not require to be explicitly included anywhere in the page" for="file_styles_no_<?php echo $count; ?>">&nbsp;Don't Include</label></div>&nbsp;<div class="radiobtn"><input type="radio" id="file_styles_head_<?php echo $count; ?>" name="stylesinclude[<?php echo $count; ?>]" <?php echo htsg_value("head", $file[1], "checked"); ?> /><label for="file_styles_head_<?php echo $count; ?>">&nbsp;Head</label></div><div class="radiobtn"><input type="radio" id="file_styles_foot_<?php echo $count; ?>" name="stylesinclude[<?php echo $count; ?>]" <?php echo htsg_value("foot", $file[1], "checked"); ?>/><label for="file_styles_foot_<?php echo $count; ?>">&nbsp;Footer</label></div> <div class="radiobtn"><input type="radio" id="file_styles_html_<?php echo $count; ?>" name="stylesinclude[<?php echo $count; ?>]" <?php echo htsg_value("html", $file[1], "checked"); ?> onchange="prompt('URL', '<?php echo $file[0]; ?>')" /><label for="file_styles_html_<?php echo $count++; ?>">&nbsp;HTML Code</label></div>  </td>
                        </tr>
                        <?php
                    }
                } $count = 0;
                ?>



                <?php
                if (empty($images)) {
                    
                } else {
                    ?> <tr><th colspan="2" class="cat_head">Images</th></tr>
                    <?php
                    foreach ($images as $file) {
                        ?>

                        <tr  class="hentry">


                            <td class="title column-title has-row-actions column-primary page-title" data-colname="File Path">
                                <strong>
                                    <a class="row-title" href="#" title=""><?php echo $file[0]; ?></a>
                                </strong>
                            </td>
                            <td class="date column-date" data-colname="Dateinpu"><div class="radiobtn"><input type="radio" id="file_images_no_<?php echo $count; ?>" name="imagesinclude[<?php echo $count; ?>]" <?php echo htsg_value("no", $file[1], "checked"); ?> /><label  title="The code will not be included anywhere (in the head or footer). This option is useful when this asset is being used by another asset and does not require to be explicitly included anywhere in the page" for="file_images_no_<?php echo $count; ?>">&nbsp;Don't Include</label></div> <div class="radiobtn"><input type="radio" id="file_images_html_<?php echo $count; ?>" name="imagesinclude[<?php echo $count; ?>]" <?php echo htsg_value("html", $file[1], "checked"); ?> onchange="prompt('URL', '<?php echo $file[0]; ?>')" /><label for="file_images_html_<?php echo $count++; ?>">&nbsp;HTML Code</label></div>  </td>
                        </tr>
                        <?php
                    }
                } $count = 0;
                ?>

                <?php
                if (empty($others)) {
                    
                } else {
                    ?>
                    <tr><th colspan="2" class="cat_head">Others</th></tr><?php
                    foreach ($others as $file) {
                        ?>

                        <tr  class="hentry">

                            <td class="title column-title has-row-actions column-primary page-title" data-colname="File Path">
                                <strong>
                                    <a class="row-title" href="#" title=""><?php echo $file[0]; ?></a>
                                </strong>
                            </td>
                            <td class="date column-date" data-colname="Dateinpu"><div class="radiobtn"><input type="radio" id="file_others_no_<?php echo $count; ?>" name="othersinclude[<?php echo $count; ?>]" <?php echo htsg_value("no", $file[1], "checked"); ?> /><label  title="The code will not be included anywhere (in the head or footer). This option is useful when this asset is being used by another asset and does not require to be explicitly included anywhere in the page" for="file_others_no_<?php echo $count; ?>">&nbsp;Don't Include</label></div>&nbsp; <div class="radiobtn"><input type="radio" id="file_others_html_<?php echo $count; ?>" name="othersinclude[<?php echo $count; ?>]" <?php echo htsg_value("html", $file[1], "checked"); ?> onchange="prompt('URL', '<?php echo $file[0]; ?>')" /><label for="file_others_html_<?php echo $count++; ?>">&nbsp;HTML Code</label></div>  </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>



        </table>
        <?php
    }
}

/*
 * add Multipart form data for File Attachment
 */

function htsg_update_edit_form() {
    echo ' enctype="multipart/form-data"';
}

/*
 * Supporting function to compare variable
 */

function htsg_value($compare1, $compare2, $return) {
    $value = "value='$compare1' ";

    return $compare1 == $compare2 ? $value . $return : $value;
}

/*
 * Display Plugin HTML field on plugin compose page.
 */

function htsg_wp_plugin_define() {

    global $post;
    // Get the info data if its already been entered

    
    $plugin_define_before = get_post_meta($post->ID, '_wp_plugin_define_before', true);
    $plugin_define_after = get_post_meta($post->ID, '_wp_plugin_define_after', true);
    $plugin_define_repeater = get_post_meta($post->ID, '_wp_plugin_define_repeater', true);
    
    ?>
   


    <div class="wp_field"><label for="_wp_plugin_define_before">Top HTML</label>
        <div class="tooltip">
            <span class="askTooltip">?</span>
            <span class="tooltiptext" style="left: -178px;width: 350px;">The top part will simply be printed the way it is entered here and you may enter any kind of code including javascript, jQuery, XML, etc.<img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/arrow.png" style="top: 64px;">
            </span>
        </div>
        <textarea rows="5" class="wp_input_fields" id="_wp_plugin_define_before" name="_wp_plugin_define_before"><?php echo html_entity_decode(esc_textarea($plugin_define_before)); ?></textarea></div>



    <div class="wp_field"><label for="_wp_plugin_define_repeater">Repeating HTML</label>
        <div class="tooltip">
            <span class="askTooltip">?</span>
            <span class="tooltiptext" style="left: -178px;width: 350px;">Write one line of the code that will repeat itself. You can choose to write scripts or any other code but only once as the final plugin will use this one line and allow the end-user to replicate it with custom information. <img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/arrow.png" style="top: 82px;">
            </span>
        </div>






        <div class="wp_field custom-button-wrap">

            <button title="Add Image" type="button" onclick="append('img')" class="button button-primary button-small btninsert" data-type="img">
                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                     width="20px" height="25px" viewBox="1 7 46 34" enable-background="new 1 7 46 34" xml:space="preserve">
                <path fill="#8A8A8A" d="M43,41H5c-2.209,0-4-1.791-4-4V11c0-2.209,1.791-4,4-4h38c2.209,0,4,1.791,4,4v26C47,39.209,45.209,41,43,41
                      z M45,11c0-1.104-0.896-2-2-2H5c-1.104,0-2,0.896-2,2v26c0,1.104,0.896,2,2,2h38c1.104,0,2-0.896,2-2V11z M41.334,34.715L35,28.381
                      L31.381,32l3.334,3.334c0.381,0.381,0.381,0.999,0,1.381c-0.382,0.381-1,0.381-1.381,0L19,22.381L6.666,34.715
                      c-0.381,0.381-0.999,0.381-1.381,0c-0.381-0.382-0.381-1,0-1.381L18.19,20.429c0.032-0.048,0.053-0.101,0.095-0.144
                      c0.197-0.197,0.457-0.287,0.715-0.281c0.258-0.006,0.518,0.084,0.715,0.281c0.042,0.043,0.062,0.096,0.095,0.144L30,30.619
                      l4.19-4.19c0.033-0.047,0.053-0.101,0.095-0.144c0.197-0.196,0.457-0.287,0.715-0.281c0.258-0.006,0.518,0.085,0.715,0.281
                      c0.042,0.043,0.062,0.097,0.095,0.144l6.905,6.905c0.381,0.381,0.381,0.999,0,1.381C42.333,35.096,41.715,35.096,41.334,34.715z
                      M29,19c-2.209,0-4-1.791-4-4s1.791-4,4-4s4,1.791,4,4S31.209,19,29,19z M29,13c-1.104,0-2,0.896-2,2s0.896,2,2,2s2-0.896,2-2
                      S30.104,13,29,13z"/>
                </svg><span class="btn-text">Editable Image</span>
            </button>



            <div class="details-open" id="image-details-open">

                <div style="margin-top:20px;">

                    <form>
                        <input type="checkbox" class="chkbox" id="image_title">Ask end-user for image title?<div style="clear:both"></div><br style="margin:20px 0 0 0">
                        <input type="checkbox" class="chkbox" id="image_alt">Ask end-user for image alt text?<div style="clear:both"></div><br style="margin:20px 0 0 0">
                        <label style="    width: 300px;height: 42px;">Enter label for this field.</label><br>
                        <input type="text" id="image_label">
                        <label style="    width: 300px;height: 42px;">Enter custom attributes within image tag (Ex: class="mainImage" or id="mainImage")</label><br>
                        <input type="text" id="image_custom"><br><br><br><br><br>
                        <a class="btn-addimage" href="#" onclick="add_image();
                                document.getElementById('image-details-open').style.display = 'none';
                                return false;">Add</a>
                        <a class="btn-addimage" style="float:right" href="#" onclick="document.getElementById('image-details-open').style.display = 'none';
                                return false;">Close</a>
                    </form>
                </div>
            </div>
            <div class="tooltip" style="float: none;top: 5px;left: -2px;">
                <span class="askTooltip">?</span>
                <span class="tooltiptext" style="left: -178px;width: 350px;">Insert this in part of your code where you want the end-user to insert an image when they<img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/arrow.png" style="top: 46px;">
                </span>
            </div>

            <button title="Add Text" type="button"  class="button button-primary button-small btninsert" data-type="txt" onclick="append('txt')">
                <svg version="1.1"
                     id="svg2" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:svg="http://www.w3.org/2000/svg"
                     xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="18px"
                     viewBox="0 0 32 32" enable-background="new 0 0 32 32" xml:space="preserve">
                <g id="background">
                <rect fill="none" width="32" height="32"/>
                </g>
                <g id="document_x5F_text_x5F_add">
                <path fill="#8A8A8A" d="M24,14.059V5.584L18.414,0H0v32h24v-0.059c4.499-0.5,7.998-4.309,8-8.941
                      C31.998,18.366,28.499,14.556,24,14.059z M17.998,2.413L21.586,6h-3.588C17.998,6,17.998,2.413,17.998,2.413z M2,30V1.998h14v6.001
                      h6v6.06c-1.752,0.194-3.352,0.89-4.652,1.941H4v2h11.517c-0.412,0.616-0.743,1.289-0.994,2H4v2h10.059
                      C14.022,22.329,14,22.661,14,23c0,2.829,1.308,5.351,3.349,7H2z M23,29.883c-3.801-0.009-6.876-3.084-6.885-6.883
                      c0.009-3.801,3.084-6.876,6.885-6.885c3.799,0.009,6.874,3.084,6.883,6.885C29.874,26.799,26.799,29.874,23,29.883z M20,12H4v2h16
                      V12z"/>
                <g>
                <polygon fill="#8A8A8A" points="28,22 24.002,22 24.002,18 22,18 22,22 18,22 18,24 22,24 22,28 24.002,28 24.002,24 28,24 		"/>
                </g>
                </g>
                </svg><span class="btn-text">Editable Text</span>
            </button> 
            <div class="tooltip" style="float: none;top: 5px;left: -2px;">
                <span class="askTooltip">?</span>
                <span class="tooltiptext" style="left: -178px;width: 350px;">Insert this in part of your code where you want the end-user to insert an image when they<img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/arrow.png" style="top: 46px;">
                </span>
            </div>

            <button title="Add Textarea"  type="button"  class="button button-primary button-small btninsert" data-type="txtarea" onclick="append('txtarea')">
                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                     width="20px" height="25px" viewBox="2.083 6.25 20.833 14.583" enable-background="new 2.083 6.25 20.833 14.583"
                     xml:space="preserve">
                <path fill="#8A8A8A" d="M14.583,10.417h-12.5V12.5h12.5V10.417z M14.583,6.25h-12.5v2.083h12.5V6.25z M18.75,14.583v-4.167h-2.083
                      v4.167H12.5v2.083h4.167v4.167h2.083v-4.167h4.167v-2.083H18.75z M2.083,16.667h8.333v-2.083H2.083V16.667z"/>
                </svg><span class="btn-text">Editable Description</span>
            </button>





        </div>

        <textarea rows="10" class="wp_input_fields" id="_wp_plugin_define_repeater" name="_wp_plugin_define_repeater"><?php echo html_entity_decode(esc_textarea($plugin_define_repeater)); ?></textarea></div>
    <div class="wp_field"><label for="_wp_plugin_define_after">Bottom HTML</label>
        <div class="tooltip">
            <span class="askTooltip">?</span>
            <span class="tooltiptext" style="left: -178px;width: 350px;">The bottom part will simply be printed the way it is entered here and you may enter any kind of code including javascript, jQuery, XML, etc.<img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/arrow.png" style="top: 64px;">
            </span>
        </div>


        <textarea rows="5" class="wp_input_fields" id="_wp_plugin_define_after" name="_wp_plugin_define_after"><?php echo html_entity_decode(esc_textarea($plugin_define_after)); ?></textarea></div>

     
    <?php
}

/*
 * Display Plugin Information field on plugin compose page.
 */

function htsg_wp_plugin_information() {
    global $post;

    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="plugin_info_meta_noncename" id="plugin_info_meta_noncename" value="' .
    wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    // Get the info data if its already been entered
    $plugin_name = get_post_meta($post->ID, '_wp_pluginname', true);
    $post_type = str_replace(' ', '_', strtolower($plugin_name));
    // Echo fields
    ?>
    


        <div class="wp_field"><label for="_wp_pluginname"><?php echo __('Plugin Name', "wp_plugin_builder"); ?></label>
        <div class="tooltip">
            <span class="askTooltip">?</span>
            <span class="tooltiptext" style="left: -178px;width: 350px;">This is the name of the plugin that will appear in the left menu of the plugin you are creating once it is installed and activated
                <img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/arrow.png" style="top: 64px;">
            </span>
        </div>
        <input type="text" class="wp_input_fields" id="_wp_pluginname" name="_wp_pluginname" required value="<?php echo $plugin_name ?>"  /></div>
    <h3>Implementation</h3>
    <h4>Shortcode:</h4>
    <p>[<?php echo $post_type; ?>] Place this shortcode where you want to show the plugin output</p>
    <h4>Manual:</h4>

    <?php
}

/**
 * Plugin update messages.
 *
 * @param array $messages Existing post update messages.
 *
 * @return array Amended post update messages with new CPT update messages.
 */
function htsg_wp_plugin_updated_messages($messages) {
    $post = get_post();
    $post_type = get_post_type($post);
    $post_type_object = get_post_type_object($post_type);

    $messages['wp_plugin'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => __('Plugin updated.', 'wp_plugin_builder'),
        4 => __('Plugin updated.', 'wp_plugin_builder'),
        /* translators: %s: date and time of the revision */
        5 => isset($_GET['revision']) ? sprintf(__('Plugin restored to revision from %s', 'wp_plugin_builder'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
        6 => __('Plugin Saved.', 'wp_plugin_builder'),
        7 => __('Plugin saved.', 'wp_plugin_builder'),
        8 => __('Plugin submitted.', 'wp_plugin_builder'),
    );

    return $messages;
}

/*
 * Save plugin information data
 */

function htsg_wp_save_wp_plugin_information($post_id, $post) {
    if (!$_POST)
        return;
    if ($post->post_type != 'wp_plugin')
        return;
    global $wpdb;
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if (isset($_POST['plugin_info_meta_noncename']))
        if (!wp_verify_nonce($_POST['plugin_info_meta_noncename'], plugin_basename(__FILE__))) {
            return $post->ID;
        }

    // Is the user allowed to edit the post or page?
    if (!current_user_can('edit_post', $post->ID))
        return $post->ID;

    // OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.
    $plugin_infometa['_wp_pluginname'] = strip_tags($_POST['_wp_pluginname']);
        $plugin_infometa['_wp_plugin_define_before'] = esc_html($_POST['_wp_plugin_define_before']);
    $plugin_infometa['_wp_plugin_define_after'] = esc_html($_POST['_wp_plugin_define_after']);
    $plugin_infometa['_wp_plugin_define_repeater'] = esc_html($_POST['_wp_plugin_define_repeater']);
    
    $plugin_infometa['_wp_pluginenable'] = intval($_POST['_wp_pluginenable']);



    // Add values of $events_meta as custom fields

    foreach ($plugin_infometa as $key => $value) { // Cycle through the $events_meta array!
        if ($post->post_type == 'revision')
            return; // Don't store custom data twice
        update_post_meta($post->ID, $key, $value);
        if (!$value)
            delete_post_meta($post->ID, $key); // Delete if blank
    }



    $scripts = json_decode((string) get_post_meta($post->ID, 'wp_plugin_assets_scripts', true));
    $styles = json_decode((string) get_post_meta($post->ID, 'wp_plugin_assets_styles', true));
    $images = json_decode((string) get_post_meta($post->ID, 'wp_plugin_assets_images', true));
    $others = json_decode((string) get_post_meta($post->ID, 'wp_plugin_assets_others', true));


    $scriptsinclude = isset($_POST['scriptsinclude']) ? (array) $_POST['scriptsinclude'] : array();
    $stylesinclude = isset($_POST['stylesinclude']) ? (array) $_POST['stylesinclude'] : array();
    $imagesinclude = isset($_POST['imagesinclude']) ? (array) $_POST['imagesinclude'] : array();
    $othersinclude = isset($_POST['othersinclude']) ? (array) $_POST['othersinclude'] : array();


    $scriptsinclude = array_map('htsg_validate_assets_position', $scriptsinclude);
    $stylesinclude = array_map('htsg_validate_assets_position', $stylesinclude);
    $imagesinclude = array_map('htsg_validate_assets_position', $imagesinclude);
    $othersinclude = array_map('htsg_validate_assets_position', $othersinclude);



    $scripts = htsg_array_value_replace($scripts, $scriptsinclude, 1);
    $styles = htsg_array_value_replace($styles, $stylesinclude, 1);
    $images = htsg_array_value_replace($images, $imagesinclude, 1);
    $others = htsg_array_value_replace($others, $othersinclude, 1);
    update_post_meta($post->ID, 'wp_plugin_assets_scripts', json_encode($scripts));
    update_post_meta($post->ID, 'wp_plugin_assets_styles', json_encode($styles));
    update_post_meta($post->ID, 'wp_plugin_assets_images', json_encode($images));
    update_post_meta($post->ID, 'wp_plugin_assets_others', json_encode($others));





    if (!empty($_FILES['wp_plugin_assets_attachment']['name'])) {

        $supported_types = array('application/zip');
        $arr_file_type = wp_check_filetype(basename($_FILES['wp_plugin_assets_attachment']['name']));
        $uploaded_type = $arr_file_type['type'];

        if (in_array($uploaded_type, $supported_types)) {

            $upload = wp_upload_bits($_FILES['wp_plugin_assets_attachment']['name'], null, file_get_contents($_FILES['wp_plugin_assets_attachment']['tmp_name']));
            if (isset($upload['error']) && $upload['error'] != 0) {
                wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
            } else {
                $upload['file'] = str_replace('\\', '/', $upload['file']);
                update_post_meta($post->ID, 'wp_plugin_assets_attachment', $upload);
                $files = array();
                $matches = array();
                $re = "/\\/[0-9]{4}\\/[0-9]{2}\\/(.*).zip$/";
                preg_match($re, $upload['url'], $matches);
                if (!empty($matches)) {
                    $upload_dir = wp_upload_dir();
                    $zip = new ZipArchive;
//open the archive
                    if ($zip->open($upload_dir['basedir'] . $matches[0]) === TRUE) {
                        //iterate the archive files array and display the filename or each one
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $files[] = $zip->getNameIndex($i);
                        }
                    } else {
                        echo 'Failed to open the archive!';
                    }
                }
                $count = 0;
                $scripts = preg_grep("/(.js)$/", $files);
                $styles = preg_grep("/(.css)$/", $files);
                $images = preg_grep("/[.](jpg)?(png)?(gif)?(tif)?$/", $files);
                $others = array_diff($files, $scripts, $styles, $images);

                $scripts = htsg_array_2d(str_replace("\\", "/", $scripts), 'head');
                $styles = htsg_array_2d(str_replace("\\", "/", $styles), 'head');
                $images = htsg_array_2d(str_replace("\\", "/", $images), 'no');
                $others = htsg_array_2d(str_replace("\\", "/", $others), 'no');


                update_post_meta($post->ID, 'wp_plugin_assets_scripts', json_encode($scripts));
                update_post_meta($post->ID, 'wp_plugin_assets_styles', json_encode($styles));
                update_post_meta($post->ID, 'wp_plugin_assets_images', json_encode($images));
                update_post_meta($post->ID, 'wp_plugin_assets_others', json_encode($others));
            }
        } else {
            wp_die("");
        }
    }
}

/*
 * Validate asset file position values
 */

function htsg_validate_assets_position($value) {
    $accepted_values = array('no', 'foot', 'head', 'html');
    if (in_array($value, $accepted_values)) {
        return $value;
    }

    return '';
}

/*
 * replace index of $arrays to index of $replace
 */

function htsg_array_value_replace($arrays, $replace, $index) {
    $return = array();
    $count = 0;

    for ($a = 0; $a < count($arrays); $a++) {
        $arrays[$a][$index] = $replace[$a];
    }
    return $arrays;
}

/*
 * Convert array to 2 dimension.
 */

function htsg_array_2d($arrays, $value) {
    $return = array();
    $count = 0;
    foreach ($arrays as $array) {
        $return[$count][0] = $array;
        $return[$count++][1] = $value;
    }
    return $return;
}

function htsg_array_search_r($needle, $haystack, $return_index) {
    $result = array();
    if (!empty($haystack)) {
        foreach ($haystack as $item) {
            if (($item == $needle) || (is_array($item) && array_search_r($needle, $item, $return_index))) {
                $result[] = $item[$return_index];
            }
        }
    }
    return $result;
}
