<?php

class SimpleImagePoster {
static public function sip_plugin_menu(){
   
        add_menu_page('Quantum Grabber', 'Quantum Grabber', 'manage_options', 'simple-image-poster', array('SimpleImagePoster', 'sip_panel'));
        add_submenu_page( 'simple-image-poster', __('Bulk Poster', 'simple-image-poster'), __('Bulk Poster', 'simple-image-poster'), 'manage_options', 'simple-image-poster',array('SimpleImagePoster', 'sip_panel'));
        
        $cek_lic_params = array(
                        'slm_action' => 'slm_check',
                        'secret_key' => QTM_SPECIAL_SECRET_KEY,
                        'license_key' => get_option('sample_license_key'),
                        );
        // Send query to the license manager server
        $response = wp_remote_get(add_query_arg($cek_lic_params, QTM_LICENSE_SERVER_URL), array('timeout' => 30, 'sslverify' => false));
        //print_r($response);
        $license_data = json_decode(wp_remote_retrieve_body($response));
        if($license_data->result == 'success'){
            add_submenu_page( 'simple-image-poster', __('Keywords', 'simple-image-poster'), __('Keywords', 'simple-image-poster'), 'manage_options', 'simple-image-savekw',array('SimpleImagePoster', 'save_kw_html'));
       }
        elseif($license_data->message === 'License key already in use on '.$_SERVER['SERVER_NAME']) {
            echo $license_data->message;
            add_submenu_page( 'simple-image-poster', __('Keywords', 'simple-image-poster'), __('Keywords', 'simple-image-poster'), 'manage_options', 'simple-image-savekw',array('SimpleImagePoster', 'save_kw_html'));
         }
        add_submenu_page( 'simple-image-poster', __('Template', 'simple-image-poster'), __('Template', 'simple-image-poster'), 'manage_options', 'simple-image-template',array('SimpleImagePoster', 'sip_template'));
        add_submenu_page( 'simple-image-poster', __('Setting', 'simple-image-poster'), __('Setting', 'simple-image-poster'), 'manage_options', 'simple-image-setting',array('SimpleImagePoster', 'sip_setting'));
        add_submenu_page( 'simple-image-poster', __('Post Management', 'simple-image-poster'), __('Post Management', 'simple-image-poster'), 'manage_options', 'simple-image-post-management',array('SimpleImagePoster', 'post_management'));
}
static public function post_management(){
        if(isset($_REQUEST['nonce_token'])) {
        $nonce = $_REQUEST[ 'nonce_token' ];
        $postid = $_REQUEST[ 'post' ];
        $nonce = wp_verify_nonce( $nonce, 'delete_att_' . $postid );
        switch ( $nonce ) {
            case 1:
                echo '<div class="update-message notice inline notice-alt updated-message notice-success"><p aria-label="updated!">Images has been deleted. Back to <a href="'.admin_url('admin.php?page=simple-image-post-management').'">Post Management</p></div>';     
                $postids = self::count_img_number($postid);// get all att id from this post
                foreach ($postids as $postid){
                    wp_delete_attachment($postid->ID,"true");
                }
                wp_redirect( admin_url() );
		        exit();
                //break;
            case 2:
            echo '<div class="update-message notice inline notice-alt updated-message notice-success"><p aria-label="updated!">Images has been deleted. Back to <a href="'.admin_url('admin.php?page=simple-image-post-management').'">Post Management</p></div>'; 
                $postids = self::count_img_number($postid);// get all att id from this post
                foreach ($postids as $postid){
                    wp_delete_attachment($postid->ID,"true");
                } 
                wp_redirect( admin_url() );
		        exit();
               // break;
            default:
            wp_redirect( admin_url() );
                exit( 'Illegal access, sssttt...' );
            }
        }
    
    global $post;
    $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
    
    if ($_GET['sort'] == "few_images") {
        $meta_query = array(
            array(
                'key' => '_few_images',
                )
            );
       
    } else {
        $meta_query = array(
            array(
                'key' => 'success_posted_field',
            )
        );
    }
    $args = array(
        'post_type'     => 'post',
        //'post_status'   => 'publish',
        'posts_per_page' => 80,
        'paged' => $paged,
        'orderby'=>'date',
        'order' => 'DESC',
        'meta_query' => $meta_query,
    );

    $few_args = array(
        'post_type'     => 'post',
        //'post_status'   => 'publish',
        'posts_per_page' => 80,
        'paged' => $paged,
        'orderby'=>'date',
        'order' => 'DESC',
        'meta_query' => array(
            array(
                'key' => '_few_images',
                )
            ),
    );

    $all_args = array(
        'post_type'     => 'post',
        //'post_status'   => 'publish',
        'posts_per_page' => 80,
        'paged' => $paged,
        'orderby'=>'date',
        'order' => 'DESC',
        'meta_query' => array(
            array(
                'key' => 'success_posted_field',
                )
            ),
    );

    $few_imgs = new WP_Query($few_args);
    $all_imgs = new WP_Query($all_args);
    $get_posts = new WP_Query($args);

    if (basename($_SERVER['REQUEST_URI']) == "admin.php?page=simple-image-post-management&sort=few_images") {
        $is_fewpost_active = "active";
    } else {
        $is_allpost_active = "active";
    }
    //print_r($get_posts);?>
    <div class="left-panel full-width">
    <div class="header">
        <h2>Post Management</h2><span class="posted-number"></span>
        <div class="tabs">
            <span class="tab-class <?php echo $is_allpost_active;?>"><a href="<?php echo get_option('siteurl');?>/wp-admin/admin.php?page=simple-image-post-management&sort=all_post"><?php echo "All post (".$all_imgs->found_posts.")";?></a></span>
            <span class="tab-class <?php echo $is_fewpost_active;?>"><a href="<?php echo get_option('siteurl');?>/wp-admin/admin.php?page=simple-image-post-management&sort=few_images"><?php echo "Post with Few Images (".$few_imgs->found_posts.")";?> </a></span>
        </div>
    </div>
    <?php if($get_posts->have_posts()): ?>
    <table class="wp-list-table widefat fixed striped users">
    <thead>
        <td class="checkbox"><input type="checkbox" id="selectall"></td>
        <td>Title</td>
        <td>Keyword</td>
        <td>Image</td>
        <td>Date</td>
        <td>Action</td>
    </thead>
    <tbody>
    <?php while ($get_posts->have_posts() ): $get_posts->the_post();?>
     <tr>
         <td class="checkbox"><input type="checkbox" class="singlechekbox" value=<?php echo $post->ID;?> data-kw="<?php echo get_post_meta($post->ID, "success_posted_field", true);?>"></td>
        <td id="post-id" style="display:none"><?php echo $post->ID;?></td>
        <td><?php the_title();?></td>
        <td id="kw"><?php echo get_post_meta($post->ID, "success_posted_field", true);?></td>
        <td><?php echo count(self::count_img_number($post->ID));?> | <a id="one-grab" href="#">Grab</a> | 
        <?php 
        $url = wp_nonce_url( admin_url("/admin.php?page=simple-image-post-management"), 'delete_att_' . $post->ID, 'nonce_token' );
        $url = add_query_arg( 'post', $post->ID, $url ); // Add the id of the user we send to
        ?>
            <a href="<?php echo $url; ?>"><?php _e( '<i class=" icon-trash-empty"></i>', 'textdomain' ); ?></a>
        </td>
        <td><?php self::sip_post_status($post);?></td>
        <td><a title="edit post" class="item-action edit" href="<?php echo get_option('siteurl');?>/wp-admin/post.php?action=edit&post=<?php echo $post->ID;?>"><i class="icon-pencil"></i></a><a title="view post" class="item-action view" href="<?php echo get_option('siteurl');?>?p=<?php echo $post->ID;?>"><i class=" icon-eye"></i></a><a title="delete post" class="item-action delete" href="<?php echo wp_nonce_url( site_url() . "/wp-admin/post.php?action=trash&post=" . $post->ID, 'trash-post_' . $post->ID);?>" onclick="javascript:if(!confirm('Are you sure you want to move this item to trash?')) return false;"><i class="icon-trash-empty"></i></a></td>     
    </tr>

    <?php endwhile; wp_reset_postdata();?>
    </tbody>
    </table>

    <select id="bulkopt" name="bulkopt">
        <option value="0">---</option>
        <option value="grab">Grab Images</option>
        <option value="delete-image">Delete Image</option>
        <option value="delete-post">Delete Post</option>
        <option value="delete-post-image">Delete Post and Image</option>
    </select>
    <input type='button' id='apply' name='apply' value='Apply'>

    </div>
    <div class="pagination">
        <?php echo self::pagination($get_posts->max_num_pages, $paged);?>
    </div>
    <?php else:?>
        <div class="left-panel">
        <div class="update-message notice inline notice-warning notice-alt"><p class="no-post">NO POST CREATED</p></div>
        </div>
    <?php endif;?>
    <div class="fixed-wrap" style="display:none">
        <div class="right-panel">
        <div class="header">
            <h2>Result</h2>
            <span class="close"><a href="<?php echo basename($_SERVER['REQUEST_URI']);?>">Close</a></span>
        </div>
        <div id="loader" class="loader">
        </div>
        <div id="done">
            <div class="update-message notice inline notice-alt updated-message notice-success"><p aria-label="updated!">Done!</p></div>
        </div>
        <div class="rp-inner">
            <ol id="sukses">
            </ol>
            <ol id="gagal">
            </ol>
        </div>
        </div>
    </div>
<?php
}

static public function sip_setting(){
        $se = array("bing"=>"bing", "google"=>"google");
        if (get_option('search_engine')=="bing"){
            $is_bing = "selected";
        } else {
            $is_google ="selected";
        }
        $arrSize = array('all' => 'all', 'small' => 'small', 'medium' => 'medium', 'large' => 'large', 'wallpaper' => 'wallpaper');
        if (get_option('save_image_as')=='keyword') {
			$is_keyword = "selected";
		} elseif (get_option('save_image_as')=='origin') {
			$is_origin = "selected";
        }
        else {
            $is_template = "selected";
        }
        if (get_option("image_license")=="all"){
            $is_all = "selected";
        } elseif (get_option("image_license")=="all_creative_commons"){
            $is_all_creative_commons = "selected";
        } elseif (get_option("image_license")=="public_domain"){
            $is_public_domain = "selected";
        }elseif (get_option("image_license")=="free_to_share_and_use"){
            $is_free_to_share_and_use = "selected";
        } elseif (get_option("image_license")=="free_to_share_and_use_com") {
            $is_free_to_share_and_use_com = "selected";
        } elseif(get_option("image_license")=="free_to_modify_share_and_use"){
            $is_free_to_modify_share_and_use = "selected";
        } else {
            $is_free_to_modify_share_and_use_com = "selected";
        }

        
		// $str_flip_disable = "";
		// $is_image_flipped ="";
		// $is_flip_available = false;
		// if (function_exists('imageflip')) {
		// 	$is_flip_available = true;
		// } else {
		// 	$str_flip_disable = "disabled";
        // }
        if (get_option('save_image')==1) {
			$is_save_image=' checked ';
        }
		if (get_option('save_mode')==1) {
			$is_save_mode=' checked ';
        }
		if (get_option('reset_img_metadata')==1) {
			$is_image_flipped=' checked ';
        }
        $is_cron_loop='';
        if (get_option('sip_cron_kw_loop')==1) 
        $is_cron_loop=' checked ';
        $is_save_ori='';
        if (get_option('save_ori_filename')==1) 
        $is_save_ori=' checked ';

        ?>
<div class="wrap">
<div class="left-panel">
    <div class="header">
        <h2>Image Setting</h2>
    </div>
    <div class="item-setting">
        <label>Search Engine</label>
        <select id="search_engine" name="search_engine">
            <?php foreach ($se as $idx => $value) {
            $sel = "";
            if ($idx == get_option('search_engine') ) $sel = ' selected ';?>
            <option value="<?php echo $idx;?>" <?php echo $sel;?>><?php echo $value;?></option>
            <?php }?>
        </select>
    </div>
    <div class="item-setting">
        <label>Save Image to Server</label>
        <?php //echo get_option("save_mode");?>
        <input type="checkbox" id="save_image" name="save_image"
            <?php echo $is_save_image;?> value=1 /> <?php echo "Yes";?>
    </div>
    <div class="item-setting">
        <label>Save hosting</label>
        <?php //echo get_option("save_mode");?>
        <input type="checkbox" id="save_mode" name="save_mode"
            <?php echo $is_save_mode;?> value=1 /> <?php echo "Yes";?>
    </div>
    <div class="item-setting">
        <label>Image number each post</label>
        <input size=3 id="num_image" name="num_image" value="<?php echo get_option('num_image');?>" />
    </div>
    <div class="item-setting">
        <label>Image size</label>
        <select id="img_size" name="img_size">
            <?php foreach ($arrSize as $idx => $value) {
            $sel = "";
            if ($idx == get_option('img_size') ) $sel = ' selected ';?>
            <option value="<?php echo $idx;?>" <?php echo $sel;?>><?php echo $value;?></option>
            <?php }?>
        </select>
    </div>
    <div class="item-setting">
        <label>Image License</label>
        <select id="image_license" name="image_license">
            <option value="all" <?php echo $is_all;?>>All</option>
            <option value="all_creative_commons" <?php echo $is_all_creative_commons;?>>All Creative Commons</option>
            <option value="public_domain" <?php echo $is_public_domain;?>>Public Domain</option>
            <option value="free_to_share_and_use" <?php echo $is_free_to_share_and_use;?>>Free to share and Use</option>
            <option value="free_to_share_and_use_com" <?php echo $is_free_to_share_and_use_com;?>>Free to Share and Use Commercially</option>
            <option value="free_to_modify_share_and_use" <?php echo $is_free_to_modify_share_and_use;?>>Free to Modify, Share and Use</option>
            <option value="free_to_modify_share_and_use_com" <?php echo $is_free_to_modify_share_and_use_com;?>>Free to Modify, Share and Use Commercially</option>
        </select>
    </div>
    <div class="item-setting">
        <label>Reset Image metadata</label>
        <input type="checkbox" id="reset_img_metadata" name="reset_img_metadata"
            <?php echo $is_image_flipped;?> value=1 /> <?php echo "Yes";?>
    </div>
    <div class="item-setting">
        <label>Save image as:</label>
        <select id="save_image_as" name="save_image_as">
            <option value="origin" <?php echo $is_origin;?>>Original File Name</option>
            <option value="keyword" <?php echo $is_keyword;?>>Keyword</option>
            <option value="template" <?php echo $is_template;?>>Template</option>
        </select>
    </div>
    <div class="item-setting">
        <label>Grab image result only from sites:</label>
        <?php 
        $target = get_option('target_site');
        $target_sites=array("none", "pinterest.com", "blogspot.com", "flickr.com",
	"pixabay.com", "amazon.com");?>
        <select id="target_site" name="target_site">
            <?php foreach ($target_sites as $idx => $value) {
            $value = trim($value);
            $sel = "";
            if (strcmp(trim($target), $value) == 0) $sel = ' selected ';?>
            <option value=<?php echo $value.''.$sel;?>><?php echo $value;?></option>
            <?php }?>
        </select>
        <?php //echo get_option("target_site");?>
    </div>
    <div class="item-setting">
        <label>Exclude website from search result:</label>
        <input size=30 id="exclude_site" name="exclude_site" value="<?php echo get_option('exclude_site');?>" /> <span class="ket">Ex: youtube.com</span>
    </div>
</div>
<div class="right-panel">
    <div class="header">
        <h2>CRON Setting</h2>
    </div>
    <div class="item-setting">
        <label>Category</label>
        <?php 
        $cron_cat = get_option('cron_category');
		wp_dropdown_categories(array(
            'hide_empty' => 0, 
            'name' => 'cron_category', 
            'orderby' => 'name',
            'selected' => $cron_cat, 
            'hierarchical' => true, 
            'show_option_none' => __('None')));
		?>
    </div>
    <div class="item-setting">
            <label>Start Post Date</label>
            <?php 
            $base_date = mktime(0, 0, 0, (int)get_option('date_month'), (int)get_option('date_day'), (int)get_option('date_year'));
            //echo get_option('date_month')."-".get_option('date_day')."-".get_option('date_year');

            echo self::show_day();		
			echo self::show_month();
            echo self::show_year();
			?>
        </div>
        <div class="item-setting">
            <label>Post Interval</label>
            <input type="text" value=<?php echo get_option("interval_num");?> name="interval[value]" id="interval_num" style="width:40px;">
            <?php 
            if (get_option("interval_type")=="hours"){
                $is_hours = "selected";
            }
            else {
                $is_days = "selected";
            }
            ?>
            <select name="interval[type]" id="interval_type">
                <option value="hours"<?php echo $is_hours;?>>Hour</option>
                <option value="days" <?php echo $is_days;?>>Day</option>
            </select>
        </div>
    <div class="item-setting">
    <?php
    if (get_option("cron_post_status")=="publish"){
        $is_publish = "selected";
    } else {
        $is_draft ="selected";
        }
    ?>
        <label>Post Status</label>
        <select name="cron_post_status" id="cron_post_status">
            <option value="publish" <?php echo $is_publish;?>>Published</option>
            <option value="draft" <?php echo $is_draft;?>>Draft</option>
        </select>
    </div>
    <div class="item-setting">
        <label>Post again after last keyword</label>
        <input type="checkbox" id="sip_cron_kw_loop" name="sip_cron_kw_loop" <?php echo $is_cron_loop;?>
            value=1 /><?php echo "Yes";?>
    </div>
</div>
<input type='button' id='save_setting' name='submit' value='Save Settings' />
<div id="saved"></div>
</div>
<?php 
}
static public function sip_template(){
        $template = get_option('post_template');
        $title_template = get_option('title_template');
        $attachment_title_template = get_option('attachment_title_template');
        $attachment_filename_template = get_option('attachment_filename_template');
        $attachment_caption_template = htmlspecialchars(get_option('attachment_caption_template'));
        $attachment_description_template = htmlspecialchars(get_option('attachment_description_template'));
        ?>
<div class="wrap">
<div class="left-panel">
    <div class="header">
        <h2>Templates</h2>
    </div>
    <div class="item-setting">
        <label>Title Template</label><br>
        <input type="text" id="title_template" name="title_template" value="<?php echo $title_template;?>"><br />
    </div>
    <div class="item-setting">
        <label>Post Template</label>
        <textarea name="post_template" id="post_template" cols="75" rows="20"><?php echo $template;?></textarea><br />
    </div>
    <div class="item-setting">
        <label>Attachment Filename Template</label><br>
        <input type="text" id="attachment_filename_template" name="attachment_filename_template"
            value="<?php echo $attachment_filename_template;?>">
    </div>
    <div class="item-setting">
        <label>Attachment Title Template</label><br>
        <input type="text" id="attachment_title_template" name="attachment_title_template"
            value="<?php echo $attachment_title_template;?>">
    </div>
    <div class="item-setting">
        <label>Attachment Caption Template</label><br>
        <input type="text" id="attachment_caption_template" name="attachment_caption_template"
            value="<?php echo $attachment_caption_template;?>">
    </div>
    <div class="item-setting">
        <label>Attachment Description Template</label><br>
        <input type="text" id="attachment_description_template" name="attachment_description_template"
            value="<?php echo $attachment_description_template;?>">
    </div>
    <input type='button' id='save_template' name='submit' value='Save Post Template' />
</div>
<div class="right-panel">
    <div class="header">
        <h2>Documentation</h2>
    </div>
    <div class="item-setting">
        <label>Title Template Shortcodes</label><br>
        <ul class="shortcode-list">
            <li><p><span class="sc">{IMG_NUMBER}</span>: Number of images uploaded to its post</p></li>
            <li><p><span class="sc">{POST_KEYWORD}</span>: keyword for its post</p></li>
        </ul>
        <label>Post Template Shortcodes</label><br>
        <ul class="shortcode-list">
            <li><p><span class="sc">{IMG_NUMBER}</span>: Number of images uploaded to its post</p></li>
            <li><p><span class="sc">{POST_KEYWORD}</span>: keyword for its post</p></li>
            <li><p><span class="sc">{PERMALINK}</span>: permalink for its post</p></li>
            <li><p><span class="sc">{POST_TITLE}</span>: title for its post</p></li>
            <li><p><span class="sc">{POST_CATEGORY}</span>: category for its post</p></li>
            <li><p><span class="sc">{GALLERY}</span>: gallery for its post</p></li>
            <li><p><span class="sc">{IMG_X}</span>: Display image number X</p></li>
            <li><p><span class="sc">{IMG_SRC_X}</span>: Display image source number x that uploaded for its post. ex: IMG_SRC_1</p></li>
            <li><p><span class="sc">{IMG_TITLE_X}</span>: Display image title number x that uploaded for its post. ex: IMG_TITLE_1</p></li>
            <li><p><span class="sc">{ARTICLE}</span>: Generate article based on keyword</p></li>
            <li><p><span class="sc">{IMG}</span>: Insert all images that uploaded to its post</p></li>
            
        </ul>
        <label>Attachment Filename Template Shortcodes</label><br>
        <ul class="shortcode-list">
        <li><p><span class="sc">{POST_KEYWORD}</span>: keyword from its parent post</p></li>
            <li><p><span class="sc">{IMG_TITLE}</span>: image title grabbed from remote server</p></li>
            <li><p><span class="sc">{IMG_FILENAME}</span>: image filename grabbed from remote server</p></li>
        </ul>

        <label>Attachment Title Template Shortcodes</label><br>
        <ul class="shortcode-list">
        <li><p><span class="sc">{POST_KEYWORD}</span>: keyword from its parent post</p></li>
            <li><p><span class="sc">{IMG_TITLE}</span>: image title grabbed from remote server</p></li>
            <li><p><span class="sc">{IMG_FILENAME}</span>: image filename grabbed from remote server</p></li>
        </ul>
        <label>Attachment Caption Template Shortcodes</label><br>
        <ul class="shortcode-list">
        <li><p><span class="sc">{POST_KEYWORD}</span>: keyword from its parent post</p></li>
            <li><p><span class="sc">{IMG_TITLE}</span>: image title grabbed from remote server</p></li>
            <li><p><span class="sc">{IMG_FILENAME}</span>: image filename grabbed from remote server</p></li>
            <li><p><span class="sc">{IMG_LINK}</span>: image link from remote server</p></li>
            <li><p><span class="sc">{IMG_DOMAIN}</span>: Domain name of grabbed image</p></li>
        </ul>
        <label>Attachment Description Template Shortcodes</label><br>
        <ul class="shortcode-list">
        <li><p><span class="sc">{POST_KEYWORD}</span>: keyword from its parent post</p></li>
            <li><p><span class="sc">{IMG_TITLE}</span>: image title grabbed from remote server</p></li>
            <li><p><span class="sc">{IMG_FILENAME}</span>: image filename grabbed from remote server</p></li>
            <li><p><span class="sc">{IMG_LINK}</span>: image link from remote server</p></li>
            <li><p><span class="sc">{IMG_DOMAIN}</span>: Domain name of grabbed image</p></li>
        </ul>
    </div>
</div>
</div>
<?php }

static public function save_kw_html(){?>
<div class="wrap">
<div class="left-panel">
<div class="header">
    <h2>Keywords</h2>
</div>
<?php if (isset($_REQUEST['campaign']) ){
        echo $_REQUEST['campaign'];
    }
    else {?>
        <form name="frmPost" method="post">
        <div class="item-setting">
        <label>Add keywords here (one per line)</label>
        <textarea name="add_kw" id="add_kw" cols="60" rows="20"></textarea><br>
        <input type='button' id='remove_duplicate' name='remove_duplicate' value='Remove Duplicate' />
        <input type='button' id='save_kw' name='submit' value='Saved Keyword' /><br>
        <label>Saved Keyword</label>
        <textarea name="kw_list" id="kw_list" cols="60" rows="20" disabled><?php echo get_option('sip_saved_kw');?></textarea>
        <input type='button' id='delete_kw' name='submit' value='Delete Keyword' /><br>
        </div>
</form>
<?php }?>
</div>
</div>
<?php }

static public function sip_panel(){
    ?>
<div class="wrap">

<div class="left-panel">
    <div class="header">
        <h2>Bulk Poster</h2>
    </div>
    <form name="frmPost" method="post">
        <div class="item-setting">
            <label>Keywords</label>
            <?php 
                $kw_posted = self::get_meta_values('success_posted_field', 'post');
                $arr_sip_saved_kw = explode("\n", get_option('sip_saved_kw'));
                ?>
            <select name="bulk_post_titles[]" id="bulk_post_titles" multiple="multiple" size="10">
                <?php
                foreach($arr_sip_saved_kw as $sip_saved_kw) {
                    if (in_array($sip_saved_kw, $kw_posted)) {?>
                    <option value="<?php echo $sip_saved_kw;?>" disabled><?php echo $sip_saved_kw;?></option>
                    <?php } 
                    else {?>
                        <option value="<?php echo $sip_saved_kw;?>"><?php echo $sip_saved_kw;?></option>
                    <?php }?>
                <?php }?>
            </select>
            <?php
            if (get_option('sip_saved_kw')=="") {
                echo "<div>Your keyword is empty, fill <a href=".site_url("/wp-admin/admin.php?page=simple-image-savekw").">here</a></div>";
            } else {
                echo "<div>".count($arr_sip_saved_kw)." keywords</div>";
            }?>
        </div>
        <div class="item-setting">
            <label>Category</label>
            <?php 
		wp_dropdown_categories(array('hide_empty' => 0, 'name' => 'category', 'orderby' => 'name', 
				'selected' => $cron_cat, 'hierarchical' => true, 'show_option_none' => __('None')));
		?>
        </div>
        <div class="item-setting">
            <label>Post Status</label>
            <select name="bulk_post_status" id="bulk_post_status">
                <option value="publish">Published</option>
                <option value="draft">Draft</option>
            </select>
        </div>
        <div class="item-setting">
            <label>Start Post Date</label>
            <?php 
            echo self::show_day();		
			echo self::show_month();
            echo self::show_year();
			?>
        </div>
        <div class="item-setting">
            <label>Post Interval</label>
            <input type="text" value="1" name="interval[value]" id="interval_num" style="width:40px;">
            <select name="interval[type]" id="interval_type">
                <option value="hours">Hour</option>
                <option value="days">Day</option>
            </select>
        </div>
        <input type='button' id='bulk-post' name='submit' value='Create Bulk Post' />
    </form>
</div>

<div class="right-panel">
    <div class="header">
        <h2>Result</h2>
    </div>
    <div id="loader" class="loader">
    </div>
    <div id="done">
    <div class="update-message notice inline notice-alt updated-message notice-success"><p aria-label="updated!">Done!</p></div>
    </div>
    <div class="rp-inner">
        <ol id="sukses">
        </ol>
        <ol id="gagal">
        </ol>
    </div>
</div>
</div>
<?php 
}
public function get_title_template($title, $id) {
    $template = get_option('title_template');
    if ($template==""){
        $template = "{POST_KEYWORD}";
    }
    $args1 = array(
        'post_type'=>'attachment',
        'posts_per_page'=>-1,
        'post_parent'=>$id	
        );
    $allphotos = get_posts($args1);
    $imgnumber = count($allphotos);
    $template = str_replace('{POST_KEYWORD}', $title, $template);
    $template = str_replace('{IMG_NUMBER}', $imgnumber, $template);
    return $template;
}
public function get_att_title_template($kw, $title, $localurl) {
    
    $img_filename = preg_replace('/\.[^.]+$/', '', basename($localurl));
    $img_filename = str_replace("-", " ", $img_filename);
    $template = get_option('attachment_title_template');
    if ($template==""){
        $template = "{IMG_TITLE}";
    }
    $template = str_replace('{POST_KEYWORD}', $kw, $template);
    $template = str_replace('{IMG_TITLE}', $title, $template);
    $template = str_replace('{IMG_FILENAME}', $img_filename, $template);
    return $template;
}

public function get_filename_template($kw, $title, $localurl) {
    
    $img_filename = preg_replace('/\.[^.]+$/', '', basename($localurl));
    //$img_filename = str_replace("-", " ", $img_filename);
    $kw = str_replace("-", " ", $kw);
    $template = get_option('attachment_filename_template');
    if ($template==""){
        $template = "{IMG_FILENAME}";
    }
    $template = str_replace('{POST_KEYWORD}', $kw, $template);
    $template = str_replace('{IMG_TITLE}', $title, $template);
    $template = str_replace('{IMG_FILENAME}', $img_filename, $template);
    return $template;
}

public function get_caption_template($kw, $title, $localurl, $domain, $imglink) {
    
    $img_filename = preg_replace('/\.[^.]+$/', '', basename($localurl));
    $img_filename = str_replace("-", " ", $img_filename);
    $kw = str_replace("-", " ", $kw);
    $domain = $domain;
    $imglink = $imglink;
    $template = get_option('attachment_caption_template');
    if ($template==""){
        $template = "source: {IMG_DOMAIN}";
    }
    $template = str_replace('{POST_KEYWORD}', $kw, $template);
    $template = str_replace('{IMG_TITLE}', $title, $template);
    $template = str_replace('{IMG_FILENAME}', $img_filename, $template);
    $template = str_replace('{IMG_DOMAIN}', $domain, $template);
    $template = str_replace('{IMG_LINK}', $imglink, $template);
    return $template;
}

public function get_description_template($kw, $title, $localurl, $domain, $imglink) {
    
    $img_filename = preg_replace('/\.[^.]+$/', '', basename($localurl));
    $img_filename = str_replace("-", " ", $img_filename);
    $kw = str_replace("-", " ", $kw);
    $domain = $domain;
    $imglink = $imglink;
    $template = get_option('attachment_description_template');
    if ($template==""){
        $template = "source: {IMG_TITLE}";
    }
    $template = str_replace('{POST_KEYWORD}', $kw, $template);
    $template = str_replace('{IMG_TITLE}', $title, $template);
    $template = str_replace('{IMG_FILENAME}', $img_filename, $template);
    $template = str_replace('{IMG_DOMAIN}', $domain, $template);
    $template = str_replace('{IMG_LINK}', $imglink, $template);
    return $template;
}

public function post_title_shortcode(){
    global $post;
    return get_the_title();
}
public function download_image(){
    $kw = $_POST['kw'];
    $imgsrc = $_POST['imgsrc'];
    $post_id = $_POST['id'];
    $idx = $_POST['idx'];
    $imgtitle =$_POST['imgtitle'];
    $domain = $_POST['domain'];
    $imglink = $_POST['imglink'];

    $att_title = self::get_att_title_template($kw, $imgtitle, $imgsrc);
    $filename_template = self::get_filename_template($kw, $imgtitle, $imgsrc);
    $caption_template = self::get_caption_template($kw, $imgtitle, $imgsrc, $domain, $imglink);
    $description_template = self::get_description_template($kw, $imgtitle, $imgsrc, $domain, $imglink);

    require_once 'spintax.php';
    $spin = new Spintax();

    $att_title = $spin->process($att_title);
    $filename_spinned = $spin->process($filename_template);
    $caption_spinned = $spin->process($caption_template);
    $description_spinned = $spin->process($description_template);

    if (get_option("save_image")==1) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
     
        // URL to the image from remote server.
        $url = preg_replace('/\?.*/', '', "$imgsrc");
        $timeout_seconds = 30;
         
        // Download file to temp dir.
        $temp_file = download_url( $url, $timeout_seconds );
         //print_r($temp_file);
        if ( ! is_wp_error( $temp_file ) ) {
         
            // Array based on $_FILE as seen in PHP file uploads.
            
            if (get_option("save_image_as")=="keyword"){
                $ext = pathinfo($url, PATHINFO_EXTENSION);
                $filename = $kw."-".$idx.".".$ext;
            } elseif (get_option("save_image_as")=="origin") {
                $filename = basename($url);
            } else {
                $ext = pathinfo($url, PATHINFO_EXTENSION);
                $filename = $filename_spinned.".".$ext;
            }
    
            $file = array(
                //'name'     => basename($url), // ex: wp-header-logo.png
                'name'     => $filename,
                //'type'     => 'image/png',
                'tmp_name' => $temp_file,
                'error'    => 0,
                'size'     => filesize( $temp_file ),
            );
         
            $overrides = array(
                'test_form' => false,
                'test_size' => true,
                'test_upload' => true, 
            );
         
            // Move the temporary file into the uploads directory.
            $results = wp_handle_sideload( $file, $overrides );
            //print_r($results);
            if ( ! empty( $results['error'] ) ) {
                // Insert any error handling here.
                echo json_encode(array("status"=>0,"desc"=>"Fail to move to uploads directory","idx"=>$ext) );
            } else {
                $filename  = $results['file']; // Full path to the file.
                $local_url = $results['url'];  // URL to the file in the uploads dir.
                $type      = $results['type']; // MIME type of the file.
         
                // Perform any actions here based in the above results.
    
                $post_data = array(
                    'post_mime_type' => $type,
                    'guid'           => $local_url, 
                    'post_title'     => $att_title,
                    'post_excerpt'   => $caption_spinned,
                    'post_content'   => $description_spinned,
                );
                $attach_id = wp_insert_attachment( $post_data, $filename, $post_id);
                
                if ($attach_id){
                    update_post_meta($attach_id, '_wp_attachment_image_alt', $imgtitle);
                    if (get_option("save_mode")==0){
                        wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $filename ) );
                    }
                    echo json_encode(array("status"=>1,"desc"=>"Success attached to post","id"=>$attach_id, "img_src"=>wp_get_attachment_image_src($attach_id, "full"), "idx"=>$idx ) );
                } else {
                echo json_encode(array("status"=>2, "desc"=>"Success move the temporary file into the uploads directory", "idx"=>$idx) );
                    }
            }
        }
        else {
            echo json_encode(array("status"=>3,"desc"=>"Fail to download", "idx"=>$idx) );
        }
    }
    else {
     echo json_encode(array("status"=>1,"desc"=>"Success attached to post","id"=>0, "img_src"=>$imgsrc, "idx"=>$idx ) );
    }

   
    die();
}
public function create_post(){
	 ini_set('max_execution_time', 300);
        $params = $_POST;
        $title = trim($params['bulk_post_title']);
        //$title = $kw;
        $key = $params['current'];
        $total = $params['total'];
        $post_status = $_POST['bulk_post_status'];
        $base_date = mktime(0, 0, 0, (int)$params['date_month'], (int)$params['date_day'], (int)$params['date_year']);
       $post_interval = '+' . ($params['interval_num'] * $key) . ' ' . $params['interval_type'];
        $post_time = strtotime($post_interval, $base_date);
        $post_time = date('Y-m-d H:i:s', $post_time);
        //echo $title;
       include_once('fetchimage.php');
        $fetchimage = new FetchImage();
        

	 	$post_tag = $fetchimage->getKeywordSuggestionsFromGoogle($title);
	// 	//echo $judul."<br/>";
		$new_draft_post = array(
						'post_title' => $title,
						'post_content' => '',
						'post_category' => array($params['category']), 
						'post_status' =>$post_status,
						'tags_input' => $post_tag,
						'post_date' => $post_time,
						);
        $new_draft_id =  wp_insert_post($new_draft_post);
       // echo $new_draft_id;
        if ($new_draft_id) {
            update_post_meta($new_draft_id, 'success_posted_field', $title);
            if (get_option("search_engine") == "bing"){
                $images = $fetchimage->get_bing_image($title, 50, get_option('target_site'), get_option("exclude_site"), get_option("img_size"), get_option("image_license"));
            } else {
                $images = $fetchimage->get_google_image2($title);
            }
                $i=0; 
                foreach($images as $image){
                    if ($i++ > get_option('num_image')) break;
                    $imgsrcs[] = $image['mediaurl'];
                    $imgtitle[] = $image['title'];
                    $imgthumb[] = $image['thumbnail'];
                    $imglink[] = $image['link'];
                    $host[] = parse_url($image['link'], PHP_URL_HOST);
                }
            echo json_encode(array("imgsrc"=>$imgsrcs, "imgtitle"=>$imgtitle, "id"=>$new_draft_id,"kw"=>$title, "imgthumb"=>$imgthumb, "imglink"=>$imglink, "host"=>$host, "num_img"=>get_option('num_image')));
            //print_r($data);
        }
        die();
        //return true;	
	}
public function attach_image_to_post(){
    $imgsrc = $_POST['imgsrc'];
    $new_draft_id = $_POST['id'];
    $kw = $_POST['kw'];
    $idx = $_POST['idx'];
    $wp_upload_dir = wp_upload_dir();
    $wp_filetype = wp_check_filetype(basename($imgsrc), null);
    $desc = null;
    $imgtitle = $_POST['imgtitle'];
    $file = array();

    if(get_option('save_image_as')=='keyword') {// save as kw
        $file['name'] = $kw.".jpg";
    }else{
        $file['name'] = basename($imgsrc);
    }
        $file['tmp_name'] = download_url($imgsrc);
        if (is_wp_error($file['tmp_name'])):
            @unlink($file['tmp_name']);
            return NULL;
        endif;
    $post_data = array(
        'post_mime_type' => $wp_filetype['type'],
        'guid'           => $wp_upload_dir['url'] .$imgsrc, 
        'post_title'     => basename($imgsrc),
        'post_content'   => "",
    );
    $attach_id = media_handle_sideload($file, $new_draft_id, $desc, $post_data);
    update_post_meta($attach_id, '_wp_attachment_image_alt', $kw);
    $attach_data = wp_generate_attachment_metadata( $attach_id,  get_attached_file($attach_id));
    wp_update_attachment_metadata( $attach_id,  $attach_data );
    $data = json_encode(array("id"=>$attach_id, "imgtitle"=>$imgtitle) );
    print_r($data);
    
    die();
}
public function one_grab(){
    $title = $_POST['kw'];
    $id = $_POST['id'];
    include_once('fetchimage.php');
	$fetchimage = new FetchImage();
    if (get_option("search_engine") == "bing"){
        $images = $fetchimage->get_bing_image($title, 50, get_option('target_site'), get_option("exclude_site"), get_option("img_size"), get_option("image_license"));
    } else {
        $images = $fetchimage->get_google_image2($title);
    }
        $i=0; 
        foreach($images as $image){
            if ($i++ > get_option('num_image')) break;
            $imgsrcs[] = $image['mediaurl'];
            $imgtitle[] = $image['title'];
            $imgthumb[] = $image['thumbnail'];
            $imglink[] = $image['link'];
            $host[] = parse_url($image['link'], PHP_URL_HOST);
        }
    echo json_encode(array("imgsrc"=>$imgsrcs, "imgtitle"=>$imgtitle, "id"=>$id,"kw"=>$title, "imgthumb"=>$imgthumb, "imglink"=>$imglink, "host"=>$host, "num_img"=>get_option('num_image')));

    die();
}
public function zero_image_posted(){
    $id = $_POST['id'];
    update_post_meta($id, '_few_images',  0);
    echo "saved";
    die();
}
public function count_image_posted(){
    $id = $_POST['id'];
    $kw = $_POST['kw'];
    $imgsrcs = $_POST["imgsrc"];
    $imgtitles =$_POST["imgtitle"];
    $template = get_option("post_template");
    include_once('fetchimage.php');
	$fetchimage = new FetchImage();
    $article = $fetchimage->get_article($kw);
    if ($template==""){
        $template = '{We have collected all our best {POST_KEYWORD} in one place.| You have just found the right place about {POST_KEYWORD}.} 
        These are our {images|photos|gallery|pictures} collection about <b>{POST_KEYWORD}</b>.
        <img src="{IMG_SRC_4}" tag="{IMG_TITLE_4}">
         {IMG_TITLE_4}
        <img src="{IMG_SRC_2}" tag="{IMG_TITLE_2}">
        {IMG_TITLE_2}
        <img src="{IMG_SRC_3}" tag="{IMG_TITLE_3}">
        {IMG_TITLE_3}
        You can explore more about {POST_CATEGORY} on this site. I hope you will be inspired about <a href="{PERMALINK}">{POST_KEYWORD}</a>.';
    }
    $args1 = array(
        'post_type'=>'attachment',
        'posts_per_page'=>-1,
        'post_parent'=>$id	
        );
    $allphotos = get_posts($args1);
    $count = count($allphotos);
    if (!$imgsrcs){
        $count = 0;
    }
    if ($count<4){
        update_post_meta($id, '_few_images',  $count);
    } else {
        delete_post_meta($id, '_few_images');
    }
   
    //print_r($data);
    $i=1;
    if (get_option("save_image")==1){
        foreach($allphotos as $allphoto){
            $img_src = wp_get_attachment_image_src($allphoto->ID, "full");
            $image = get_post($allphoto->ID);
            $template = str_replace("{IMG_SRC_".$i."}", $img_src[0], $template);
            $template = str_replace("{IMG_".$i."}", "<img src='".$img_src[0]."' alt='{IMG_TITLE_".$i."}'><p class='wp-caption-text'>".$image->post_excerpt."</p>", $template);
            $template = str_replace("{IMG_TITLE_".$i."}", get_the_title($allphoto->ID), $template);
            $template = str_replace("{IMG_CAPTION_".$i."}", "<p class='wp-caption-text'>".$image->post_excerpt."</p>", $template);
            $img .= "<img src='".$img_src[0]."' alt='".$image->post_title."'><p class='wp-caption-text'>".$image->post_excerpt."</p>";
            $i++;
        }
    } else {
        foreach($imgsrcs as $imgsrc){
            $template = str_replace("{IMG_".$i."}", "<img src='".$imgsrcs[$i-1]."' alt='".$imgtitles[$i-1]."'>", $template);
            $template = str_replace("{IMG_SRC_".$i."}", $imgsrcs[$i-1], $template);
            $template = str_replace("{IMG_TITLE_".$i."}", $imgtitles[$i-1], $template);
            $img .= "<img src='".$imgsrcs[$i-1]."' alt='".$imgtitles[$i-1]."'><p class='wp-caption-text'>".$imgtitles[$i-1]."</p>";
            $i++;
        }
    }
        $template = str_replace("{IMG}", $img, $template);
        $template = str_replace("{POST_KEYWORD}", get_post_meta($id, "success_posted_field", true), $template);
        $template = str_replace("{PERMALINK}", get_the_permalink($id), $template);
        $template = str_replace("{POST_TITLE}", get_the_title($id), $template);
        $template = str_replace("{POST_CATEGORY}", get_the_category( $id )[0]->name, $template);
        $template = str_replace("{GALLERY}", "[gallery orderby='rand'  size='medium' columns='4' link='post']", $template);
        $template = str_replace("{ARTICLE}", $article, $template);

    require_once 'spintax.php';
    $spin = new Spintax();
    $template = $spin->process($template);
    
    $post_title = self::get_title_template(get_post_meta($id, "success_posted_field", true), $id);
    $post_title = $spin->process($post_title);

    $my_post = array( 
        'ID'           => $id,
        'post_title'    =>$post_title,
        'post_content' => $template,
    );
    wp_update_post( $my_post );
    
    
    echo json_encode(array("count"=>$count,"link"=>get_the_permalink($id), "template"=>$template ) );

    die();
}
public function bulk_grab(){
    $id = $_POST['id'];
    $kws = $_POST['element'];
    $idxs = $_POST['idx'];
    include_once('fetchimage.php');
    $fetchimage = new FetchImage();
    
    $title = get_post_meta($id, "success_posted_field", true);
    if (get_option("search_engine") == "bing"){
        $images = $fetchimage->get_bing_image($title, 50, get_option('target_site'), get_option("exclude_site"), get_option("img_size"), get_option("image_license"));
    } else {
        $images = $fetchimage->get_google_image2($title);
    }
        $i=0; 
        foreach($images as $image){
            if ($i++ > get_option('num_image')) break;
            $imgsrcs[] = $image['mediaurl'];
            $imgtitle[] = $image['title'];
            $imgthumb[] = $image['thumbnail'];
            $imglink[] = $image['link'];
            $host[] = parse_url($image['link'], PHP_URL_HOST);
        }
    echo json_encode(array("imgsrc"=>$imgsrcs, "imgtitle"=>$imgtitle, "id"=>$id,"kw"=>$title, "imgthumb"=>$imgthumb, "imglink"=>$imglink, "host"=>$host, "num_img"=>get_option('num_image')));
    //print_r($bing_images);
    //echo $idx;
    die();
}
public function bulk_delete_image(){
    $ids = $_POST['ids'];
    $i = 0;
    foreach ($ids as $id) {
        $postids = self::count_img_number($id);// get all att id from this post
        foreach ($postids as $postid){
         wp_delete_attachment($postid->ID,"true");
        $i++;
        }
    }
    echo "<p>".$i." images has been deleted from ".count($ids)." post</p>";
    die();
}
public function bulk_delete_post(){
    $ids = $_POST['ids'];
    $i = 0;
    foreach ($ids as $id) {
        wp_delete_post($id,"true");
        $i++;
        }
    echo "<p>".$i." posts has been deleted</p>";
    die();
}
public function bulk_delete_post_and_image(){
    $ids = $_POST['ids'];
    $j = 0;
    foreach ($ids as $id) {
        $postids = self::count_img_number($id);// get all att id from this post
        foreach ($postids as $postid){
            wp_delete_attachment($postid->ID,"true");
            $j++;
            }
        }
    $i = 0;
    foreach ($ids as $id) {
         wp_delete_post($id,"true");
        $i++;
        }
    echo "<p>".$i." posts and ".$j." images has been deleted</p>";
 
    die();
}
public function get_posts(){
    $post_id = $_POST['id'];
    echo get_post_meta($post_id, "success_posted_field", true);
    die();
	return true;
}
static public function quantum_cron() {
    include_once('fetchimage.php');
    $fetchimage = new FetchImage();
    require_once 'spintax.php';
    $spin = new Spintax();
    
    // $ls_kw = get_option('sip_saved_kw');
    $idx = get_option('sip_kw_index');
    $post_status = get_option('cron_post_status');
    $post_category = get_option('cron_category');
    $isKWLoop = get_option('sip_cron_kw_loop');
    $base_date = mktime(0, 0, 0, (int)get_option('date_month'), (int)get_option('date_day'), (int)get_option('date_year'));
    $post_interval = '+' . (get_option('interval_num') * $idx) . ' ' . get_option('interval_type');
    $post_time = strtotime($post_interval, $base_date);
    $post_time = date('Y-m-d H:i:s', $post_time);

    $arr = explode("\n", get_option('sip_saved_kw'));
    if ($idx == "" || $idx > count($arr) - 1 && $isKWLoop == 1) {
             $idx = 0;
         }
         if (count($arr) > $idx) {
             $kw = $arr[$idx];
             $article = $fetchimage->get_article($kw);
             //$kw = str_replace(" ","-", $kw);
             update_option('sip_kw_index', ($idx + 1));
             if ($kw != "") {
                $post_tag = $fetchimage->getKeywordSuggestionsFromGoogle($kw);
                $new_draft_post = array(
                        'post_title' => $kw,
                        'post_content' => "",
                        'post_category' => array($post_category),
                        'post_status' =>$post_status,
                        'tags_input' => $post_tag,
                        'post_date' => $post_time,
                        );
                $post_id = wp_insert_post($new_draft_post);
                update_post_meta($post_id, 'success_posted_field', $kw);
                
                $bing_images = $fetchimage->get_bing_image($kw, 13, get_option("target_site"), get_option("exclude_site"), get_option("img_size"), get_option("image_license"));
                $i=0; 
                foreach($bing_images as $bing_image){
                    if ($i++ > get_option('num_image')) break;
                    $imgsrcs[] = $bing_image['mediaurl'];
                    $imgtitle[] = $bing_image['title'];
                    $imgthumb[] = $bing_image['thumbnail'];
                    $imglink[] = $bing_image['link'];
                    $host[] = parse_url($bing_image['link'], PHP_URL_HOST);

                }
                $results = array("imgsrc"=>$imgsrcs, "imgtitle"=>$imgtitle, "id"=>$new_draft_id,"kw"=>$title, "imgthumb"=>$imgthumb, "imglink"=>$imglink, "host"=>$host);
                if (get_option("save_image")==1) {
                $j = 0;
                foreach($results['imgsrc'] as $imgsrc){
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                    $url = preg_replace('/\?.*/', '', "$imgsrc");
                    $timeout_seconds = 20;
                    $temp_file = download_url( $url, $timeout_seconds );
                    
                    if ( ! is_wp_error( $temp_file ) ) {
                        //$j++;
                        
                        if (get_option("save_image_as")=="keyword"){
                            $ext = pathinfo($url, PATHINFO_EXTENSION);
                            $filename = $kw.".jpeg";
                        } elseif (get_option("save_image_as")=="origin") {
                             $filename = basename($url);
                             $filename = str_replace(".jpg","", $filename);
                             $filename = str_replace(".jpeg","", $filename);
                             $filename = str_replace(".png", "", $filename);
                             $filename = $filename.".jpeg";
                        //$filename = "apalah.jpeg";
                        } else {
                            $ext = pathinfo($url, PATHINFO_EXTENSION);
                            $filename_template = self::get_filename_template($kw, $imgtitle[$j], $imgsrcs[$j]);
                            $filename_spinned = $spin->process($filename_template);
                            $filename_spinned = str_replace(" ","-", $filename_spinned);
                            $filename = $filename_spinned.".jpeg";
                        }
                    }
                    $file = array(
                        //'name'     => basename($url), // ex: wp-header-logo.png
                        'name'     => $filename,
                        //'name'      => 'nama-file.jpeg',
                        'type'     => 'image/jpeg',
                        'tmp_name' => $temp_file,
                        'error'    => 0,
                        'size'     => filesize( $temp_file ),
                    );
                 
                    $overrides = array(
                        'test_form' => false,
                        'test_size' => true,
                        'test_upload' => true, 
                    );
                 
                    // Move the temporary file into the uploads directory.
                    $results = wp_handle_sideload( $file, $overrides );
                    $filename  = $results['file']; // Full path to the file.
                    $local_url = $results['url'];  // URL to the file in the uploads dir.
                    $type      = $results['type']; // MIME type of the file.
             
                    // Perform any actions here based in the above results.
        
                    $post_data = array(
                        'post_mime_type' => $type,
                        'guid'           => $local_url, 
                        'post_title'     => $imgtitle[$j],
                        'post_content'   => basename($url),
                    );
                    $attach_id = wp_insert_attachment( $post_data, $filename, $post_id );
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );
                    if (get_option("save_mode")==0){
                        wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $filename ) );
                    }
                    $j++;
                }
            }
                //proses template to update the content
                $id = $post_id;
                $template = get_option("post_template");
                if ($template==""){
                    $template = '{We have collected all our best {POST_KEYWORD} in one place.| You have just found the right place about {POST_KEYWORD}.} 
                    These are our {images|photos|gallery|pictures} collection about <b>{POST_KEYWORD}</b>.
                    <img src="{IMG_SRC_4}" tag="{IMG_TITLE_4}">
                     {IMG_TITLE_4}
                    <img src="{IMG_SRC_2}" tag="{IMG_TITLE_2}">
                    {IMG_TITLE_2}
                    <img src="{IMG_SRC_3}" tag="{IMG_TITLE_3}">
                    {IMG_TITLE_3}
                    You can explore more about {POST_CATEGORY} on this site. I hope you will be inspired about <a href="{PERMALINK}">{POST_KEYWORD}</a>.';
                }
                $args1 = array(
                    'post_type'=>'attachment',
                    'posts_per_page'=>-1,
                    'post_parent'=>$id	
                    );
                $allphotos = get_posts($args1);
                $count = count($allphotos);
                //print_r($data);
                $k=1;
                if (get_option("save_image")==1){
                    foreach($allphotos as $allphoto){
                        $img_src = wp_get_attachment_image_src($allphoto->ID, "full");
                        $image = get_post($allphoto->ID);
                        $template = str_replace("{IMG_SRC_".$k."}", $img_src[0], $template);
                        $template = str_replace("{IMG_".$k."}", "<img src='".$img_src[0]."' alt='{IMG_TITLE_".$k."}'><p class='wp-caption-text'>".$image->post_excerpt."</p>", $template);
                        $template = str_replace("{IMG_TITLE_".$k."}", get_the_title($allphoto->ID), $template);
                        $template = str_replace("{IMG_CAPTION_".$k."}", "<p class='wp-caption-text'>".$image->post_excerpt."</p>", $template);
                        $k++;
                    }
                } else {
                    foreach($imgsrcs as $imgsrc){
                        $template = str_replace("{IMG_".$k."}", "<img src='".$imgsrcs[$k-1]."' alt='".$imgtitle[$k-1]."'>", $template);
                        $template = str_replace("{IMG_SRC_".$k."}", $imgsrcs[$k-1], $template);
                        $template = str_replace("{IMG_TITLE_".$k."}", $imgtitle[$k-1], $template);
                        $k++;
                    }
                    
                }
                    $template = str_replace("{POST_KEYWORD}", get_post_meta($id, "success_posted_field", true), $template);
                    $template = str_replace("{PERMALINK}", get_the_permalink($id), $template);
                    $template = str_replace("{POST_TITLE}", get_the_title($id), $template);
                    $template = str_replace("{POST_CATEGORY}", get_the_category( $id )[0]->name, $template);
                    $template = str_replace("{GALLERY}", "[gallery orderby='rand'  size='medium' columns='4' link='post']", $template);
                    $template = str_replace("{ARTICLE}", $article, $template);
            
           
                $template = $spin->process($template);
                
                $post_title = self::get_title_template(get_post_meta($id, "success_posted_field", true), $id);
                $post_title = $spin->process($post_title);

                $my_post = array( 
                    'ID'           => $post_id,
                    'post_title'    =>$post_title,
                    'post_content' => $template,
                );
                wp_update_post( $my_post );

            }
         } else {
             echo "End of Keyword. Cron mandek";
         }
        // wp_die();
    }
	function save_kw() {
        $additional_kw = $_POST['add_kw'];
        $additional_kw_arr = explode("\n", $additional_kw);
        $existed_kw = get_option("sip_saved_kw");
        $existed_kw_arr = explode("\n", $existed_kw);
        $merge_kw = array_merge($additional_kw_arr, $existed_kw_arr);
        $result = implode("\n", $merge_kw);
        if ($existed_kw){
        update_option("sip_saved_kw", $result);
        } else {
            update_option("sip_saved_kw", $additional_kw);
        }
        //update_option("cron_category", $_POST['category']);
        update_option('sip_kw_index', 0);
        echo "Keywords Saved";
        die();
		return true;
    }
    function delete_kw(){
        update_option("sip_saved_kw","");
        echo "Keywords has been deleted";
        die();
    }
    function save_template() {
        update_option("post_template", stripslashes($_POST['post_template']));
        update_option("title_template", $_POST['title_template']);
        update_option("attachment_title_template", $_POST['attachment_title_template']);
        update_option("attachment_filename_template", $_POST['attachment_filename_template']);
        update_option("attachment_caption_template", stripslashes($_POST['attachment_caption_template']));
        update_option("attachment_description_template", stripslashes($_POST['attachment_description_template']));
        
        echo "Template Saved";
        die();
		return true;
    }
    function save_settings() {
        update_option("search_engine", $_POST['search_engine']);
        update_option("num_image", $_POST['num_image']);
        update_option("img_size", $_POST['img_size']);
        update_option("save_image", $_POST['save_image']);
        update_option("save_mode", $_POST['save_mode']);
        update_option("reset_img_metadata", $_POST['reset_img_metadata']);
        update_option("save_image_as", $_POST['save_image_as']);
        update_option("image_license", $_POST['image_license']);
        update_option("target_site", $_POST['target_site']);
        update_option("exclude_site", $_POST['exclude_site']);
        update_option("cron_category", $_POST['cron_category']);
        update_option("date_day", $_POST['date_day']);
        update_option("date_month", $_POST['date_month']);
        update_option("date_year", $_POST['date_year']);
        update_option("interval_num", $_POST['interval_num']);
        update_option("interval_type", $_POST['interval_type']);
        update_option("cron_post_status", $_POST['cron_post_status']);
        update_option("sip_cron_kw_loop", $_POST['sip_cron_kw_loop']);
        // if (isset($_POST['reset_img_metadata'])) update_option("reset_img_metadata", 1);
        // else update_option("reset_img_metadata", 0);
        echo "Saved";
        die();
		return true;
    }
	  function show_year() {
        $cur_year = date("Y");
        $str = '<select name="date[year]" id="date_year">';
        FOR ($currentMonth = $cur_year + 2;$currentMonth >= $cur_year - 2;$currentMonth--) {
            $str.= "<OPTION VALUE=\"";
            $str.= INTVAL($currentMonth);
            $str.= "\"";
            IF ($currentMonth == get_option("date_year")) {
                $str.= " SELECTED";
            }
            $str.= ">" . $currentMonth . "";
        }
        $str.= "</SELECT>";
        return $str;
    }
    function show_month() {
        $monthName = ARRAY(1 => "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        $useDate = TIME();
        $str = '<select name="date[month]" id="date_month">';
        FOR ($currentMonth = 1;$currentMonth <= 12;$currentMonth++) {
            $str.= "<OPTION VALUE=\"";
            $str.= INTVAL($currentMonth);
            $str.= "\"";
            IF (get_option("date_month") == $currentMonth) {
                $str.= " SELECTED";
            }
            $str.= ">" . $monthName[$currentMonth] . "";
        }
        $str.= "</SELECT>";
        return $str;
    }
    function show_day() {
        $useDate = TIME();
        $str = '<select name="date[day]" id="date_day">';
        FOR ($currentDay = 1;$currentDay <= 31;$currentDay++) {
            $str.= " <OPTION VALUE='$currentDay'";
            IF (get_option("date_day") == $currentDay) {
                $str.= " SELECTED";
            }
            $str.= ">$currentDay";
        }
        $str.= "</SELECT>";
        return $str;
    }
    
    function pagination($total, $paged){
        
        $pagination = paginate_links( array(
            'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
            'total'        => $total,
            'current'      => max( 1, $paged ),
            'format'       => '?paged=%#%',
            'show_all'     => false,
            'type'         => 'plain',
            'end_size'     => 2,
            'mid_size'     => 1,
            'prev_next'    => true,
            'prev_text'    => sprintf( '<i class="icon icon-left-open-big"></i> %1$s', __( '', 'text-domain' ) ),
            'next_text'    => sprintf( '%1$s <i class="icon icon-right-open-big"></i>', __( '', 'text-domain' ) ),
            'add_args'     => false,
            'add_fragment' => '',
        ) );
        return $pagination;
    }

    function count_img_number($id) {
        $args1 = array(
            'post_type'=>'attachment',
            'posts_per_page'=>-1,
            'post_parent'=>$id	
            );
        $allphotos = get_posts($args1);
        //$count = count($allphotos);
        return $allphotos;
    }
    function sip_post_status($post){
        global $mode;

		if ( '0000-00-00 00:00:00' === $post->post_date ) {
			$t_time = $h_time = __( 'Unpublished' );
			$time_diff = 0;
		} else {
			$t_time = get_the_time( __( 'Y/m/d g:i:s a' ) );
			$m_time = $post->post_date;
			$time = get_post_time( 'G', true, $post );

			$time_diff = time() - $time;

			if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
				$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
			} else {
				$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
			}
		}

		if ( 'publish' === $post->post_status ) {
			$status = __( 'Published' );
		} elseif ( 'future' === $post->post_status ) {
			if ( $time_diff > 0 ) {
				$status = '<strong class="error-message">' . __( 'Missed schedule' ) . '</strong>';
			} else {
				$status = __( 'Scheduled' );
			}
		} else {
			$status = __( 'Last Modified' );
		}

		/**
		 * Filters the status text of the post.
		 *
		 * @since 4.8.0
		 *
		 * @param string  $status      The status text.
		 * @param WP_Post $post        Post object.
		 * @param string  $column_name The column name.
		 * @param string  $mode        The list display mode ('excerpt' or 'list').
		 */
		$status = apply_filters( 'post_date_column_status', $status, $post, 'date', $mode );

		if ( $status ) {
			echo $status . '<br />';
		}

		if ( 'excerpt' === $mode ) {
			/**
			 * Filters the published time of the post.
			 *
			 * If `$mode` equals 'excerpt', the published time and date are both displayed.
			 * If `$mode` equals 'list' (default), the publish date is displayed, with the
			 * time and date together available as an abbreviation definition.
			 *
			 * @since 2.5.1
			 *
			 * @param string  $t_time      The published time.
			 * @param WP_Post $post        Post object.
			 * @param string  $column_name The column name.
			 * @param string  $mode        The list display mode ('excerpt' or 'list').
			 */
			echo apply_filters( 'post_date_column_time', $t_time, $post, 'date', $mode );
		} else {

			/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
			echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $post, 'date', $mode ) . '</abbr>';
		}
    }
    function get_meta_values( $key = '', $type = 'post' ) {
        global $wpdb;
        if( empty( $key ) )
            return;
        $r = $wpdb->get_col( $wpdb->prepare( "
            SELECT pm.meta_value FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = '%s'
            AND p.post_type = '%s'
        ", $key, $type ) );
        return $r;
    }

    function find_duplicate(){
        $kws = $_POST['kws'];
        $saved_kw = get_option('sip_saved_kw');
        $saved_kw_arr = explode("\n", get_option('sip_saved_kw'));
        $join = array_merge($kws, $saved_kw_arr);
        $uniq_kw = array_unique($join);
        $array_intersect = array_intersect($kws, $join);
        $diff = array_diff($array_intersect, $saved_kw_arr);
        $save_kw = implode("\n", $diff);
        //update_option("sip_saved_kw", $save_kw);
        print_r($save_kw);


        die();
    }

    public function setExtension($array)
	{

		if ( empty($array['file']))
			return false;

		$fileInfo = pathinfo($array['file']);
		$filePath = $fileInfo['dirname'] . '/'.$fileInfo['basename'];
		switch ($fileInfo['extension']) {
			case 'jpg':
				$array['file'] = self::removeExif($filePath, 'jpg');
				break;
			case 'png':
				$array['file'] = self::removeExif($filePath, 'png');
				break;
		}

		return $array;
	}

	private function removeExif($imagePath, $type)
	{
		if (empty($imagePath) || !is_admin())
			return false;

		if ($type == 'jpg')
			$clearExif = imagecreatefromjpeg($imagePath);
		elseif ($type == 'png')
			$clearExif = imagecreatefrompng($imagePath);
		else
			return $imagePath;

		imagejpeg($clearExif, $imagePath, 100);
		imagedestroy($clearExif);

		return $imagePath;
	}

}//end class




$sip = new SimpleImagePoster();

add_action('admin_menu', array('SimpleImagePoster','sip_plugin_menu'));
add_action('wp_ajax_bulk_grab', array('SimpleImagePoster','bulk_grab'));

add_action('wp_ajax_bulk_delete_post', array('SimpleImagePoster','bulk_delete_post'));
add_action('wp_ajax_bulk_delete_image', array('SimpleImagePoster','bulk_delete_image'));
add_action('wp_ajax_bulk_delete_post_and_image', array('SimpleImagePoster','bulk_delete_post_and_image'));
add_action('wp_ajax_count_image_posted', array('SimpleImagePoster','count_image_posted'));
add_action('wp_ajax_zero_image_posted', array('SimpleImagePoster','zero_image_posted'));
add_action('wp_ajax_one_grab', array('SimpleImagePoster','one_grab'));
add_action('wp_ajax_download_image', array('SimpleImagePoster','download_image'));
add_action('wp_ajax_save_kw', array('SimpleImagePoster','save_kw'));
add_action('wp_ajax_delete_kw', array('SimpleImagePoster','delete_kw'));
add_action('wp_ajax_save_template', array('SimpleImagePoster','save_template'));
add_action('wp_ajax_save_settings', array('SimpleImagePoster','save_settings'));
add_action('wp_ajax_create_post', array('SimpleImagePoster','create_post'));
add_action('wp_ajax_get_posts', array('SimpleImagePoster','get_posts'));

add_action('wp_ajax_find_duplicate', array('SimpleImagePoster','find_duplicate'));
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

add_action("wp_head", "remove_width_att");
function remove_width_att(){
    echo "<style>.wp-caption{width:auto !important}.wp-caption img[class*='wp-image-'] {
        width: auto;max-width: initial;
    }</style>";
}

//include_once('license.php');
// $qtm_license = new QTMLicense();
// add_action('admin_menu', $qtm_license->slm_sample_license_menu);