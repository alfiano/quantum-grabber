<?php
/*
Plugin Name: Quantum Grabber
version:2.0
Plugin URI: http://wpquantum.net
Description: A plugin to grab images from search engine and automatically create an image based post 
*/
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
       // var_dump($license_data);//uncomment it to look at the data
        
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
    $cek_lic_params = array(
        'slm_action' => 'slm_check',
        'secret_key' => QTM_SPECIAL_SECRET_KEY,
        'license_key' => get_option('sample_license_key'),
        );
// Send query to the license manager server
$response = wp_remote_get(add_query_arg($cek_lic_params, QTM_LICENSE_SERVER_URL), array('timeout' => 30, 'sslverify' => false));
//print_r($response);
$license_data = json_decode(wp_remote_retrieve_body($response));
//print_r($license_data);
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
    //echo 'License key already in use on '.$_SERVER['SERVER_NAME'];
    ?>
<?php //print_r($license_data);?>
<?php if ($license_data->result == "success") {
    echo "<p>Status: ".$license_data->status."</p>";
} else {
?><p>Status: <?php echo $license_data->message;?></p>
<?php }?>
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
    
// $cek_lic_params = array(
//     'slm_action' => 'slm_check',
//     'secret_key' => QTM_SPECIAL_SECRET_KEY,
//     'license_key' => get_option('sample_license_key'),
//     );
//     // Send query to the license manager server
//     $response = wp_remote_get(add_query_arg($cek_lic_params, QTM_LICENSE_SERVER_URL), array('timeout' => 30, 'sslverify' => false));
//     //print_r($response);
//     $license_data = json_decode(wp_remote_retrieve_body($response));
//     if($license_data->result == 'success'){
//         //echo "sukses";
//         include_once('sip.php');
//         $sip = new SimpleImagePoster();
//         //add_action('admin_menu', array('SimpleImagePoster','sip_plugin_menu'));
        
//     }