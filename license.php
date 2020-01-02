<?php

define('QTM_SPECIAL_SECRET_KEY', '580ae4418e9ae1.73850033'); 
define('QTM_LICENSE_SERVER_URL', 'https://wpquantum.net'); 
define('QTM_ITEM_REFERENCE', 'Quantum Grabber License'); 
add_action('admin_menu', 'slm_sample_license_menu');
function slm_sample_license_menu() {
    add_options_page('Quantum License Activation Menu', 'Quantum License', 'manage_options', __FILE__, 'sample_license_management_page');
}
function sample_license_management_page() {
    echo '<div class="wrap">';
    echo '<h2>Quantum License Management</h2>';
    /*** License activate button was clicked ***/
    if (isset($_REQUEST['activate_license'])) {
        $license_key = $_REQUEST['sample_license_key'];
		if(is_multisite())
			{
				$domain = site_url();
			}
		else
			{
				$domain = $_SERVER['SERVER_NAME'];
			}
        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_activate',
            'secret_key' => QTM_SPECIAL_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $domain, // Edit/Added by ParaTheme
            'item_reference' => urlencode(QTM_ITEM_REFERENCE),
        );
        // Send query to the license manager server
        $response = wp_remote_get(add_query_arg($api_params, QTM_LICENSE_SERVER_URL));
        // Check for error in the response
        if (is_wp_error($response)){
            echo "Unexpected Error! The query returned with an error.";
        }
        //var_dump($response);//uncomment it if you want to look at the full response
        
        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));
        
        // TODO - Do something with it.
        //var_dump($license_data);//uncomment it to look at the data
        
        if($license_data->result == 'success'){//Success was returned for the license activation
            ?> 
            <div class="update-message notice inline notice-alt updated-message notice-success"><p aria-label="updated!"><?php echo $license_data->message;?></p></div>
            <?php 
            //Uncomment the followng line to see the message that returned from the license server
            //echo '<br />The following message was returned from the server: '.$license_data->message;
            
            //Save the license key in the options table
            update_option('sample_license_key', $license_key);

        }
        else{
            //Show error to the user. Probably entered incorrect license key.
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />The following message was returned from the server: '.$license_data->message;
        }
    }
    /*** End of license activation ***/
    
    /*** License activate button was clicked ***/
    if (isset($_REQUEST['deactivate_license'])) {
        $license_key = $_REQUEST['sample_license_key'];
		if(is_multisite())
			{
				$domain = site_url();
			}
		else
			{
				$domain = $_SERVER['SERVER_NAME'];
			}
        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_deactivate',
            'secret_key' => QTM_SPECIAL_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $domain, // Edit/Added by ParaTheme
            'item_reference' => urlencode(QTM_ITEM_REFERENCE),
        );
        // Send query to the license manager server
        $response = wp_remote_get(add_query_arg($api_params, QTM_LICENSE_SERVER_URL));
        // Check for error in the response
        if (is_wp_error($response)){
            echo "Unexpected Error! The query returned with an error.";
        }
        //var_dump($response);//uncomment it if you want to look at the full response
        
        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));
        
        // TODO - Do something with it.
        //var_dump($license_data);//uncomment it to look at the data
        
        if($license_data->result == 'success'){//Success was returned for the license activation
           ?> 
            <div class="update-message notice inline notice-alt updated-message notice-success"><p aria-label="updated!"><?php echo $license_data->message;?></p></div>
            <?php 
            //Uncomment the followng line to see the message that returned from the license server
           // echo '<br />The following message was returned from the server: '.$license_data->message;
            

            //Remove the licensse key from the options table. It will need to be activated again.
            update_option('sample_license_key', '');
        }
        else{
            //Show error to the user. Probably entered incorrect license key.
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />The following message was returned from the server: '.$license_data->message;
        }
        
    }
    /*** End of sample license deactivation ***/
    
    ?>
    <form action="" method="post">
        <table class="form-table">
            <tr>
                <th style="width:100px;"><label for="sample_license_key">License Key</label></th>
                <td ><input class="regular-text" type="text" id="sample_license_key" name="sample_license_key"  value="<?php echo get_option('sample_license_key'); ?>" ></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="activate_license" value="Activate" class="button-primary" />
            <input type="submit" name="deactivate_license" value="Deactivate" class="button" />
        </p>
    </form>
    <?php
    
    echo '</div>';
}


include_once('sip.php');
$sip = new SimpleImagePoster();

add_action('admin_menu', array('SimpleImagePoster','sip_plugin_menu'));
add_action('wp_ajax_bulk_grab', array('SimpleImagePoster','bulk_grab'));

add_action('wp_ajax_bulk_delete_post', array('SimpleImagePoster','bulk_delete_post'));
add_action('wp_ajax_bulk_delete_image', array('SimpleImagePoster','bulk_delete_image'));
add_action('wp_ajax_bulk_delete_post_and_image', array('SimpleImagePoster','bulk_delete_post_and_image'));
add_action('wp_ajax_count_image_posted', array('SimpleImagePoster','count_image_posted'));
add_action('wp_ajax_one_grab', array('SimpleImagePoster','one_grab'));
add_action('wp_ajax_download_image', array('SimpleImagePoster','download_image'));
add_action('wp_ajax_save_kw', array('SimpleImagePoster','save_kw'));
add_action('wp_ajax_save_template', array('SimpleImagePoster','save_template'));
add_action('wp_ajax_save_settings', array('SimpleImagePoster','save_settings'));
add_action('wp_ajax_create_post', array('SimpleImagePoster','create_post'));
add_action('wp_ajax_get_posts', array('SimpleImagePoster','get_posts'));
//add_action('wp_ajax_sip_cron', array('SimpleImagePoster','sip_cron'));
add_action('quantum_cron_action',array('SimpleImagePoster','quantum_cron'));
add_shortcode('post_title',array('SimpleImagePoster','post_title_shortcode'));
wp_enqueue_script('sip-plugin', plugins_url( '/js/custom.js' , __FILE__ ) , array( 'jquery' ));
wp_localize_script( 'sip-plugin', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php')));
function sip_admin_style() {
    wp_register_style( 'sip-style', plugins_url('style.css', __FILE__) );
    wp_enqueue_style( 'sip-style' );
}
add_action( 'admin_enqueue_scripts', 'sip_admin_style' );
function success_posted_post_meta() {
    register_post_meta( 'post', 'success_posted_field', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ) );
}
add_action( 'init', 'success_posted_post_meta' );
if (get_option('reset_img_metadata')==1) {
if (!function_exists('wp_handle_upload'))
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	add_action('wp_handle_upload', array('SimpleImagePoster', 'setExtension'));
}