<?php
/* Plugin Name: Instant Connect FinancialInsiders
 * Author: Vinodhagan Thangarajan
 * Author URI: https://github.com/financialinsiders
 * Plugin URI: https://github.com/financialinsiders/Instant-Connect
 * Description: Instant Connect plugin for Neil Thomas
 * Version: 2.0
 */
 
 $dir = pathinfo(__FILE__);
 define('IC_PLUGIN_URL', plugin_dir_url( __FILE__ ));
 define('IC_PLUGIN_DIR',$dir['dirname']);

 define("API_KEY", "45426652");
 define("API_SECRET", "ff71053e07be2f36ec4c1a6f1351fdc340285b81");
 //define("STRIPE_KEY", pmpro_getOption("stripe_secretkey");

  if(!class_exists("Stripe\Stripe"))
 {
	 
 	 require_once( ABSPATH . '/wp-content/plugins/paid-memberships-pro/includes/lib/Stripe/init.php');
 	 //Had to change the stripe location.
	 
 }


 
 include 'includes.php';
 include 'pusher/pusher.php';
 
 global $endorsements, $ntmadmin, $ntm_mail;
 $endorsements = new Instant_Connect();
 //$ntm_mail = new IC_mail_template();
 
 Class Instant_Connect
 {
	function __construct()
	{
		register_activation_hook(__FILE__, array( &$this, 'Endorsement_install'));
		//register_uninstall_hook(__FILE__, array( &$this, 'Endorsement_uninstall'));
		
		function codex_custom_init() {
			$args = array(
			  'public' => true,
			  'label'  => 'Meeting'
			);
			register_post_type( 'meeting', $args );
		}
		add_action( 'wpmu_new_blog', array( &$this, 'on_create_blog' ), 10, 6 );
		add_action( 'init', 'codex_custom_init' );
		add_action( 'init', array( &$this, 'strategy_posttype' ));
		add_action( 'add_meta_boxes', array( &$this, 'ic_strategy_link' ));
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_date_picker' ));
		add_filter( 'page_template', array( &$this, 'wpa3396_page_template' ));
		//add_action('wp_login', array( &$this, 'after_login' ), 10, 2); // We will use firebase fo this purpose

		add_filter('single_template', array( &$this, 'chat_post_template'));
		
		add_action( 'add_meta_boxes', array( &$this, 'ic_meta_boxes' ));
		add_action( 'save_post', array( &$this, 'ic_save_meta_box' ));

		add_shortcode('REGISTER_FORM', array( &$this, 'ic_register_form'));
		add_shortcode('PROFILEIMAGE', array( &$this, 'ic_profile_image'));

		$ntmadmin = new IC_admin();
		new IC_Metabox();
		new IC_ajax();
		new IC_front();
		new IC_agent_api();
	}

	function chat_post_template($single){
		global $post;

	    /* Checks for single template by post type */
	    if ( $post->post_type == 'ic-chat-bot' ) {
	        if ( file_exists( IC_PLUGIN_DIR . '/chat_post_template.php' ) ) {
	            return IC_PLUGIN_DIR . '/chat_post_template.php';
	        }
	    }

	    return $single;
	}

	function ic_profile_image(){
		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');

		$profile_image = get_user_meta($agent_id, 'ic_agent_profile_image', true);
		$profile_image = $profile_image ? $profile_image : plugin_dir_url(__FILE__).'dist/v2/img/neil-avatar.jpg';

		return '<img class="ic_profile_image" src="'.$profile_image.'" />';
	}
	
	function on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        	$clone_from = isset($_GET['clone']) ? $_GET['clone'] : 1;
        	switch_to_blog($clone_from);
        	$ltemplates = get_posts(array('post_type' => 'ictemplate', 'posts_per_page' => -1));
        	$stemplates = get_posts(array('post_type' => 'icstatic', 'posts_per_page' => -1));
        	restore_current_blog();

        	switch_to_blog( $blog_id );
        	

        	
        	



        	$this->Endorsement_install($blog_id, $user_id, $domain, $path, $site_id, $meta);
        	
        	foreach ($ltemplates as $t => $val) {
        		$strategy = array('post_title' => $val->post_title, 'post_type' => 'ictemplate', 'post_status' => 'publish', 'post_author' => $user_id);
				$post_id = wp_insert_post( $strategy);

				update_post_meta($post_id, 'template_html', get_post_meta($post_id, 'template_html', true));
		    	update_post_meta($post_id, 'dynamic_template', get_post_meta($post_id, 'dynamic_template', true));
		    	update_post_meta($post_id, 'template_agents', array(0));
		    	update_post_meta($post_id, 'template_social_fb_text', get_post_meta($post_id, 'template_social_fb_text', true));
		    	update_post_meta($post_id, 'template_social_fb_image', get_post_meta($post_id, 'template_social_fb_image', true));
		    	update_post_meta($post_id, 'template_social_tw_text', get_post_meta($post_id, 'template_social_tw_text', true));
		    	update_post_meta($post_id, 'template_social_tw_image', get_post_meta($post_id, 'template_social_tw_image', true));
		    	update_post_meta($post_id, 'template_social_pi_image', get_post_meta($post_id, 'template_social_pi_image', true));
        	}

        	foreach ($stemplates as $t => $val) {
        		$strategy = array('post_title' => $val->post_title, 'post_type' => 'ictemplate', 'post_status' => 'publish', 'post_author' => $user_id);
				$post_id = wp_insert_post( $strategy);

				update_post_meta($post_id, 'template_html', get_post_meta($post_id, 'template_html', true));
		    	update_post_meta($post_id, 'dynamic_template', get_post_meta($post_id, 'dynamic_template', true));
		    	update_post_meta($post_id, 'template_agents', array(0));
		    	update_post_meta($post_id, 'template_social_fb_text', get_post_meta($post_id, 'template_social_fb_text', true));
		    	update_post_meta($post_id, 'template_social_fb_image', get_post_meta($post_id, 'template_social_fb_image', true));
		    	update_post_meta($post_id, 'template_social_tw_text', get_post_meta($post_id, 'template_social_tw_text', true));
		    	update_post_meta($post_id, 'template_social_tw_image', get_post_meta($post_id, 'template_social_tw_image', true));
		    	update_post_meta($post_id, 'template_social_pi_image', get_post_meta($post_id, 'template_social_pi_image', true));
        	}

        	restore_current_blog();

	}

	function strategy_posttype() {
	$labels = array(
		'name'               => _x( 'Strategy', 'post type general name', 'your-plugin-textdomain' ),
		'singular_name'      => _x( 'Strategy', 'post type singular name', 'your-plugin-textdomain' ),
		'menu_name'          => _x( 'Strategies', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Strategy', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add New', 'strategy', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Strategy', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Strategy', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Strategy', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Strategy', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Strategies', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Strategies', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Strategies:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No Strategies found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No Strategies found in Trash.', 'your-plugin-textdomain' )
	);

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Description.', 'your-plugin-textdomain' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'strategy', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title' )
	);

	register_post_type( 'strategy', $args );

	$labels = array(
		'name'               => _x( 'Template', 'post type general name', 'your-plugin-textdomain' ),
		'singular_name'      => _x( 'Template', 'post type singular name', 'your-plugin-textdomain' ),
		'menu_name'          => _x( 'Templates', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Template', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add New', 'Template', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Template', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Template', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Template', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Template', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Templates', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Templates', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Templates:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No Templates found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No Templates found in Trash.', 'your-plugin-textdomain' )
	);

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Description.', 'your-plugin-textdomain' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'ictemplate', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title' )
	);

	register_post_type( 'ictemplate', $args );

	$labels = array(
		'name'               => _x( 'Static Template', 'post type general name', 'your-plugin-textdomain' ),
		'singular_name'      => _x( 'Static Template', 'post type singular name', 'your-plugin-textdomain' ),
		'menu_name'          => _x( 'Static Templates', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Static Template', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add New', 'Static', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Static', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Static', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Static', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Static', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Templates', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Templates', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Templates:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No Templates found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No Templates found in Trash.', 'your-plugin-textdomain' )
	);

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Description.', 'your-plugin-textdomain' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'icstatic' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title' )
	);

	register_post_type( 'icstatic', $args );

	$labels = array(
		'name'               => _x( 'Chat Bot', 'post type general name', 'your-plugin-textdomain' ),
		'singular_name'      => _x( 'Chat Bot', 'post type singular name', 'your-plugin-textdomain' ),
		'menu_name'          => _x( 'Chat Bot', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Chat Bot', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add New', 'Chat Bot', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Chat Bot', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Chat Bot', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Chat Bot', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Chat Bot', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Chat Bot', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Chat Bot', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Chat Bot:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No Chat Bot found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No Chat Bot found in Trash.', 'your-plugin-textdomain' )
	);

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Description.', 'your-plugin-textdomain' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'ic-chat-bot' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title' )
	);

	register_post_type( 'ic-chat-bot', $args );
}

	function ic_meta_boxes() {
	    add_meta_box( 'ic_meta_boxes', __( 'Instant Connect Settings', 'textdomain' ), array( &$this, 'ic_meta_boxes_callback'), 'page' );
	}

	function ic_meta_boxes_callback( $post ) {
	    
	    $instant_connect_settings = get_post_meta($post->ID, 'instant_connect_settings', true);
	    ?>
	    <p><label>Auto popup on/off</label> <input <?= isset($instant_connect_settings['autopopup']) ? 'checked' : ''?> value="1" type="checkbox" name="instant_connect_settings[autopopup]"></p>

	    <p>
	    	<label>Email input timeout delay(in seconds)</label>
	    	<select name="instant_connect_settings[timeout]">
	    		<option <?= (isset($instant_connect_settings['timeout']) && $instant_connect_settings['timeout'] == 10) ? 'selected' : ''?> value="10">10</option>
	    		<option <?= (isset($instant_connect_settings['timeout']) && $instant_connect_settings['timeout'] == 20) ? 'selected' : ''?> value="20">20</option>
	    		<option <?= (isset($instant_connect_settings['timeout']) && $instant_connect_settings['timeout'] == 30) ? 'selected' : ''?> value="30">30</option>
	    		<option <?= (isset($instant_connect_settings['timeout']) && $instant_connect_settings['timeout'] == 40) ? 'selected' : ''?> value="40">40</option>
	    		<option <?= (isset($instant_connect_settings['timeout']) && $instant_connect_settings['timeout'] == 50) ? 'selected' : ''?> value="50">50</option>
	    		<option <?= (isset($instant_connect_settings['timeout']) && $instant_connect_settings['timeout'] == 60) ? 'selected' : ''?> value="60">60</option>
	    	</select>
	    </p>
	    <p><label>Get name with email</label> <input <?= isset($instant_connect_settings['getname']) ? 'checked' : ''?> value="1" type="checkbox" name="instant_connect_settings[getname]"></p>
	    <div>
	    	<h4>Custom chat message</h4>
	    	<textarea name="instant_connect_settings[message]" cols="60" rows="3"><?= isset($instant_connect_settings['message']) ? $instant_connect_settings['message'] : '';?></textarea>
	    </div>
	    <div>
	    	<h4>Chat welcome message(If Online)</h4>
	    	<textarea name="instant_connect_settings[onmessage]" cols="60" rows="3"><?= isset($instant_connect_settings['onmessage']) ? $instant_connect_settings['onmessage'] : '';?></textarea>
	    </div>
	    <div>
	    	<h4>Chat welcome message(If Offline)</h4>
	    	<textarea name="instant_connect_settings[offmessage]" cols="60" rows="3"><?= isset($instant_connect_settings['offmessage']) ? $instant_connect_settings['offmessage'] : '';?></textarea>
	    </div>
	    <?php
	}

	function ic_strategy_link(){
		add_meta_box( 'ic_meta_boxes', __( 'Strategy link', 'textdomain' ), array( &$this, 'ic_strategy_link_callback'), 'strategy' );

		if(is_super_admin()){
			add_meta_box( 'ic_meta_boxes', __( 'Template option', 'textdomain' ), array( &$this, 'ic_template_link_callback'), 'ictemplate' );
			add_meta_box( 'ic_meta_boxes', __( 'Template option', 'textdomain' ), array( &$this, 'ic_template_link_callback'), 'icstatic' );
		}
	}

	function ic_template_link_callback( $post ) {
	    
	    $dynamic_template = get_post_meta($post->ID, 'dynamic_template', true);
	    $dynamic_template = is_array($dynamic_template) ? $dynamic_template : array() ;
	    $template_html = get_post_meta($post->ID, 'template_html', true);
	    $template_social_fb_text = get_post_meta($post->ID, 'template_social_fb_text', true);
	    $template_social_fb_image = get_post_meta($post->ID, 'template_social_fb_image', true);
	    $template_social_tw_text = get_post_meta($post->ID, 'template_social_tw_text', true);
	    $template_social_tw_image = get_post_meta($post->ID, 'template_social_tw_image', true);
	    $template_social_pi_image = get_post_meta($post->ID, 'template_social_pi_image', true);
	    $template_agents = get_post_meta($post->ID, 'template_agents', true);
	    $sagents = explode(',', $template_agents);
	    ?>
	    <div id="titlediv">
	    	<p>
	    		<label><b>HTML</b></label><br>
	    		<textarea rows="5" cols="90" name="template_html"><?= isset($template_html) ? $template_html : '';?></textarea>
	    	</p>
	    	<script type="text/template" id="ic_dynamic_html_template">
	    		<tr>
    				<td>
    					<b>customtemplate###</b>
    				</td>
    				<td>
    					<select class="ct_type" name="dynamic_template[type][]">
    						<option value="text">Text</option>
    						<option value="image">Image</option>
    						<option value="video">Video</option>
    					</select>
    					
    				</td>
    				<td>
    					<div class="preview_dimension"><b>Preview Dimension</b><br>
	    				<input name="dynamic_template[width][]" size="5">
	    				<input name="dynamic_template[height][]" size="5"></div>
    				</td>
    				<td>
    					<textarea rows="5" cols="40" name="dynamic_template[content][]"></textarea>
    				</td>
    				<td><button class="ic_delete_dynamic_temp">Delete</button></td>
    			</tr>
	    	</script>
	    	<script type="text/javascript">
	    		jQuery(document).ready(function(){
	    			jQuery('#add_more_ic_temp').click(function(e){
	    				e.preventDefault();
	    				var hhh = jQuery('#ic_dynamic_html_template').html().replace('###', jQuery('#ic_dynamic_template tbody tr').length + 1);
	    				jQuery('#ic_dynamic_template tbody').append(hhh);
	    			});
	    			jQuery('body').on('click', '.ic_delete_dynamic_temp', function(e){
	    				e.preventDefault();
	    				jQuery(this).parent().parent().remove();
	    			});
	    			jQuery('body').on('change', '.ct_type', function(e){
	    				var tt = jQuery(this).val();
	    				if(tt == 'text'){
	    					jQuery(this).parent().parent().find('.preview_dimension').hide();
	    				} else {
	    					jQuery(this).parent().parent().find('.preview_dimension').show();
	    				}
	    				
	    			});
	    		});
	    	</script>
	    	<h3>Dynamic Template <button id="add_more_ic_temp">Add</button></h3>
	    	<table id="ic_dynamic_template" width="100%">
	    		<thead>
	    			<tr>
		    			<th width="15%">#</th>
		    			<th width="10%">Type</th>
		    			<th width="25%">Dimension</th>
		    			<th width="45%">Content</th>
		    			<th width="10%">Delete</th>
		    		</tr>
		    	</thead>
	    		<tbody>
	    			<?php  if(isset($dynamic_template['type']) && count($dynamic_template['type'])){ foreach($dynamic_template['type'] as $key=>$value){?>
	    			<tr>
	    				<td>
	    					<b>customtemplate<?= $key + 1;?></b>
	    				</td>
	    				<td>
	    					<select class="ct_type" name="dynamic_template[type][]">
	    						<option <?php echo $dynamic_template['type'][$key] == 'text' ? 'selected' : '';?> value="text">Text</option>
	    						<option <?php echo $dynamic_template['type'][$key] == 'image' ? 'selected' : '';?> value="image">Image</option>
	    						<option <?php echo $dynamic_template['type'][$key] == 'video' ? 'selected' : '';?> value="video">Video</option>
	    					</select>
	    					
	    				</td>
	    				<td>
	    					<div <?php echo $dynamic_template['type'][$key] == 'text' ? 'style="display:none"' : '';?> class="preview_dimension"><b>Preview DImension</b><br>
	    					<input name="dynamic_template[width][]" size="5" value="<?= $dynamic_template['width'][$key];?>">x
	    					<input name="dynamic_template[height][]" size="5" value="<?= $dynamic_template['height'][$key];?>"></div>
	    				</td>
	    				<td>
	    					<textarea rows="5" cols="40" name="dynamic_template[content][]"><?= $dynamic_template['content'][$key];?></textarea>
	    				</td>
	    				<td><button class="ic_delete_dynamic_temp">Delete</button></td>
	    			</tr>
	    			<?php }} else {?>
	    			<tr>
	    				<td>
	    					<b>customtemplate1</b>
	    				</td>
	    				<td>
	    					<select class="ct_type" name="dynamic_template[type][]">
	    						<option value="text">Text</option>
	    						<option value="image">Image</option>
	    						<option value="video">Video</option>
	    					</select>
	    					
	    				</td>
	    				<td>
	    					<div style="display: none;" class="preview_dimension"><b>Preview Dimension</b><br>
		    				<input name="dynamic_template[width][]" size="5">
		    				<input name="dynamic_template[height][]" size="5"></div>
	    				</td>
	    				<td>
	    					<textarea rows="5" cols="40" name="dynamic_template[content][]"></textarea>
	    				</td>
	    				<td><button class="ic_delete_dynamic_temp">Delete</button></td>
	    			</tr>	
	    			<?php }?>
	    		</tbody>
	    	</table>
	    	<p>
	    		<label style="display: inline-block;width:200px;"><b>Facebook Card Text</b></label>
	    		<input size="50" type="text" name="template_social_fb_text" value="<?= isset($template_social_fb_text) ? $template_social_fb_text : '';?>">
	    	</p>
	    	<p>
	    		<label style="display: inline-block;width:200px;"><b>Facebook Card Image</b></label>
	    		<input size="50" type="text" name="template_social_fb_image" value="<?= isset($template_social_fb_image) ? $template_social_fb_image : '';?>">
	    	</p>
	    	<p>
	    		<label style="display: inline-block;width:200px;"><b>Twitter Card Text</b></label>
	    		<input size="50" type="text" name="template_social_tw_text" value="<?= isset($template_social_tw_text) ? $template_social_tw_text : '';?>">
	    	</p>
	    	<p>
	    		<label style="display: inline-block;width:200px;"><b>Twitter Card Text</b></label>
	    		<input size="50" type="text" name="template_social_tw_image" value="<?= isset($template_social_tw_image) ? $template_social_tw_image : '';?>">
	    	</p>
	    	<p>
	    		<label style="display: inline-block;width:200px;"><b>Pinterest Image</b></label>
	    		<input size="50" type="text" name="template_social_pi_image" value="<?= isset($template_social_pi_image) ? $template_social_pi_image : '';?>">
	    	</p>
	    	<p>
	    		<label><b>Choose Agent</b></label><br>
	    		<select name="template_agents[]" multiple>
	    			<option <?= in_array(0, $sagents) ? 'selected' : ''?> value="0">For All</option>
	    			<?php $users = get_users(array('userrole' => 'agent'));
					foreach ($users as $key => $value) {?>
	    			<option <?= in_array($value->ID, $sagents) ? 'selected' : ''?> value="<?= $value->ID?>"><?= $value->user_login?></option>
	    			<?php }?>
	    		</select>
	    	</p>
	    </div>
	    <?php
	}

	function ic_strategy_link_callback( $post ) {
	    
	    $strategy_link = get_post_meta($post->ID, 'strategy_link', true);
	    ?>
	    <div id="titlediv">
	    	<input type="text" id="title" name="strategy_link" value="<?= isset($strategy_link) ? $strategy_link : '';?>">
	    </div>
	    <?php
	}

	function ic_save_meta_box( $post_id ) {
		if(isset($_POST['instant_connect_settings'])){
			update_post_meta($post_id, 'instant_connect_settings', $_POST['instant_connect_settings']);
		}
	    if(isset($_POST['strategy_link'])){
	    	update_post_meta($post_id, 'strategy_link', $_POST['strategy_link']);
	    }
	    if(isset($_POST['template_html'])){
	    	update_post_meta($post_id, 'template_html', $_POST['template_html']);
	    	update_post_meta($post_id, 'dynamic_template', $_POST['dynamic_template']);
	    	update_post_meta($post_id, 'template_agents', implode(',',$_POST['template_agents']));
	    	update_post_meta($post_id, 'template_social_fb_text', $_POST['template_social_fb_text']);
	    	update_post_meta($post_id, 'template_social_fb_image', $_POST['template_social_fb_image']);
	    	update_post_meta($post_id, 'template_social_tw_text', $_POST['template_social_tw_text']);
	    	update_post_meta($post_id, 'template_social_tw_image', $_POST['template_social_tw_image']);
	    	update_post_meta($post_id, 'template_social_pi_image', $_POST['template_social_pi_image']);
	    }
	}

	function after_login($user_login, $user)
	{
		update_user_meta($current_user->ID, 'user_current_status', $_GET['status']);
		update_user_meta($current_user->ID, 'user_logintime', date("Y-m-d H:i:s"));
	}

	function wpa3396_page_template( $page_template )
	{
		if ( is_page( 'meeting' ) && !isset($_GET['waitinghall'])) {
			$version = isset($_GET['version']) ? $_GET['version'] : 2;
			$page_template = dirname( __FILE__ ) . '/meeting_template_v'.$version.'.php';
		}
		return $page_template;
	}
	
	function Endorsement_install()
	{
		

		global $wpdb;
		
		$mailtemplates = $wpdb->prefix . "meeting";
		
		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			   created datetime NOT NULL,
			   title tinytext NOT NULL,
			   session_id text NOT NULL,
			   bot_id int(11),
			   token text NOT NULL,
			   description text NOT NULL,
			   agent_id text NOT NULL,
			   meeting_date datetime NOT NULL,
			   event_id text NOT NULL,
			   timekit_meeting_id text NOT NULL,
			   active_time tinytext NOT NULL,
			   meeting_date_string text,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}
		
		$mailtemplates = $wpdb->prefix . "meeting_participants";
		
		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			   meeting_id int(11),
			   name tinytext NOT NULL,
			   email tinytext NOT NULL,
			   question text NOT NULL,
			   meeting_date datetime NOT NULL,
			   status int(1),
			   video int(1),
			   whiteboard int(1),
			   lead int(11),
			   endorser int(11),
			   gift_status int(1),
			   mode int(1),
			   is_mobile int(1),
			   complete_device_name tinytext NOT NULL,
			   form_factor tinytext NOT NULL,
			   ip_address tinytext NOT NULL,
			   chat_conversion int(1),
			   meeting_conversion int(1),
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}
		
		$mailtemplates = $wpdb->prefix . "meeting_presentations";
		
		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			   name text,
			   file text,
			   url text,
			   default_presentation int(1),
			   status int(11),
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = $wpdb->prefix . "points_transaction";
		
		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			   endorser_id int(11),
			   agent_id int(11),
			   points int(11),
			   notes text,
			   queue int(1),
			   type tinytext NOT NULL,
			   created datetime,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = $wpdb->prefix . "campaigns";
		
		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			   title tinytext NOT NULL,
			   facebook tinytext NOT NULL,
			   twitter tinytext NOT NULL,
			   linkedin tinytext NOT NULL,
			   gplus tinytext NOT NULL,
			   type tinytext NOT NULL,
			   is_default int(1) DEFAULT 0,
			   is_main_site boolean,
			   strategy int(11),
			   created datetime,
			   landing_page text,
			   completed int(1) DEFAULT 0,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = $wpdb->prefix . "campaign_templates";
		
		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			   campaign_id int(11),
			   template text,
			   name tinytext NOT NULL,
			   media tinytext NOT NULL,
			   subject tinytext NOT NULL,
			   preheader_text text,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = $wpdb->prefix . "video_library";
		
		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			   agent_id int(11),
			   agent_status int(1),
			   file_name tinytext NOT NULL,
			   name tinytext NOT NULL,
			   file_url tinytext NOT NULL,
			   file_thumb tinytext NOT NULL,
			   template_type tinytext NOT NULL,
			   created datetime,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = $wpdb->prefix . "video_message";
		
		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			   video_id int(11),
			   status_message text,
			   message_type tinytext NOT NULL,
			   created datetime,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = "agent_billing";

		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			  agent_id int(11),
			   particulars tinytext NOT NULL,
			   amount float,
			   credit int(1),
			   created datetime,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = "tmp_user";

		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			  agent_id int(11),
			   firstname tinytext NOT NULL,
			   lastname tinytext NOT NULL,
			   email tinytext NOT NULL,
			   status int(1),
			   created datetime,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = "predefined_notes";

		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			  agent_id int(11),
			  campaign_id int(11),
			  name longtext NOT NULL,
			  content tinytext NOT NULL,
			  status int(1),
			  created datetime,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = $wpdb->prefix ."notes";

		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			  agent_id int(11),
			  lead_id int(11),
			  endorser_id int(11),
			  notes tinytext NOT NULL,
			  events tinytext NOT NULL,
			  created datetime,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = $wpdb->prefix ."agent_wallet";

		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			  agent_id int(11),
			  transaction_id int(11),
			  points int(11),
			  balance int(11),
			  notes text,
			  endorser_id int(11),
			  amount float,
			  created datetime,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = $wpdb->prefix ."chat_category";

		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			  agent_id int(11),
			  notes tinytext NOT NULL,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = $wpdb->prefix ."chat_bot";

		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			  title tinytext NOT NULL,
			  category_id int(11),
			  created datetime,
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = "wp_short_link";

		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			  link tinytext NOT NULL,
			  params text,
			  endorser_id int(11) NOT NULL,
			  agent_id int(11) NOT NULL,
			  ts datetime NOT NULL,
			  parent_id int(11) NOT NULL,
			  type varchar(20) NOT NULL,
			  email varchar(250) NOT NULL,
			  email_status varchar(20) NOT NULL,
			  invite_type varchar(50) NOT NULL,
			  session_id varchar(20) NOT NULL
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = "wp_agent_message";

		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			  endorser_id int(11) NOT NULL,
			  agent_id int(11) NOT NULL,
			  ts datetime NOT NULL,
			  message TEXT,
			  video TEXT 
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$mailtemplates = $wpdb->prefix ."chat_bot_data";

		if($wpdb->get_var('SHOW TABLES LIKE ' . $mailtemplates) != $mailtemplates){
			$sql_one = "CREATE TABLE " . $mailtemplates . "(
			  id int(11) NOT NULL AUTO_INCREMENT,
			  chat_id int(11),
			  parent int(11),
			  coption int(1),
			  clabel text,
			  elements text,
			  data text,
			  back int(1),
			  skip int(1),
			  avatar int(1),
			  avatar_image tinytext NOT NULL,
			  userinput tinytext NOT NULL,
			  opt tinytext NOT NULL,
			  ref tinytext NOT NULL,
			  reflabel tinytext NOT NULL,
			  labelref tinytext NOT NULL,
			  inputref tinytext NOT NULL,
			  action tinytext NOT NULL,
			  image tinytext NOT NULL,
			  video tinytext NOT NULL,
			  videotype tinytext NOT NULL,
			  validation_pattern tinytext NOT NULL,
			  validation_msg tinytext NOT NULL,
			  type tinytext NOT NULL,
			  refid tinytext NOT NULL,
			  drag_order int(1),
			  PRIMARY KEY  (id) ) ENGINE=InnoDB";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_one);
		}

		$blog_id = get_current_blog_id();
		$check_already_exist = !get_blog_option($blog_id, 'strategy_link_created');

		$clone_from = isset($_GET['clone']) ? $_GET['clone'] : 1;

		if($check_already_exist){
			$strategy = array('post_title' => 'Online Consultation', 'post_content' => '', 'post_type' => 'strategy', 'post_status' => 'publish');
			$sid = wp_insert_post( $strategy);
			update_post_meta($sid, 'strategy_link', site_url());
			add_blog_option($blog_id, 'strategy_link_created', 1 );

			$results = $wpdb->get_results("select * from wp_campaigns");
			foreach ($results as $key => $value) {
				$value = (array) $value;

				$templates = $wpdb->get_results("select * from wp_campaign_templates where campaign_id=".$value['id']);
				foreach ($templates as $key => $value2) {
					$value2 = (array) $value2;

					unset($value['id']);
					$wpdb->insert("wp_campaigns", $value);
					$value2['campaign_id'] = $wpdb->insert_id;
					$wpdb->insert("wp_campaign_templates", $value2);
				}
			}

			$default_site_settings = ['points_per_dollar', 'admin_fee', 'twitter_text', 'endorser_app', 'mail_template_css', 'cloudsponge', 'sendgrid', 'giftbit'];

			foreach ($default_site_settings as $key => $value) {
				add_blog_option($blog_id, $value, get_blog_option($clone_from, $value));
			}
	        
			if($clone_from != 1){
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				$campaigns = 'Insert in to wp_'.$blog_id.'_campaigns select * from wp_'.$clone_from.'_campaigns';
				dbDelta($campaigns);

				$campaign_templates = 'Insert in to wp_'.$blog_id.'_campaign_templates select * from wp_'.$clone_from.'_campaign_templates';
				dbDelta($campaign_templates);

			}
		}
	}
	
	function Endorsement_uninstall()
	{
		
	}
	
	function Endorsement_menu()
	{
		
	}
	
	function Endorsement_frontend()
	{
		$ntm_front_end = new NTM_Frontend();
		
		return $ntm_front_end->frontend();
	}
	
	function Endorsement_load_js_and_css()
	{
		
	}
	
	function enqueue_date_picker(){
                wp_enqueue_script(
				'field-date-js', 
				'Field_Date.js', 
				array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'),
				time(),
				true
			);	

			wp_enqueue_style( 'jquery-ui-datepicker' );
	}

	function get_endorser_points($user_id){
		global $wpdb;

		$blog_id = get_active_blog_for_user( $user_id )->blog_id;

		$results = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 0 and endorser_id='".$user_id."'");

		$endorser_points = $results->points ? $results->points : 0;
		return $endorser_points;
	}

	function add_points($data){
		global $wpdb;
		$blog_id = get_active_blog_for_user( $data['agent_id'] )->blog_id;
		$wpdb->insert("wp_".$blog_id."_points_transaction", $data);
	}

	function ic_register_form(){
		?>
		<div class="modal-body register-form">
        <h4>Register</h4>
        <p>Please register to your account</p>
         <form id="ic_register_form" name="ic_register_form" action="" class="mail-box">
            <p style="display: none;" class="register_status"></p>
            <div class="form-group">
                <label class="control-label" for="email">Firstname</label>
                <input type="text" class="form-control" name="firstname" placeholder="Enter Firstname">
            </div>
            <div class="form-group">
                <label class="control-label" for="email">Lastname</label>
                <input type="text" class="form-control" name="lastname" placeholder="Enter Lastname">
            </div>
            <div class="form-group">
                <label class="control-label" for="email">Email</label>
                <input type="email" class="form-control" name="email"  placeholder="Enter email">
            </div>
            <div class="form-group">
                <label class="control-label" for="email">Phone</label>
                <input type="email" class="form-control" name="phone"  placeholder="Enter Phone">
            </div>
            <div class="form-group">
                <label class="control-label" for="email">Address</label>
                <textarea class="form-control" name="address"  placeholder="Enter Address"></textarea>
            </div>
            <input type="hidden" name="campaign_id" value="<?= $_GET['campaign_id']?>">
            <input type="hidden" name="agent_id" value="<?= $_GET['agent_id']?>">
            <input class="submit_button" type="submit" name="submit" value="Register">
          </form>

          <script type="text/javascript">
          	jQuery(document).ready(function(){
          		jQuery('#ic_register_form .submit_button').click(function(){
          			var form_data = jQuery('#ic_register_form').serialize();
          			jQuery.post("<?= site_url('/wp-admin/admin-ajax.php?action=ic_endorser_register')?>", form_data).success(function(res){
          				jQuery('#register_status').text(res.status);
          				jQuery('#register_status').show();
          			});
          		});
          	});
          </script>
        </div>
		<?php
	}
 }
