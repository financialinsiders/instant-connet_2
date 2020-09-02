<?php
//$ip = $_SERVER['REMOTE_ADDR'];
//$ipInfo = file_get_contents('http://ip-api.com/json/' . $ip);
//$ipInfo = json_decode($ipInfo);
//$timezone = $ipInfo->timezone;
//date_default_timezone_set($timezone);
// TESTING COMMIT AND DEPLOYMENT!

if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

use OpenTok\OpenTok;
use Stripe\Customer as Stripe_Customer;
use Stripe\Invoice as Stripe_Invoice;
use Stripe\Plan as Stripe_Plan;
use Stripe\Charge as Stripe_Charge;

class IC_agent_api{

	function __construct() {

	    header('Access-Control-Allow-Origin: *');
	    header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
	    header("Access-Control-Allow-Headers: X-Requested-With");
	   

	    $functions = array('ic_all_letter_list', 'ic_agent_login', 'ic_site_pages', 'ic_add_endorser', 'ic_send_invitation',
	    	'ic_send_endorsement_invitation', 'ic_update_endorser', 'ic_endorser_list', 'ic_get_endorser_list', 'ic_add_create_email_template',
	    	'ic_update_email_template', 'ic_endorser_letter_list', 'ic_endorsement_letter_list',
	    	'ic_delete_endorser', 'ic_delete_letter', 'ic_new_lead', 'ic_new_lead_nomail', 'ic_update_lead', 
	    	'ic_noti_to_agent', 'ic_noti_to_user', 'ic_resend_gift', 'ic_send_gift', 'ic_get_sites', 
	    	'ic_add_points', 'ic_get_points', 'ic_update_fb_id', 'ic_get_fb_id', 'ic_instant_meeting', 
	    	'ic_appointment_meeting', 'ic_update_meeting_date', 'ic_update_meeting_eventid', 'ic_update_meeting_timekit',
	    	'ic_update_lead', 'ic_get_active_meeting_list', 'ic_generate_token', 'ic_update_active_time', 
	    	'ic_update_meeting_data', 'ic_get_endorser_info', 'ic_auto_login', 'ic_new_campaign', 
	    	'ic_update_campaign', 'ic_delete_campaign', 'ic_delete_campaign_letter', 'ic_campaigns', 
	    	'ic_new_video', 'ic_video_list', 'ic_delete_video', 'ic_test_template', 'ic_get_default_campaign',
	    	'ic_set_default_campaign', 'ic_get_template_style', 'ic_strategy', 'ic_update_video', 'ic_video_by_id',
	    	'ic_video_message', 'ic_video_message_delete', 'ic_video_message_update', 'ic_message_by_type',
	    	'test_email', 'ic_agent_endorsement_settings', 'ic_agent_save_endorsement_settings',
	    	'ic_agent_billing_transaction', 'ic_cron_agent_billing', 'ic_agent_update', 'ic_get_agent_details',
	    	'ic_upgrade_membership', 'ic_endorsement_settings', 'ic_endorser_login', 'ic_timekit_add_gmail', 
			'ic_video_message_by_id', 'ic_message_with_video', 'ic_endorser_register', 'ic_get_tmp_user', 'ic_update_user_status',
			'ic_reset_password', 'ic_get_giftbit_region', 'ic_get_giftbit_brands', 'ic_send_giftbit_campaign',
			'ic_follow_up_email', 'ic_get_predefined_notes', 'ic_notes_action', 'ic_forgot_password', 'ic_change_email',
			'ic_track_invitation_open', 'get_user_activity', 'get_endorser_invitation', 'ic_blog_info',
			'ic_get_points_by_type', 'ic_endorser_profile', 'ic_timeline_notes', 'ic_add_timeline_notes',
			'ic_endorser_redeemed_list', 'ic_resend_autologin_link', 'ic_save_offline_msg', 'ic_get_offline_msg',
			'ic_add_agent_wallet', 'ic_update_agent_status', 'ic_agent_status', 'ic_get_stripe_customer_cards', 'ic_create_customer_card', 'ic_delete_customer_card', 'ic_charge_current_customer','ic_create_stripe_customer_charge',
			'ic_lead_list', 'ic_lead_meeting', 'ic_get_lead_info', 'ic_delete_lead', 'ic_get_presentations', 'ic_get_videos', 
			'ic_save_ppt', 'ic_wallet_purchase_transaction', 'ic_get_point_value', 'ic_add_chat_points', 'ic_agent_balance',
			'ic_disable_agent_acc_have_no_wallet', 'ic_agent_account_active', 'ic_endorser_points_details', 'ic_agent_redeem_list', 'ic_agent_top_endorser', 'ic_agent_create_landing_page', 'ic_agent_get_landing_page',
			'ic_get_landing_page_templates', 'ic_agent_create_static_page', 'ic_agent_get_static_page',
			'ic_get_static_page_templates', 'ic_upload_image', 'ic_profile_image', 'ic_get_base64_image',
			'ic_chat_bot_category', 'ic_chat_bot_new', 'ic_retrieve_chat_bot', 'ic_retrieve_chat_list',
			'ic_new_endorsement_invitation', 'ic_delete_bot', 'ic_chat_bot_update', 'ic_chat_toggle_status',
			'ic_copy_chat_bot', 'ic_agent_status_frontend', 'ic_update_profile_page_data', 'ic_get_profile_page_data',
			'ic_add_session_timeline', 'getIntro', 'ic_link', 'ic_shorten_link', 'ic_create_introduction',
			'ic_timekit_google_callback', 'approve_endorser', 'ic_shorten_link_info',
			'ic_widget_settings', 'get_geo', 'ic_update_lead_info', 'ic_get_endorser_intro',
			'ic_endorser_session_info', 'ic_endorser_email_info', 'ic_resend_introduction', 'ic_track_introduction_open', 'ic_add_endorser_bot', 'ic_endorser_bot',
			'ic_update_default_endorser_bot', 'dis_approve_endorser','ic_endorser_update_browser_id', 'ic_endorser_get_browser_id'
	    );
		
		foreach ($functions as $key => $value) {
			add_action( 'wp_ajax_'.$value, array( &$this, $value) );
			add_action( 'wp_ajax_nopriv_'.$value, array( &$this, $value) );
		}
	}

	function ic_get_endorser_intro(){
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$response = array('status' => 'Success', 'data' => array());
		$response['data']['video'] = get_user_meta($_POST['endorserId'], 'video', true);
		$response['data']['landingPageContent'] = get_user_meta($_POST['endorserId'], 'landingPageContent', true);

		echo json_encode($response);
		die(0);
	}

	function get_geo(){
		$loc = file_get_contents('http://api.ipstack.com/check?access_key=ba0006dcf32e6aa480a9729f70193c49');
    	echo $loc;
    	die(0);
	}

	function ic_update_lead_info() {
		global $wpdb;

		$lead = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		unset($lead['endorser_id']);
		$wpdb->update("wp_leads", $lead, array('id' => $_GET['id']));

		$response = array('status' => 'Success', 'id' => $_GET['id'], 'msg' => 'Lead updated successfully');
		
		echo json_encode($response);
		die(0);
	}

	function ic_widget_settings(){
		$_POST = (array) json_decode(file_get_contents('php://input'));

		if(isset($_POST['widgetSettings'])){
			update_user_meta($_POST['user_id'], 'widgetSettings', $_POST['widgetSettings']);
		}

		$response = array('Status' => 'Success', 'data' => get_user_meta($_POST['user_id'], 'widgetSettings', true));

		echo json_encode($response);
        die(0);
	}

	function ic_timekit_google_callback(){
		file_put_contents('log.txt', json_encode(array('post' => $_POST, 'body' => (array) json_decode(file_get_contents('php://input')))));
		die(0);
	}

	function ic_shorten_link_info(){
		global $wpdb;

		$data = (array) $wpdb->get_row('select * from wp_short_link where id = '.$_GET['id']);

		if(isset($data['params'])){
			$data['params'] = unserialize($data['params']);
		}

		$response = array('Status' => 'Success', 'data' => $data);

		echo json_encode($response);
        die(0);
	}

	function ic_resend_introduction(){
		global $wpdb, $ntm_mail, $endorsements;
		$_POST = (array) json_decode(file_get_contents('php://input'));

		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');
		$endorser_id = $_POST['endorser_id'];

		$invitation = (array) $wpdb->get_row('select * from wp_short_link where id = '. $_POST['invitation_id']);

		$params = unserialize($invitation['params']);

		$link = site_url('introduction.php?id='.$_POST['invitation_id']);

		$agent_full_name = get_user_meta($agent_id, 'agent_full_name', true);

		$endorser_name = get_user_meta($endorser_id, 'first_name', true).' '.get_user_meta($endorser_id, 'last_name', true);

		$track_image = "<img src='".site_url('wp-admin/admin-ajax.php?action=ic_track_introduction_open&ref='.base64_encode(base64_encode($_POST['invitation_id'])))."' width='1' height='1'>";

		$content = "<div>$endorser_name wants to introduce you to $agent_full_name from Financial Insiders <a href='$link'>Click Here to View</a>$track_image</div>";

		$subject = "$endorser_name wants to introduce $agent_full_name of Financial Insiders";

		if(isset($params['video_url'])){
			$vid_src = $_POST['video_url'].'.png';
			$content .= "<div><img src='$vid_src'></div>";

			$ntm_mail->send_mail($invitation['email'], $subject, $content, '', '');
		} else{
			
			$ntm_mail->send_mail($invitation['email'], $subject, $content, '', '');
			
		}

		$wpdb->update('wp_short_link', array('email_status' => 'resent'), array('id' => $_POST['invitation_id']));

		$response = array('Status' => 'Success');

		echo json_encode($response);
        die(0);
	}

	function ic_create_introduction(){
		global $wpdb, $ntm_mail, $endorsements;
		$_POST = (array) json_decode(file_get_contents('php://input'));

		$data = $_POST;
		unset($data['contacts']);

		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');
		$endorser_id = $_POST['endorser_id'];

		if(isset($_POST['video_url']) && $_POST['video_url']){
			if($_POST['extrapoint']){
				$type = 'Extra points for video';
				$new_balance = $endorsements->get_endorser_points($endorser_id)['points'] + $_POST['extrapoint'];
				$data2 = array('points' => $_POST['extrapoint'], 'agent_id' => $agent_id, 'endorser_id' => $endorser_id, 'created' => date("Y-m-d H:i:s"), 'type' => 'extra_points', 'notes' => $type);

				$endorsements->add_points($data2);
			}
		}

		if($_POST['type'] == 'email'){
			$parent_id = 0;
			$respdata = array();
			foreach ($_POST['contacts'] as $key => $value) {
				$value = (array)$value;
				$data['first_name'] = $value['name'];
				$data['last_name'] = $value['name'];
				$data['email'] = $value['email'];
				$data['video_url'] = $_POST['video_url'];
				$data['attention_message'] = $_POST['attention_message'];

				$wpdb->insert("wp_short_link", 
					array(
						'link' => get_permalink($data['page_id']),
				  		'params' => serialize($data),
				  		'endorser_id' => $_POST['endorser_id'],
						'agent_id' => $_POST['agent_id'],
						'ts' => date('Y-m-d H:i:s'),
						'type' => $_POST['type'],
						'parent_id' => $parent_id,
						'email' => $value['email'],
						'email_status' => 'pending'
					)
				);

				$intro_id = $wpdb->insert_id;

				if($parent_id == 0){
					$parent_id = $intro_id;
				}

				$track_image = "<img src='".site_url('wp-admin/admin-ajax.php?action=ic_track_introduction_open&ref='.base64_encode(base64_encode($intro_id)))."' width='1' height='1'>";

				$link = site_url('introduction.php?id='.$wpdb->insert_id);

				$agent_full_name = get_user_meta($agent_id, 'agent_full_name', true);

				$endorser_name = get_user_meta($endorser_id, 'first_name', true).' '.get_user_meta($endorser_id, 'last_name', true);

				$content = "<div>$endorser_name wants to introduce you to $agent_full_name from Financial Insiders <a href='$link'>Click Here to View</a> $track_image</div>";

				$subject = "$endorser_name wants to introduce $agent_full_name of Financial Insiders";

				if(isset($_POST['video_url'])){
					$vid_src = $_POST['video_url'].'.png';
					$content .= "<div><img src='$vid_src'></div>";

					$ntm_mail->send_mail($value['email'], $subject, $content, '', '');
				} else{
					
					$ntm_mail->send_mail($value['email'], $subject, $content, '', '');
					
				}

				$endorsement_settings = get_user_meta($agent_id, 'endorsement_settings', true);
				$points = $endorsement_settings['email_point_value'] ? $endorsement_settings['email_point_value'] : 0;

				if($points){
					$type = 'Email Invitation';
					$new_balance = $endorsements->get_endorser_points($endorser_id)['points'] + $points;
					$data = array('points' => $points, 'agent_id' => $agent_id, 'endorser_id' => $endorser_id, 'created' => date("Y-m-d H:i:s"), 'type' => 'email_invitation', 'notes' => $type);
					$endorsements->add_points($data);
				}
				

				$respdata[] = $link;
			}

			$response = array('Status' => 'Success', 'data' => $respdata);
		} elseif(isset($_POST['type']) && $_POST['type']){
			$wpdb->insert("wp_short_link", 
				array(
					'link' => get_permalink($data['bot_id']),
			  		'params' => serialize($_POST),
			  		'endorser_id' => $_POST['endorser_id'],
					'agent_id' => $_POST['agent_id'],
					'ts' => date('Y-m-d H:i:s'),
					'type' => $_POST['type']
				)
			);

			$response = array('Status' => 'Success', 'data' => site_url('introduction.php?id='.$wpdb->insert_id));
		} else {
			$response = array('Status' => 'Error', 'msg' => 'Invalid type');
		}

		echo json_encode($response);
        die(0);
	}

	function ic_track_introduction_open(){

		global $wpdb;

		$track_link = base64_decode(base64_decode($_GET['ref']));
		$wpdb->update("wp_short_link", 
			array("email_status" => 'seen'), 
			array('id' => $track_link)
		);

		header('Content-Type: image/png');
		echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
		exit;
	}
				
				

	function ic_endorser_session_info(){
		global $wpdb;

		$res = $wpdb->get_results('select * from wp_short_link where endorser_id = '. $_GET['endorser_id']);
		$response = array('Status' => 'Success', 'endorserId' => $_GET['endorser_id']);

		$messages = array();
		$videos = array();

		foreach ($res as $key => $value) {
			$params = (array) unserialize($value->params);
			if($params['attention_message']){
				$messages[] = array('date' => $value->ts, 'message' => $params['attention_message']);
			}

			if($params['video_url']){
				$videos[] = array('date' => $value->ts, 'videoUrl' => $params['video_url']);
			}
		}

		$response['messages'] = $messages;
		$response['videos'] = $videos;

		echo json_encode($response);
        die(0);
	}

	function ic_endorser_email_info(){
		global $wpdb;

		$res = $wpdb->get_results('select * from wp_short_link where type = "email" and parent = 0 and endorser_id = '. $_GET['endorser_id']);
		$response = array('Status' => 'Success', 'endorserId' => $_GET['endorser_id']);

		$messages = array();

		foreach ($res as $key => $value) {
			$params = (array) unserialize($value->params);
			$tmp = array('date' => $value->ts);
			if($params['attention_message']){
				$tmp['message'] = $params['attention_message'];
			}

			if($params['video_url']){
				$tmp['videoUrl'] = $params['video_url'];
			}

			$tmp['emailList'] = array();
			$tmp['emailList'][] = array('email' => $value->email, 'email_status' => $value->email_status);

			$res2 = $wpdb->get_results('select * from wp_short_link where parent_id = '. $value->id);
			foreach ($res2 as $key2 => $value2) {
				$tmp['emailList'][] = array('email' => $value2->email, 'email_status' => $value2->email_status);
			}

			$messages[] = $tmp;
		}

		$response['messages'] = $messages;

		echo json_encode($response);
        die(0);
	}

	function ic_link(){
		global $wpdb;

		$res = $wpdb->get_row('select * from wp_short_link where id='.$_GET['id']);

		$link = $res->link.'?';

		$data = unserialize($res->params);//print_r($data);exit;
if(isset($data['agent_id'])) {		
			echo $siteID = get_active_blog_for_user( $data['agent_id'] )->blog_id;
			switch_to_blog( $siteID );
		}
	
if(isset($data['bot_id'])){
			$link = get_permalink($data['bot_id']).'?';
		} elseif(isset($data['page_id'])){
			$link = get_permalink($data['page_id']).'?';
		}

		if(isset($data['agent_id'])) {		
			echo $siteID = get_active_blog_for_user( $data['agent_id'] )->blog_id;
			switch_to_blog( $siteID );
		}
		
		if(isset($data['bot_id'])){
			$link = get_permalink($data['bot_id']).'?';
		} elseif(isset($data['page_id'])){
			$link = get_permalink($data['page_id']).'?';
		}
		$params = [];
		foreach($data as $k=>$v){
			$params[] = $k.'='.$v;
		}

		$params = [];
		foreach($data as $k=>$v){
			$params[] = $k.'='.$v;
		}

		$params = implode('&', $params);

		$link .= $params;

		if(isset($_POST['meta'])){
			?>
			<meta property="og:title" content="">
			<meta property="og:site_name" content="Financial Insiders">
			<meta property="og:url" content="<?= $link;?>">

			<?php if(isset($data['video_url'])){?>
			<meta property="og:image" content="<?= $data['video_url'].'png';?>">
			<?php }?>

			<?php if(isset($data['attention_message'])){?>
			<meta property="og:description" content="<?= $data['attention_message'];?>">
			<?php }?>

			<?php
		} else {
			echo $link;exit;
wp_redirect($link);
		}
		
		exit;
	}

	function ic_shorten_link(){
		global $wpdb;
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$wpdb->insert("wp_short_link", 
				array(
					'link' => $_POST['link'],
			  		'params' => serialize($_POST['params'])
				)
			);

		$response = array('Status' => 'Success', 'id' => $wpdb->insert_id);

		echo json_encode($response);
        die(0);
	}

	function ic_add_session_timeline(){
		global $wpdb;
		$_POST = isset($_POST['cid']) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$ress = $wpdb->get_results('select * from wp_leads where fb_ref = "'.$_POST['cid'].'"');
		
		if(count($ress)){	
			$blog_id = get_active_blog_for_user( $_POST['agentId'] )->blog_id;
			$this->track_api('ic_new_session', $blog_id, $ress[0]->id, $_POST);
			$wpdb->insert($wpdb->prefix ."notes", 
				array(
					'agent_id' => $_POST['agentId'],
			  		'lead_id' => $ress[0]->id,
			  		'endorser_id' => $ress[0]->endorser_id,
			  		'notes' => 'New Session - '.$_POST['sid'],
			  		'events' => $_POST['cid'],
			  		'created' => date('Y-m-d H-i-s')
				)
			);


			$response = array('Status' => 'Success', 'message' => "Stored in Timeline");
		} else {
			$response = array('Status' => 'Error');
		}
		echo json_encode($response);
        die(0);
	}

	function ic_update_profile_page_data() {
		
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));	
		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');
		
		//update_user_meta( $agent_id, 'profile_img', 'https://financialinsiders.ca/profile/agentsite/profile-img.png');
        
		$arr = array('agent_full_name', 'agent_designation', 'agent_company_name', 'profile_img', 'profile_bg_img', 'agent_about_profile_short_desc', 'agent_about_featured_img', 'agent_about_page_html', 'cares_reg_bot_id', 'cares_short_desc', 'cares_title', 'cta_bot_id', 'bot_listing_text_header', 'approach_short_desc', 'approach_page_title', 'footer_cta_headline_1','footer_cta_headline_2', 'footer_cta_bg_img', 'cta_btn_text','video_bool', 'video_url', 'conversation_bot_id', 'about_conversation_bot_id', 'approach_conversation_bot_id', 'home_greetings_bot_id', 'about_greetings_bot_id', 'approach_greetings_bot_id');
		foreach($arr as $a){
			if(isset($_POST[$a])) {
						update_user_meta($agent_id, $a, $_POST[$a]);
			}
		}
		
	   	if(isset($_POST['home_bots'])) { update_user_meta($agent_id, 'home_bots', $_POST['home_bots']); }
        
        	if(isset($_POST['approach_blocks'])) { update_user_meta($agent_id, 'approach_blocks', $_POST['approach_blocks']); } 

		$response = array('Status' => 'Success', 'message' => "Updated Data", "data" => $_POST);
		echo json_encode($response);
        die(0);

	}

	function ic_get_profile_page_data() {
		

		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');
			
		$videoBoolean = get_user_meta($agent_id, 'video_bool', true);
		
		
		if($videoBoolean == "true")  {

			$videoBool = true;


		} else {

			$videoBool = false;
		}
		 $data = array('agent_full_name' => get_user_meta($agent_id, 'agent_full_name', true),
			'agent_designation' => get_user_meta($agent_id, 'agent_designation', true),
			'agent_company_name' => get_user_meta($agent_id, 'agent_company_name', true),
			'profile_img' => get_user_meta($agent_id, 'profile_img', true),
			'profile_bg_img' => get_user_meta($agent_id, 'profile_bg_img', true),
			'agent_about_profile_short_desc' => get_user_meta($agent_id, 'agent_about_profile_short_desc', true),
			//'agent_about_page_title' => get_user_meta($agent_id, 'about_page_title', true),
        	'agent_about_featured_img' => get_user_meta($agent_id, 'agent_about_featured_img', true),
        	'agent_about_page_html' => get_user_meta($agent_id, 'agent_about_page_html', true),
        	'cares_reg_bot_id' => get_user_meta($agent_id, 'cares_reg_bot_id', true),
        	'cares_short_desc' => get_user_meta($agent_id, 'cares_short_desc', true),
        	'cares_title' => get_user_meta($agent_id, 'cares_title', true),
        	'about_conversation_bot_id' => get_user_meta($agent_id, 'about_conversation_bot_id', true),
        	'approach_conversation_bot_id' => get_user_meta($agent_id, 'approach_conversation_bot_id', true),
        	'conversation_bot_id' => get_user_meta($agent_id, 'conversation_bot_id', true),
        	'greetings_bot_id' => get_user_meta($agent_id, 'greetings_bot_id', true),
        	'about_conversation_bot_id' => get_user_meta($agent_id, 'about_conversation_bot_id', true),
        	'about_greetings_bot_id' => get_user_meta($agent_id, 'about_greetings_bot_id', true),
        	'approach_conversation_bot_id' => get_user_meta($agent_id, 'approach_conversation_bot_id', true),
        	'approach_greetings_bot_id' => get_user_meta($agent_id, 'approach_greetings_bot_id', true),
        	'cta_bot_id' => get_user_meta($agent_id, 'cta_bot_id', true),
        	'bot_listing_text_header' => get_user_meta($agent_id, 'bot_listing_text_header', true),
        	'approach_page_title' => get_user_meta($agent_id, 'approach_page_title', true),
        	'approach_short_desc' => get_user_meta($agent_id, 'approach_short_desc', true),
        	'footer_cta_headline_1' => get_user_meta($agent_id, 'footer_cta_headline_1', true),
        	'footer_cta_headline_2' => get_user_meta($agent_id, 'footer_cta_headline_2', true),
        	'footer_cta_bg_img' => get_user_meta($agent_id, 'footer_cta_headline_2', true),
        	'cta_btn_text' => get_user_meta($agent_id, 'cta_btn_text', true),
			'video_bool' => $videoBool,
			'video_url' => get_user_meta($agent_id, 'video_url', true),
        	'home_bots' => get_user_meta($agent_id, 'home_bots', true),
        	'approach_blocks' => get_user_meta($agent_id, 'approach_blocks', true)
        );

		$response = array('Status' => 'Success', 'data' => $data);
		echo json_encode($response);
		die(0);
	}

	function ic_chat_bot_category(){
		global $wpdb;
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		if($_POST['perform'] == 'add'){
			$wpdb->insert($wpdb->prefix ."chat_category", array('name' => $_POST['name']));
		} elseif($_POST['perform'] == 'edit'){
			$wpdb->update($wpdb->prefix ."chat_category", array('name' => $_POST['name']), array('id' => $_POST['id']));
		} elseif($_POST['perform'] == 'delete'){
			$wpdb->delete($wpdb->prefix ."chat_category", array('id' => $_GET['id']));
		}

		$results = $wpdb->get_results("select * from ".$wpdb->prefix ."chat_category");

		$data = array('status' => 'Success', 'url' => $results);

		echo json_encode($data);

		die(0);
	}

	function store_elements($cid, $pid, $results){
		global $wpdb;

		foreach ($results as $key => $value) {
			$value = (array) $value;

			if($value['opt'] == 'option'){
				$wpdb->insert($wpdb->prefix ."chat_bot_data", array(
					'action' => $value['action'] ? $value['action'] : '',
					'type' => $value['type'],
					'refid' => $value['refid'] ? $value['refid'] : '',
					'chat_id' => $cid,
			  		'parent' => $pid,
			  		'clabel' => $value['label'],
			  		'data' => $value['data'] ? serialize($value['data']) : '',
			  		'elements' => $value['elements'] ? serialize($value['elements']) : '',
			  		'labelref' => $value['labelref'] ? $value['labelref'] : '',
			  		'inputref' => $value['inputref'] ? $value['inputref'] : '',
			  		'ref' => $value['ref'] ? $value['ref'] : '',
			  		'reflabel' => $value['reflabel'] ? $value['reflabel'] : '',
			  		'action' => $value['action'] ? $value['action'] : '',
			  		'opt' => $value['opt'],
			  		'back' => $value['back'] ? 1 : 0,
			  		'skip' => $value['skip'] ? 1 : 0,
			  		'userinput' => $value['userinput'] ? $value['userinput'] : '',
			  		'avatar' => $value['avatar'] ? $value['avatar'] : 0,
			  		'avatar_image' => $value['avatar_image'] ? $value['avatar_image'] : '',
			  		'drag_order' => $value['drag_order'] ? $value['drag_order'] : 0,
			  		'video' => $value['video'] ? $value['video'] : '',
			  		'videotype' => $value['videotype'] ? $value['videotype'] : '',
			  		'validation_pattern'=> $value['validation_pattern'] ? $value['validation_pattern'] : '',
			  		'validation_msg'=> $value['validation_msg'] ? $value['validation_msg'] : ''
				));

				$parent_id = $wpdb->insert_id;

				foreach ($value['choice'] as $key1 => $value1) {
					$value1 = (array) $value1;
					$wpdb->insert($wpdb->prefix ."chat_bot_data", array(
						'chat_id' => $cid,
				  		'parent' => $parent_id,
				  		'coption' => 1,
				  		'clabel' => $value1['option'],
				  		'type' => $value1['opttype'] ? $value1['opttype'] : '',
				  		'action' => $value1['action'] ? $value1['action'] : ''
					));
					if(count($value1['logic_jump'])){
						$this->store_elements($cid, $wpdb->insert_id, $value1['logic_jump']);
					}
				}

			} else {
				$wpdb->insert($wpdb->prefix ."chat_bot_data", array(
					'action' => $value['action'] ? $value['action'] : '',
					'chat_id' => $cid,
			  		'parent' => $pid,
			  		'clabel' => $value['label'],
			  		'refid' => $value['refid'] ? $value['refid'] : '',
			  		'data' => $value['data'] ? serialize($value['data']) : '',
			  		'elements' => $value['elements'] ? serialize($value['elements']) : '',
			  		'labelref' => $value['labelref'] ? $value['labelref'] : '',
			  		'inputref' => $value['inputref'] ? $value['inputref'] : '',
			  		'ref' => $value['ref'] ? $value['ref'] : '',
			  		'reflabel' => $value['reflabel'] ? $value['reflabel'] : '',
			  		'action' => $value['action'] ? $value['action'] : '',
			  		'opt' => $value['opt'],
			  		'back' => $value['back'] ? 1 : 0,
			  		'skip' => $value['skip'] ? 1 : 0,
			  		'userinput' => $value['userinput'] ? $value['userinput'] : '',
			  		'video' => $value['video'] ? $value['video'] : '',
			  		'videotype' => $value['videotype'] ? $value['videotype'] : '',
			  		'type' => $value['type'] ? $value['type'] : '',
			  		'avatar' => $value['avatar'] ? $value['avatar'] : 0,
			  		'avatar_image' => $value['avatar_image'] ? $value['avatar_image'] : '',
			  		'drag_order' => $value['drag_order'] ? $value['drag_order'] : 0,
			  		'validation_pattern'=> $value['validation_pattern'] ? $value['validation_pattern'] : '',
			  		'validation_msg'=> $value['validation_msg'] ? $value['validation_msg'] : ''
				));

				if($value['opt'] == 'bot' && is_array($value['elements'])){
						$this->store_elements($cid, $wpdb->insert_id, $value['elements']);
				}
			}
		}
	}

	function ic_chat_bot_new(){
		global $wpdb;
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$args = array('post_title' => $_POST['title'], 'post_content' => $_POST['content'], 'post_type' => 'ic-chat-bot', 'post_status' => 'publish');

		$id = wp_insert_post($args);


		$arr = array('keywords', 'chat_category', 'avatarImage', 'chat_type', 'fbText', 'fb_image', 'twText', 'tw_image', 'piText', 'pi_image', 'liText', 'li_image', 'inviteContent', 'backgroundImage', 'fullscreen', 'emailInvite', 'appearance');

		foreach($arr as $a){
			update_post_meta($id, $a, $_POST[$a]);
		}

		$this->store_elements($id, 0, $_POST['elements']);

		$data = array('status' => 'Success', 'id' => $id);

		echo json_encode($data);

		die(0);
	}

	function ic_copy_chat_bot(){
		global $wpdb;
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$botId = $_POST['ID'];
		$botData = $this->ic_retrieve_chat_bot($botId, 1);

		$args = array('post_title' => $botData['title'].' - Copy', 'post_content' => $botData['content'], 'post_type' => 'ic-chat-bot', 'post_status' => 'publish');

		$id = wp_insert_post($args);


		$arr = array('keywords', 'chat_category', 'avatarImage', 'chat_type', 'fbText', 'fb_image', 'twText', 'tw_image', 'piText', 'pi_image', 'liText', 'li_image', 'inviteContent', 'backgroundImage', 'fullscreen', 'emailInvite', 'appearance');

		foreach($arr as $a){
			update_post_meta($id, $a, $botData[$a]);
		}

		$this->store_elements($id, 0, $botData['elements']);

		$data = array('status' => 'Success', 'id' => $id);

		echo json_encode($data);

		die(0);
	}

	function ic_chat_toggle_status(){
		global $wpdb;
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$args = array('post_status' => $_POST['status'] == 'true'? 'publish' : 'draft', 'ID' => $_POST['ID']);

		$id = wp_update_post($args);
		$data = array('status' => 'Success');

		echo json_encode($data);

		die(0);
	}

	function ic_chat_bot_update(){
		global $wpdb;
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$args = array('post_title' => $_POST['title'], 'post_content' => $_POST['content'], 'ID' => $_POST['ID'], 'post_status' => $_POST['status'] == 'true'? 'publish' : 'draft');

		$id = wp_update_post($args);

		$arr = array('keywords', 'chat_category', 'avatarImage', 'chat_type', 'fbText', 'fb_image', 'twText', 'tw_image', 'piText', 'pi_image', 'liText', 'li_image', 'inviteContent', 'backgroundImage', 'fullscreen', 'emailInvite', 'appearance');

		foreach($arr as $a){
			update_post_meta($_POST['ID'], $a, $_POST[$a]);
		}
		$wpdb->delete($wpdb->prefix ."chat_bot_data", array('chat_id' => $_POST['ID']));
		$this->store_elements($_POST['ID'], 0, $_POST['elements']);

		$data = array('status' => 'Success');

		echo json_encode($data);

		die(0);
	}

	function ic_delete_bot(){
		global $wpdb;
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$args = array('post_status' => 'trash', 'ID' => $_POST['ID']);

		$id = wp_update_post($args);

		$data = array('status' => 'Success');

		echo json_encode($data);

		die(0);
	}

	function get_chat_data($chat_data, $ind){
		$res = array();
		foreach ($chat_data[$ind] as $key => $value) {
			$value = (array)$value;
			$value['option'] = $value['coption'];
			$value['label'] = stripslashes($value['clabel']);
			$value['data'] = $value['data'] ? (array) unserialize($value['data']) : ((object)array());
			if(is_array($value['data']) && isset($value['data']['skipLabel'])){
				$value['data']['skipLabel'] = str_replace('\\', '', $value['data']['skipLabel']);
			}
			unset($value['elements']);
			if($value['opt'] == 'bot'){
				$value['elements'] = $this->get_chat_data($chat_data, $value['id']);
			}

			if($value['opt'] == 'option'){
				$tmp = $value;
				$tmp['choice'] = array();
				foreach ($chat_data[$value['id']] as $key1 => $value1) {
					$value1 = (array)$value1;
					$tmp['choice'][] = array(
						'id' => $value1['id'],
						'opttype' => $value1['type'],
						'option' => $value1['clabel'],
						'action' => $value1['action'],
						'logic_jump' => $this->get_chat_data($chat_data, $value1['id'])
					);
				}
				$res[] = $tmp;
			} else {
				$res[] = $value;
			}
		}

		return $res;
	}

	function ic_update_default_endorser_bot(){
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		update_user_meta($_POST['endorser_id'], 'intro_bot', $_POST['bot_id']);

		echo json_encode(array('status' => 'Success', 'data' => $this->ic_endorser_bot(1)));

		die(0);
	}

	function ic_add_endorser_bot(){

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$endorser_bot = get_user_meta($_POST['endorser_id'], 'endorser_bot', true);
		
		if($endorser_bot && is_array($endorser_bot)){
			$endorser_bot[] = $_POST['bot_id'];
		} else {
			$endorser_bot = array($_POST['bot_id']);
		}

		update_user_meta($_POST['endorser_id'], 'endorser_bot', array_unique($endorser_bot));

		echo json_encode(array('status' => 'Success', 'data' => $this->ic_endorser_bot(1)));

		die(0);
	}

	function ic_endorser_update_browser_id() {
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		if(isset($_POST['endorser_id'])) {	
			$siteID = get_active_blog_for_user( $_POST['endorser_id'] )->blog_id;
			switch_to_blog($siteId);

		}
		$uniqueFBIDs = get_user_meta($_POST['endorser_id'], 'browser_fb_id', false);
		array_push($uniqueFBIDs, $_POST['fb_id']);
		update_user_meta($_POST['endorser_id'], 'browser_fb_id', $data , false);
		echo json_encode(array('status' => 'Success'));			
		die(0);

		
	}

	function ic_endorser_get_browser_id() {
		if(isset($_GET['endorser_id'])) {	
			$siteID = get_active_blog_for_user( $_GET['endorser_id'] )->blog_id;
			switch_to_blog($siteId);
		}

		$fb_ids = get_user_meta($_GET['endorser_id'], 'browser_fb_id', false);
		echo json_encode(array('status' => 'Success', 'data' => $fb_ids));
		die(0);

	}

	function ic_endorser_bot($st = 1){

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		if(isset($_POST['endorser_id'])) {		
			$siteID = get_active_blog_for_user( $_POST['endorser_id'] )->blog_id;
			switch_to_blog( $siteID );
		}

		$endorser_bot = get_user_meta($_POST['endorser_id'], 'endorser_bot', true);
		$intro_bot = get_user_meta($_POST['endorser_id'], 'intro_bot', true);
		if(!$endorser_bot || !is_array($endorser_bot)){
			$endorser_bot = array();
		}

		$args = array(
		    'include'   => $endorser_bot,
		    'post_type' => 'ic-chat-bot',
		    'orderby'   => 'post__in',
		);
		//print_r($args);
		$posts = get_posts($args);
		$res = array();
		foreach ($posts as $p) :
		    $res[] = array(
		    	'id' => $p->ID,
		    	'title' => $p->post_title,
		    	'description' => $p->post_content,
		    	'default_bot' => $intro_bot == $p->ID,
		    	'appearance' => get_post_meta($p->ID, 'appearance', true)
		    );
		endforeach;

		if($st){
			return $res;
		} else {
			echo json_encode(array('status' => 'Success', 'data' => $res));

			die(0);
		}
	}

	function ic_retrieve_chat_bot($botId, $fl = 0){
		global $wpdb;

		if(isset($_GET['agentID'])) {		
			$siteID = get_active_blog_for_user( $_GET['agentID'] )->blog_id;
			switch_to_blog( $siteID );
		}

		if(isset($_GET['agent_id'])) {		
			$siteID = get_active_blog_for_user( $_GET['agent_id'] )->blog_id;
			switch_to_blog( $siteID );
		}
		
		$botId = $fl == 0 ? $_GET['chat'] : $botId;

		$value = get_post($botId);

		$chat = array(
			'ID' => $value->ID,
			'status' => $value->post_status == 'publish',
			'title' => $value->post_title,
			'content' => $value->post_content,
			'link' => get_permalink($value->ID)
		);

		$arr = array('keywords', 'chat_category', 'avatarImage', 'chat_type', 'fbText', 'fb_image', 'twText', 'tw_image', 'piText', 'pi_image', 'liText', 'li_image', 'inviteContent', 'backgroundImage', 'fullscreen', 'emailInvite', 'appearance');

		foreach($arr as $a){
			$chat[$a] = get_post_meta($value->ID, $a, true);
		}

		$chat_results = $wpdb->get_results("select * from ".$wpdb->prefix ."chat_bot_data where chat_id =".$botId." order by parent asc, drag_order asc");



		$chat_data = array();
		foreach ($chat_results as $key => $value) {
			if(!isset($chat_data[$value->parent])){
				$chat_data[$value->parent] = array();
			}
			$chat_data[$value->parent][] = $value;
		}

		$chat['elements'] = $this->get_chat_data($chat_data, 0);

		if($fl){
			return $chat;
		} else {
			$data = array('status' => 'Success', 'data' => $chat);
			echo json_encode($data);
			die(0);
		}
	}

	function ic_retrieve_chat_list(){
		global $wpdb;

		$chat = get_posts(array('post_type' => 'ic-chat-bot', 'posts_per_page' => -1, 'post_status' => array('draft', 'publish')));
		$newresults = [];
		foreach ($chat as $key => $value) {
			$newresults[] = array(
				'ID' => $value->ID,
				'status' => $value->post_status == 'publish',
				'title' => $value->post_title,
				'content' => $value->post_content,
				'link' => get_permalink($value->ID),
				'avatar' => get_post_meta($value->ID, 'chat_avatar_img', true),
				'type' => get_post_meta($value->ID, 'chat_type', true),
				'chatCardImg' => get_post_meta($value->ID, 'backgroundImage', true),
				'description' => get_post_meta($value->ID, 'backgroundImage', true)
			);
		}

		$data = array('status' => 'Success', 'data' => $newresults);

		echo json_encode($data);

		die(0);
	}

	function ic_get_base64_image(){

		$file = explode(".", $_GET['img']);
		$base = 'data:image/'.$file[count($file)-1].';base64,';
		$encode = base64_encode(file_get_contents($_GET['img']));

		$data = array('status' => 'Success', 'url' => $base.$encode);

		echo json_encode($data);

		die(0);
	}

	function ic_profile_image(){
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');

		if(isset($_POST['img'])){
			update_user_meta($agent_id, 'ic_agent_profile_image', $_POST['img']);
		}

		$data = array('status' => 'Success', 'url' => get_user_meta($agent_id, 'ic_agent_profile_image', true));

		echo json_encode($data);

		die(0);
	}

	function ic_upload_image(){
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$data = $_POST['file'];
		$name = strtotime('now').str_replace(' ', '-', $_POST['name']);

		list($type, $data) = explode(';', $data);
		list(, $data) = explode(',', $data);
		$data = base64_decode($data);
		$upload_dir       = wp_upload_dir();

		$upload_path      = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
		
		file_put_contents($upload_path.$name, $data);

		$data = array('status' => 'Success', 'url' => $upload_dir['url'].'/'.$name);

		echo json_encode($data);

		die(0);
	}

	function ic_get_landing_page_templates(){
		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');

		switch_to_blog(1);
        $templates = get_posts(array('post_type' => 'ictemplate', 'posts_per_page' => -1));
        
        $results = array();
        foreach ($templates as $t => $val) {
        	$value = array('ID' => $val->ID, 'title' => $val->post_title);
		    $value['template'] = get_post_meta($value['ID'], 'template_html', true);
		    $template_agents = get_post_meta($value['ID'], 'template_agents', true);
		    $value['agents'] = explode(',', $template_agents);

		    if(in_array($agent_id, $value['agents']) || in_array(0, $value['agents'])){
		    	$value['social_template'] = [];
		    	$value['social_template']['fb_text'] = get_post_meta($val->ID, 'template_social_fb_text', true);
			    $value['social_template']['fb_image'] = get_post_meta($val->ID, 'template_social_fb_image', true);
			    $value['social_template']['tw_text'] = get_post_meta($val->ID, 'template_social_tw_text', true);
			    $value['social_template']['tw_image'] = get_post_meta($val->ID, 'template_social_tw_image', true);
			    $value['social_template']['pi_image'] = get_post_meta($val->ID, 'template_social_pi_image', true);

		    	$value['custom_field'] = [];
		    	$dynamic_template = get_post_meta($value['ID'], 'dynamic_template', true);
	    		$dynamic_template = is_array($dynamic_template) ? $dynamic_template : array() ;
		    	foreach ($dynamic_template['type'] as $key => $va) {
		    		$value['custom_field'][] = array('id' => 'customfield'.($key+1), 
		    			'type' => $va, 
		    			'width' => $dynamic_template['width'][$key],
		    			'height' => $dynamic_template['height'][$key],
		    			'content' => $dynamic_template['content'][$key]);
		    	}

		    	$results[] = $value;
		    }
        }
        restore_current_blog();
        header('Content-Type: application/json');
        echo json_encode(array('status' => 'Success', 'data' => $results));

		die(0);
		exit;
	}

	function ic_agent_create_landing_page(){
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));	
		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');

		$landing_page = $_POST['ID'];
		if($landing_page){
			$strategy = array('post_title' => $_POST['title'], 'ID' => $landing_page);
			update_post_meta($landing_page, 'template_html', $_POST['template']);
			update_post_meta($landing_page, 'dynamic_template', $_POST['custom_field']);

			foreach($_POST['social_template'] as $k=>$v){
				update_post_meta($landing_page, 'template_social_'.$k, $v);
			}
			
			wp_update_post( $strategy);
		} else {
			$strategy = array('post_title' => $_POST['title'], 'post_type' => 'ictemplate', 'post_status' => 'publish', 'post_author' => $agent_id);
			$landing_page = wp_insert_post( $strategy);
			update_post_meta($landing_page, 'template_html', $_POST['template']);
			update_post_meta($landing_page, 'dynamic_template', $_POST['custom_field']);
			foreach($_POST['social_template'] as $k=>$v){
				update_post_meta($landing_page, 'template_social_'.$k, $v);
			}
		}

		echo json_encode(array('status' => 'Success'));

		die(0);
		exit;
	}

	function ic_agent_get_landing_page(){
		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');

		$posts = get_posts(array('post_type' => 'ictemplate', 'posts_per_page' => -1));
		$results = array();
        foreach ($posts as $t => $val) {
        	$value = array('ID' => $val->ID, 'title' => $val->post_title);
		    $value['template'] = get_post_meta($value['ID'], 'template_html', true);
		    $value['custom_field'] = get_post_meta($value['ID'], 'dynamic_template', true);
		    $value['link'] = get_permalink($value['ID']);

		    $value['social_template'] = [];
	    	$value['social_template']['fb_text'] = get_post_meta($val->ID, 'template_social_fb_text', true);
		    $value['social_template']['fb_image'] = get_post_meta($val->ID, 'template_social_fb_image', true);
		    $value['social_template']['tw_text'] = get_post_meta($val->ID, 'template_social_tw_text', true);
		    $value['social_template']['tw_image'] = get_post_meta($val->ID, 'template_social_tw_image', true);
		    $value['social_template']['pi_image'] = get_post_meta($val->ID, 'template_social_pi_image', true);

		    $results[] = $value;
		}
		header('Content-Type: application/json');
		echo json_encode(array('status' => 'Success', 'data' => $results));

		die(0);
		exit;
	}

	

	function ic_get_static_page_templates(){
		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');

		switch_to_blog(1);
        $templates = get_posts(array('post_type' => 'icstatic', 'posts_per_page' => -1));
        
        $results = array();
        foreach ($templates as $t => $val) {
        	$value = array('ID' => $val->ID, 'title' => $val->post_title);
		    $value['template'] = get_post_meta($value['ID'], 'template_html', true);
		    $template_agents = get_post_meta($value['ID'], 'template_agents', true);
		    $value['agents'] = explode(',', $template_agents);

		    if(in_array($agent_id, $value['agents']) || in_array(0, $value['agents'])){
		    	$value['social_template'] = [];
		    	$value['social_template']['fb_text'] = get_post_meta($val->ID, 'template_social_fb_text', true);
			    $value['social_template']['fb_image'] = get_post_meta($val->ID, 'template_social_fb_image', true);
			    $value['social_template']['tw_text'] = get_post_meta($val->ID, 'template_social_tw_text', true);
			    $value['social_template']['tw_image'] = get_post_meta($val->ID, 'template_social_tw_image', true);
			    $value['social_template']['pi_image'] = get_post_meta($val->ID, 'template_social_pi_image', true);

		    	$value['custom_field'] = [];
		    	$dynamic_template = get_post_meta($value['ID'], 'dynamic_template', true);
	    		$dynamic_template = is_array($dynamic_template) ? $dynamic_template : array() ;
		    	foreach ($dynamic_template['type'] as $key => $va) {
		    		$value['custom_field'][] = array('id' => 'customfield'.($key+1), 
		    			'type' => $va, 
		    			'width' => $dynamic_template['width'][$key],
		    			'height' => $dynamic_template['height'][$key],
		    			'content' => $dynamic_template['content'][$key]);
		    	}

		    	$results[] = $value;
		    }
        }
        restore_current_blog();
        header('Content-Type: application/json');
        echo json_encode(array('status' => 'Success', 'data' => $results));

		die(0);
		exit;
	}

	function ic_agent_create_static_page(){
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));	
		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');

		$landing_page = $_POST['ID'];
		if($landing_page){
			$strategy = array('post_title' => $_POST['title'], 'ID' => $landing_page);
			update_post_meta($landing_page, 'template_html', $_POST['template']);
			

			$dynamic_template = array('type' => [], 'width' => [], 'height' => [], 'content' => []);
	    	foreach ($_POST['custom_field'] as $key => $va) {
	    		$dynamic_template['type'][$key] =  $va['type'];
	    		$dynamic_template['width'][$key] = $va['width'];
	    		$dynamic_template['height'][$key] = $va['height'];
	    		$dynamic_template['content'][$key] = $va['content'];
	    	}

	    	update_post_meta($landing_page, 'dynamic_template', $dynamic_template);

			foreach($_POST['social_template'] as $k=>$v){
				update_post_meta($landing_page, 'template_social_'.$k, $v);
			}
			
			wp_update_post( $strategy);
		} else {
			$strategy = array('post_title' => $_POST['title'], 'post_type' => 'icstatic', 'post_status' => 'publish', 'post_author' => $agent_id);
			$landing_page = wp_insert_post( $strategy);
			update_post_meta($landing_page, 'template_html', $_POST['template']);
			update_post_meta($landing_page, 'dynamic_template', $_POST['custom_field']);
			foreach($_POST['social_template'] as $k=>$v){
				update_post_meta($landing_page, 'template_social_'.$k, $v);
			}
		}

		echo json_encode(array('status' => 'Success'));

		die(0);
		exit;
	}

	function ic_agent_get_static_page(){
		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');

		$posts = get_posts(array('post_type' => 'icstatic', 'posts_per_page' => -1, 'orderby'    => 'menu_order', 'sort_order' => 'asc'));
		$results = array();
        foreach ($posts as $t => $val) {
        	$value = array('ID' => $val->ID, 'title' => $val->post_title);
		    $value['template'] = get_post_meta($value['ID'], 'template_html', true);
		    //$value['custom_field'] = get_post_meta($value['ID'], 'dynamic_template', true);
		    $value['link'] = get_permalink($value['ID']);

		    $value['social_template'] = [];
	    	$value['social_template']['fb_text'] = get_post_meta($val->ID, 'template_social_fb_text', true);
		    $value['social_template']['fb_image'] = get_post_meta($val->ID, 'template_social_fb_image', true);
		    $value['social_template']['tw_text'] = get_post_meta($val->ID, 'template_social_tw_text', true);
		    $value['social_template']['tw_image'] = get_post_meta($val->ID, 'template_social_tw_image', true);
		    $value['social_template']['pi_image'] = get_post_meta($val->ID, 'template_social_pi_image', true);

		    $value['custom_field'] = [];
	    	$dynamic_template = get_post_meta($value['ID'], 'dynamic_template', true);
    		$dynamic_template = is_array($dynamic_template) ? $dynamic_template : array() ;
	    	foreach ($dynamic_template['type'] as $key => $va) {
	    		$value['custom_field'][] = array('id' => 'customfield'.($key+1), 
	    			'type' => $va, 
	    			'width' => $dynamic_template['width'][$key],
	    			'height' => $dynamic_template['height'][$key],
	    			'content' => $dynamic_template['content'][$key]);
	    		}

	    	$results[] = $value;
		}
		header('Content-Type: application/json');
		echo json_encode(array('status' => 'Success', 'data' => $results));

		die(0);
		exit;
	}

	function ic_agent_redeem_list(){
		global $wpdb;

		$resuts = $wpdb->get_results("select * from ".$wpdb->prefix."points_transaction where type = 'Redeem Point' ");

		echo json_encode(array('status' => 'Success', 'data' => $resuts));

		die(0);
		exit;
	}

	function ic_agent_top_endorser(){
		global $wpdb;

		$resuts = $wpdb->get_results("SELECT sum(points) as points, endorser_id FROM ".$wpdb->prefix."points_transaction WHERE type != 'Redeem Point' group by endorser_id order by points desc");

		$new_results = [];
		foreach ($resuts as $key => $value) {
			$value = (array) $value;
			$value['name'] = get_user_meta($value['endorser_id'], 'first_name', true).' '.get_user_meta($value['endorser_id'], 'last_name', true); 
			$new_results[] = $value;
		}

		echo json_encode(array('status' => 'Success', 'data' => $new_results));

		die(0);
		exit;
	}

	function ic_agent_account_active(){
		global $wpdb;
		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');

		$disable_agent_app = !get_meta($agent_id, 'disable_agent_app');

		echo json_encode(array('status' => 'Success', 'is_account_active' => $disable_agent_app));

		die(0);
		exit;
	}

	function ic_disable_agent_acc_have_no_wallet(){
		global $wpdb, $ntm_mail;

		$data = (array)get_users(array('role' => 'pmpro_role_2'));
		foreach ($data as $key => $value) {
			$agent_id = $value->ID;
			$blog_id = get_active_blog_for_user($value->ID)->blog_id;

			$res = $wpdb->get_results("SELECT * FROM wp_".$blog_id."_points_transaction where queue = 1 and agent_id = ".$agent_id." order by id asc");

			if(count($res) && (strtotime('now') - strtotime($res[0]->created)) > 86400){
				update_user_meta($agent_id, 'disable_agent_app', 1);

				$user_info1 = get_userdata($agent_id);

				$template1 = 'Your App Acc disabled. Last 24 hour no purchase made after wallet empty notifiction. Contact your administrator to enable your account';

				$ntm_mail->send_mail($user_info1->user_email, 'Your App Acc disabled', $template1, '', '');
			}
		}
	}

	function ic_add_chat_points(){
		global $wpdb, $endorsements;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$endorser = $_POST['endorser'];
		$results = $wpdb->get_results("select * from wp_leads where endorser_id = '".$endorser."' and (email='".$_POST['email']."' or ip_address= '".$_POST['ip_address']."')");

    	if(count($results)!=0){
    		if($results[0]->chat_conversion == 0){
				$blog_id = get_active_blog_for_user($endorser)->blog_id;
				$agent_id = get_blog_option($blog_id, 'agent_id');
				$points = get_user_meta($agent_id, 'endorsement_settings', true)['chat_point_value'];
				$type = 'Instant connect Chat';
				$new_balance = $endorsements->get_endorser_points($endorser)['points'] + $points;
				$data = array('points' => $points, 'agent_id' => $agent_id, 'endorser_id' => $endorser, 'created' => date("Y-m-d H:i:s"), 'type' => 'chat_conversion', 'notes' => $type);
				$endorsements->add_points($data);
				$this->track_api('chat_participants', $blog_id, $endorser, $data);
				$wpdb->update("wp_leads", array('chat_conversion' => 1), array('id' => $results[0]->id));

				$wpdb->insert($wpdb->prefix ."notes", 
					array(
						'agent_id' => $agent_id,
				  		'lead_id' => $results[0]->id,
				  		'endorser_id' => $endorser,
				  		'notes' => 'Chat Point credited',
				  		'events' => 'ic_add_chat_points',
				  		'created' => date('Y-m-d H-i-s')
					)
				);

				echo json_encode(array('status' => 'Success', 'balance' => $new_balance));
			} else {
				echo json_encode(array('status' => 'Error', 'msg' => 'ALread chat point converted.'));
			}
		} else {
			echo json_encode(array('status' => 'Error', 'msg' => 'No lead info exist!!'));
		}

		die(0);
		exit;
	}

	function ic_get_point_value($point=false){
	        
		$points_per_dollar = get_option('points_per_dollar');

		$admin_fee = get_option('admin_fee');
		
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		
		$dollar_per_point = 1/$points_per_dollar;

		if($point)
			$points = ($point*$dollar_per_point);
		else
			$points = ($_POST['points']*$dollar_per_point);

		$point_value = $points+(($points*$admin_fee)/100);

		
		if($point){
			return number_format($points, 2);
		} else {
			echo json_encode(array('status' => 'Success', 'point_value' => number_format($point_value, 2)));

			die(0);
			exit;
		}
		
	}

	function ic_save_ppt()
	{
		
		
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		
		
		$data = $_POST['data'];
		$file = $_POST['id'];

		$files = array();

		mkdir(IC_PLUGIN_DIR."/extract/$file/");
		foreach ($data as $key => $value) {
			$filee = 'file-page'.($key+1).'.jpg';
			$files[] = $filee;
			file_put_contents(IC_PLUGIN_DIR."/extract/$file/".$filee, base64_decode($value->FileData));

			$w = 1600; $h = 1200;
			list($width, $height) = getimagesize(IC_PLUGIN_DIR."/extract/$file/".$filee);
		    $r = $width / $height;
		    if ($crop) {
		        if ($width > $height) {
		            $width = ceil($width-($width*abs($r-$w/$h)));
		        } else {
		            $height = ceil($height-($height*abs($r-$w/$h)));
		        }
		        $newwidth = $w;
		        $newheight = $h;
		    } else {
		        if ($w/$h > $r) {
		            $newwidth = $h*$r;
		            $newheight = $h;
		        } else {
		            $newheight = $w/$r;
		            $newwidth = $w;
		        }
		    }
		    $src = imagecreatefromjpeg(IC_PLUGIN_DIR."/extract/$file/".$filee);
		    $dst = imagecreatetruecolor($newwidth, $newheight);
		    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

		    imagejpeg($dst,IC_PLUGIN_DIR."/extract/$file/".$filee);
		}

		$option = get_option('ic_presentations');
		$option = is_array($option) ? $option : [];
		$option[] = array('folder' => $file, 'files' => $files, 'name' => $_GET['name']);
		update_option('ic_presentations', $option);

		echo json_encode(array('folder' => $file, 'files' => $files));

		die(0);
		exit;
	}

	function ic_get_presentations(){
		$option = get_option('ic_presentations');
    	$option = is_array($option) ? $option : [];

		echo json_encode($option);
    	die(0);
	}

	function ic_get_videos(){
		$option = get_option('youtube_videos');
    	$option = is_array($option) ? $option : [];

		echo json_encode($option);
    	die(0);
	}

	function ic_delete_lead() {

		global $wpdb;
		$results = $wpdb->delete("wp_leads", array('id' => $_GET['lead_id']));
		if($results) {

			$response = array('status' => 'Success', 
							'msg' => 'Lead Deleted',					  	
						);
		} else {

			$response = array('status' => 'Fail', 'msg' =>'Failed to delete lead');
		}

		echo json_encode($response);
		die(0);

	}

	function ic_lead_list(){
		global $wpdb;
		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');
		$search = $_GET['search']['value'];
		$ss = '';
		if($search){
			$ss = "(email like '%$search%' or first_name like '%$search%' or last_name like '%$search%' or phone like '%$search%') and ";
		}

		$recordsTotal = $wpdb->get_results("select * from wp_leads where $ss agent_id = ".$agent_id);
		$start = $_GET['start'];
		$length = $_GET['length'];
		$offset = $start * $length;
		$order = $_GET['columns'][$_GET['order'][0]['column']]['data'];
		$orderby = $_GET['order'][0]['dir'];

		


		
		//print_r("select * from wp_leads where agent_id = " . $agent_id ." order by ". $order ." ". $orderby." limit " .$offset.", ".$length);

		$recordsFiltered = $wpdb->get_results("select * from wp_leads where $ss agent_id = " . $agent_id ." order by ". $order ." ". $orderby." limit " .$offset.", ".$length."");
		
		//echo $recordsFiltered;

		$response = array('status' => 'Success', 
							'draw' => (int)$_GET['draw'],
							'data' => $recordsFiltered,
						  	'recordsTotal' => count($recordsTotal),
						  	'recordsFiltered' => count($recordsTotal),
						);
		echo json_encode($response);
		die(0);
	}

	function ic_get_stripe_customer_cards() {	
	
		
		
		if(isset($_GET['customer_id'])){
			
			$stripeCustomerId = $_GET['customer_id'];
			
			try{
				Stripe\Stripe::setApiKey(pmpro_getOption("stripe_secretkey"));
				Stripe\Stripe::setAPIVersion("2017-08-15");
				$cards = Stripe_Customer::retrieve($stripeCustomerId)->sources->all(array(
	  'limit'=>3, 'object' => 'card'));
				$response = array('status' => 'Success', 'data' =>  $cards);
			
			}
			catch (Exception $e)
					{
					
					$errorResponse = array('status' => 'Fail', 'msg' =>  $e->getMessage());
					json_encode($errorResponse);
					die(0);

				}
		} else {
			
			$response = array('status' => 'Fail', 'msg' =>  'NO CUSTOMER ID SENT');
			
		}
		
		echo json_encode($response);
		
		die(0);
	
	}

	function ic_create_customer_card() {
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));	
		if(isset($_POST['customer_id'])){
			
			try
				{
			Stripe\Stripe::setApiKey(pmpro_getOption("stripe_secretkey"));
			Stripe\Stripe::setAPIVersion("2017-08-15");


			$customer = Stripe_Customer::retrieve($_POST['customer_id']);
			$card = $customer->sources->create(array("source" => $_POST['stripe_token']));
			
			$response = array('status' => 'Success', 'data' =>  $card);

		}

		catch (Exception $e)
				{
					
					$errorResponse = array('status' => 'Fail', 'msg' =>  $e->getMessage());
					json_encode($errorResponse);
					die(0);

				}


		} else {
			
			$response = array('status' => 'Fail', 'msg' =>  'Invalid Parameters');
			
		}
		
		echo json_encode($response);
		die(0);
		
	}

	function ic_delete_customer_card() {
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));	
		if(isset($_POST['customer_id']) && isset($_POST['card_id'])){
			
			Stripe\Stripe::setApiKey(pmpro_getOption("stripe_secretkey"));
			Stripe\Stripe::setAPIVersion("2017-08-15");
			

			try
				{
				$customer = Stripe_Customer::retrieve($_POST['customer_id']);
				$card = $customer->sources->retrieve($_POST['card_id'])->delete();
				$response = array('status' => 'Success', 'data' =>  $card);
			}

			catch (Exception $e)
				{
					
					$errorResponse = array('status' => 'Fail', 'msg' =>  $e->getMessage());
					json_encode($errorResponse);
					die(0);

				}


		} else {
			$response = array('status' => 'Fail', 'msg' =>  'Invalid Parameters');
		}
		echo json_encode($response);
		die(0);
	}

	function ic_charge_current_customer() {
		
		
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));	
			if( isset($_POST['customer_id']) && isset($_POST['amount_cents'])){
			
			Stripe\Stripe::setApiKey(pmpro_getOption("stripe_secretkey"));
			Stripe\Stripe::setAPIVersion("2017-08-15");
			
			try
				{
				
				
			$charge = Stripe_Charge::create(array(
	  		"amount" => $_POST['amount_cents'],
	  		"currency" => "CAD",
	  		"customer" => $_POST['customer_id'],
	  		"source" => $_POST['card_id'],
	  		"description" => 'Point Purchase'
			));
			//VINO PLEASE ADD THE CODE FOR AGENT WALLET HERE.
			

			$this->ic_add_agent_wallet($charge);
			

			$response = array('status' => 'Success', 'data' =>  $charge);
			
			}

			catch (Exception $e)
				{
					
					$errorResponse = array('status' => 'Fail', 'msg' =>  $e->getMessage());
					json_encode($errorResponse);
					die(0);

				}

		} else {
			
			$response = array('status' => 'Fail', 'msg' =>  $_POST['customer_id']);

		}

		echo json_encode($response);
		die(0);

	}

	function ic_create_stripe_customer_charge() {
		

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		
		

		if(isset($_POST['agent_id']) && isset($_POST['stripe_token']) && isset($_POST['amount_cents']))  {
			
			$agentInfo = get_userdata( $_POST['agent_id'] );
			Stripe\Stripe::setApiKey(pmpro_getOption("stripe_secretkey"));
			Stripe\Stripe::setAPIVersion("2017-08-15");


			
			try
				{
					
					$customer = Stripe_Customer::create(array(							 
							  "description" => $agentInfo->user_nicename . " (" .  $agentInfo->user_email . ")",
							  "card" => $_POST['stripe_token']
							));
					
					

					$var = update_user_meta($user_id, "pmpro_stripe_customerid", $customer->id);

				}

				catch (Exception $e)
				{
					

					$errorResponse = array('status' => 'Fail', 'msg' =>  $e->getMessage());
					echo json_encode($errorResponse);
					die(0);
				}
					

			try
			{
				$charge = Stripe_Charge::create(array(
				  "amount" => $_POST['amount_cents'],
				  "currency" => 'CAD',
				  "customer" => $customer->id,
				  "description" => "Point Purchase"
				  )
				);

				$this->ic_add_agent_wallet($charge);
			}
			catch (Exception $e)
			{
				
				
				$errorResponse = array('status' => 'Fail', 'msg' =>  $e->getMessage());
				echo json_encode($errorResponse);
				die(0);
			}

			if(empty($charge["failure_message"]))
			{
				//successful charge
				//VINO ADD THIS TRANSACTION ID TO OUR DB RECORDS FOR THE PURCHASE $response["id"];
				//SAVE THIS IN OUR AGENT WALLET HERE
				$response = array('status' => 'Success', 'data' =>  $charge);
				
				echo json_encode($response);
				die(0);
				
			}
			else
			{
				//FAILED CHARGE 
				
				$response = array('status' => 'Fail', 'msg' =>  $e->getMessage());
				echo json_encode($response);
				die(0);
			}


					


		} else {

			$response = array('status' => 'Fail', 'msg' =>  'Invalid Parameters');
			echo json_encode($response);
			die(0);

		}

	}




	function ic_agent_status()
	
	{
		$arr = array(1 => 'Online', 2 => 'Offline', 3 => 'Meeting', 4 => 'Away');
		$lst_login_time = get_user_meta($_GET['agent'], 'last_seen_time', true);
		$user_current_status = get_user_meta($_GET['agent'], 'agent_status', true);
		$response = array('status' => 'Success', 'status_text'=>$arr[$user_current_status], 'agent_status' => $user_current_status, 'last_seen_time' => $lst_login_time);
		echo json_encode($response);
		die(0);
	}

	function ic_update_agent_status() {

			$arr = array(1 => 'Online', 2 => 'Offline', 3 => 'Meeting', 4 => 'Away');

			$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

			update_user_meta($_POST['agent'], 'agent_status', $_POST['status']);

			update_user_meta($_POST['agent'], 'last_seen_time', date("Y-m-d H:i:s"));
			
			$lst_login_time = get_user_meta($_POST['agent'], 'last_seen_time', true);
			$user_current_status = get_user_meta($_POST['agent'], 'agent_status', true);
			$response = array('status' => 'Success', 'status_text'=>$arr[$user_current_status], 'agent_status' => $user_current_status, 'last_seen_time' => $lst_login_time);
			echo json_encode($response);
			die(0);
			exit;

	}

	

	function ic_save_offline_msg(){

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
	

		update_user_meta($_POST['agent_id'], 'status_data_'.$_POST['type'], 
			array(
				'video' => $_POST['offline_video'],
				'msg' => $_POST['offline_msg'],
			)
		);

		$response = array('status' => 'Success', 'data'=>get_user_meta($_POST['agent_id'], 'status_data_'.$_POST['type'], true));
		echo json_encode($response);
		die(0);
	}

	function ic_get_offline_msg(){
		
		if(!isset($_GET['type'])){
			$data = array(
				'Welcome' => get_user_meta($_GET['agent_id'], 'status_data_Welcome', true),
				'Online' => get_user_meta($_GET['agent_id'], 'status_data_Online', true),
				'Offline' => get_user_meta($_GET['agent_id'], 'status_data_Offline', true),
				'Away' => get_user_meta($_GET['agent_id'], 'status_data_Away', true),
				'Wait60' => get_user_meta($_GET['agent_id'], 'status_data_Wait60', true),
			);
		} else {
			$data = get_user_meta($_GET['agent_id'], 'status_data_'.$_GET['type'], true);
		}

		$response = array('status' => 'Success', 'data'=>$data);
		echo json_encode($response);
		die(0);
	}

	function ic_agent_status_frontend(){
		$status = $_GET['online'];
		$agent_id = $_GET['agent_id'];
		if(isset($_GET['online'])){
			
			$arr = array(1 => 'Online', 2 => 'Offline', 3 => 'Meeting', 4 => 'Away');

			$st = get_user_meta($agent_id, 'agent_status', true);

			$data = get_user_meta($agent_id, 'status_data_'.$arr[$st], true);
		} else {
			$data = get_user_meta($agent_id, 'status_data_Offline', true);
		}

		$data = (array) $data;

		$response = array(
			'status' => 'Success', 
			'botID'=>$data['msg'], 
			'data' => $this->ic_retrieve_chat_bot($data['msg'], 1)
		);
		echo json_encode($response);
		die(0);
	}

	function ic_agent_balance($id = ''){
		global $wpdb, $ntm_mail;
		
		if($id == ''){
			$blog_id = get_current_blog_id();
			$uid = get_blog_option($blog_id, 'agent_id');
		} else {
			$blog_id = get_active_blog_for_user( $id )->blog_id;
			$uid = $id;
		}

		
		$res = $wpdb->get_row("SELECT * FROM wp_".$blog_id."_agent_wallet where agent_id = ".$uid." order by id desc");

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$points_per_dollar = get_option('points_per_dollar');
		$points_per_dollar = $points_per_dollar ? $points_per_dollar : 0;
		$dollar_per_point = $points_per_dollar ? (1/$points_per_dollar) : 0;

		$avail_balance = $res->balance ? $res->balance : 0;

		$avail_balance = $avail_balance / 100;

		$avail_points = $avail_balance * $points_per_dollar;

		$endorserr = [];

		$res2 = $wpdb->get_results("SELECT * FROM wp_".$blog_id."_points_transaction where queue = 1 and agent_id = ".$uid." order by id desc");
		if(count($res2)){
			foreach ($res2 as $key => $value) {
				$value = (array)$value;
				$value['notes'] = $value['notes'];
				$value['points'] = abs($value['points']);

				/*if(!isset($endorserr[$value['endorser_id']])){
					$endorserr[$value['endorser_id']] = array('data' => [], 
						'name' => get_user_meta($value['endorser_id'], 'first_name', true).' '.get_user_meta($value['endorser_id'], 'last_name', true));
				}

				$endorserr[$value['endorser_id']]['data'][] = $value;*/

				$value['name'] = get_user_meta($value['endorser_id'], 'first_name', true).' '.get_user_meta($value['endorser_id'], 'last_name', true);

				$endorserr[] = $value;
			}
		}

		$res3 = $wpdb->get_row("SELECT sum(points) as tp FROM wp_".$blog_id."_points_transaction where queue = 1 and agent_id = ".$uid." order by id desc");

		if($id == ''){
			$response = array('status' => 'Success', 
				'avail_balance'=>$avail_balance, 
				'avail_points'=>$avail_points, 
				'points_per_dollar' => $points_per_dollar,
				'dollar_per_point' => $dollar_per_point,
				'queue_point_details' => $endorserr,
				'total_queue_points' => $res3->tp ? $res3->tp : 0
			);
			echo json_encode($response);
			die(0);
		} else {
			return $res->balance;
		}

	}
	
	function ic_endorser_points_details($id=''){
		global $wpdb;
		
		$user_id = $id ? $id : $_GET['endorser_id'];
		$blog_id = get_active_blog_for_user( $user_id )->blog_id;

		$results1 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where created like '".date("Y-m-")."%' and type in ('email_invitation', 'fbShare', 'liShare') and queue = 0 and endorser_id='".$user_id."'");
		$results2 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 0 and endorser_id='".$user_id."'");
		$results3 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 1 and endorser_id='".$user_id."'");

		$results4 = $wpdb->get_row("select * from wp_".$blog_id."_points_transaction where queue = 1 and endorser_id='".$user_id."'");

		$endorser_points1 = $results1->points ? $results1->points : 0;
		$endorser_points2 = $results2->points ? $results2->points : 0;
		$endorser_points3 = $results3->points ? $results3->points : 0;

		$response = array('status' => 'Success', 
							'msg' => 'Gift coupon request initiated, sent to your mail',
							'points' => $endorser_points2,
							'allowance' => $endorser_points1,
							'non_release_points' => $endorser_points3,
							'queue_point_details' => $results4
						);

		$response['open_status'] = $wpdb->get_row("select count(*) as cnt from wp_".$blog_id."_endorsements where open_status = 1 and endorser_id='".$user_id."'");
		$response['open_status'] = $response['open_status']->cnt ? $response['open_status']->cnt : 0;
		$response['track_status'] = $wpdb->get_row("select count(*) as cnt from wp_".$blog_id."_endorsements where track_status = 1 and endorser_id='".$user_id."'");
		$response['track_status'] = $response['track_status']->cnt ? $response['track_status']->cnt : 0;

		$response['redeemlist'] = $wpdb->get_resuts("select * from wp_".$blog_id."_points_transaction where type = 'Redeem Point' and endorser_id='".$user_id."'");
		$response['share_details'] = $wpdb->get_resuts("select * from wp_".$blog_id."_points_transaction where type != 'Redeem Point' and endorser_id='".$user_id."'");

		$response['chat_conversion'] = $wpdb->get_row("select count(*) as cnt from wp_leads where chat_conversion = 1 and endorser_id='".$user_id."'");
		$response['chat_conversion'] = $response['chat_conversion']->cnt ? $response['chat_conversion']->cnt : 0;
		$response['meeting_conversion'] = $wpdb->get_row("select count(*) as cnt from wp_".$blog_id."_meeting_participants where meeting_conversion = 1 and endorser='".$user_id."'");
		$response['meeting_conversion'] = $response['meeting_conversion']->cnt ? $response['meeting_conversion']->cnt : 0;
		

		if($id){
			return $response;
		} else {
			echo json_encode($response);
			die(0);
		}
		
	}

	function ic_wallet_purchase_transaction(){
		global $wpdb;

		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');
		$res = $wpdb->get_results("SELECT * FROM wp_".$blog_id."_agent_wallet order by id desc");

		$nres = array();
		foreach ($res as $key => $value) {
			$value = (array)$value;
			$value['notes'] = $value['amount'] < 0 ? array('description' => $value['notes']) : unserialize($value['notes']);
			$nres[] = $value;
		}

		$response = array('status' => 'Success', 'data' => $nres);
		
		echo json_encode($response);
		die(0);
	}

	function ic_add_agent_wallet($notes = ''){
		global $wpdb, $ntm_mail;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$user = $_POST['agent_id'];
		$blog_id = get_active_blog_for_user($user)->blog_id;

		$points_per_dollar = get_blog_option($blog_id, 'points_per_dollar');
		
		$dollar_per_point = 1/$points_per_dollar;

		$amt = $dollar_per_point * $_POST['points'] * 100;
		$points = $_POST['points'];
		

		$balance = $this->ic_agent_balance($user);

		$wpdb->insert("wp_". $blog_id ."_agent_wallet", 
				array(
					'agent_id' => $user,
			  		'points' => $points,
			  		'amount' => $amt,
			  		'balance' => $balance+$amt,
			  		'notes' => serialize($notes),
			  		'created' => date('Y-m-d H-i-s')
				)
		);

		/* Checking queue transaction*/
		$res = $wpdb->get_results("SELECT * FROM wp_".$blog_id."_points_transaction where queue = 1 and agent_id = ".$user." order by id desc");
		
		if(count($res)){
			foreach ($res as $key => $value) {
				$balance = $this->ic_agent_balance($user);
				$point_value = $this->ic_get_point_value($value->points) * 100;
				if($balance >= $point_value){
					$wpdb->insert("wp_". $blog_id ."_agent_wallet", 
						array(
							'agent_id' => $user,
					  		'points' => $value->points,
					  		'balance' => $balance-$point_value,
					  		'amount' => -$point_value,
					  		'endorser_id' => $value->endorser_id,
					  		'transaction_id' => $value->id,
					  		'notes' => 'Debited - Queue Transaction',
					  		'transaction_id' => $value->id,
					  		'created' => date('Y-m-d H-i-s')
						)
					);

					$wpdb->update("wp_". $blog_id ."_points_transaction", array('queue' => 0), array('id' => $value->id));

					$user_info1 = get_userdata($value->endorser_id);

					$template1 = 'You now have access to your points, please proceed link to redeem your points.';
					
					if(isset($user_info1->user_email)){
					$ntm_mail->send_mail($user_info1->user_email, 'Your pending point released', $template1, '', '');
					}
				}
			}
		}

		update_user_meta($user,'disable_agent_app', 0);
	}

	function ic_resend_autologin_link(){
		global $ntm_mail, $wpdb;

		$userpass = wp_generate_password( $length=12, $include_standard_special_chars=false );
		$user_info = get_userdata($_POST['id']);
		$username = $user_info->user_login;
		$video = $_POST['video'];

		$user_id  = $_POST['id'];
		$blog_id = get_active_blog_for_user( $user_id )->blog_id;
		$agent_id = get_blog_option($blog_id, 'agent_id');

		if($_POST['bot']){
			update_user_meta($_POST['id'], 'campaign', $_POST['bot']);
		}

		$link = '';

		if($_POST['act'] == 'create_link'){
			if($_POST['opt'] == 'expire'){
				$autologin = $username.'#exp-'.$_POST['id'].'-'.strtotime('now');
			} else {
				wp_set_password( $userpass, $_POST['id'] );
				$autologin = $username.'#'.$userpass;
			}


			$dt = array(
				'messagetxt' => $_POST['landingPageContent'],
				'videoURL' => $video,
				'endorser_id' => $user_id,
				'agent_id' => $agent_id,
				'bot_id' => $_POST['bot'],
				'autologin' => base64_encode(base64_encode($autologin))
			);
			$wpdb->insert("wp_short_link", 
				array(
					'link' => '',
			  		'params' => serialize($dt),
			  		'endorser_id' => $user_id,
					'agent_id' => $agent_id
				)
			);
			$link = get_permalink($_POST['bot']).'?detailInfo='.$wpdb->insert_id;
		} else {
			if($_POST['opt'] == 'expire'){
				$ntm_mail->send_welcome_mail($user_info->user_email, $_POST['id'], $username.'#exp-'.$_POST['id'].'-'.strtotime('now'), $video);
			} else {
				wp_set_password( $userpass, $_POST['id'] );
				$ntm_mail->send_welcome_mail($user_info->user_email, $_POST['id'], $username.'#'.$userpass, $video);
			}
		}
		
		$response = array('status' => 'Success', 'link' => $link);
		
		echo json_encode($response);
		die(0);
	}

	function ic_resend_auto_link(){
		global $ntm_mail;

		$userpass = wp_generate_password( $length=12, $include_standard_special_chars=false );
		$user_info = get_userdata($_GET['id']);
		$username = $user_info->user_login;
		wp_set_password( $userpass, $_GET['resend_welcome_email'] );
		$ntm_mail->send_welcome_mail($user_info->user_email, $_GET['id'], $username.'#'.$userpass);

		$response = array('status' => 'Success');
		
		echo json_encode($response);
		die(0);
	}

	function ic_endorser_redeemed_list(){
		global $wpdb;

		$blog_id = get_active_blog_for_user( $_GET['id'] )->blog_id;

		$res = $wpdb->get_results("SELECT * FROM wp_".$blog_id."_points_transaction where type = 'Redeem Point' and endorser_id = ".$_GET['id']." order by id desc");

		$data = array();
		foreach ($res as $key => $value) {
			$value = (array)$value;
			$value['notes'] = unserialize($value['notes']);
			$value['points'] = abs($value['points']);
			$data[] = $value;
		}

		$response = array('status' => 'Success', 'data' => $data);
		
		echo json_encode($response);
		die(0);
	}

	function ic_add_timeline_notes(){
		global $wpdb;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$wpdb->insert($wpdb->prefix ."notes", 
				array(
					'agent_id' => $_POST['agent_id'],
			  		'lead_id' => $_POST['lead_id'] ? $_POST['lead_id'] : 0,
			  		'endorser_id' => $_POST['endorser_id'] ? $_POST['endorser_id'] : 0,
			  		'notes' => isset($_POST['notes']) ? $_POST['notes'] : '',
			  		'events' => isset($_POST['events']) ? $_POST['events'] : '',
			  		'created' => date('Y-m-d H-i-s')
				)
		);

		$this->track_api('notes_added', $blog_id, $_POST['endorser_id'] ? $_POST['endorser_id'] : $_POST['lead_id'], array('notes_id' => $wpdb->insert_id, 'is_lead' => !!$_POST['lead_id'] ));

		$response = array('status' => 'Success');
		
		echo json_encode($response);
		die(0);
	}

	function ic_timeline_notes(){
		global $wpdb;
		if($_GET['type'] == 'lead'){
			$blog_id = get_current_blog_id();
			$group = $wpdb->get_results("SELECT created, month(created) as mn, YEAR(created) as yr FROM wp_".$blog_id."_notes where lead_id = ".$_GET['id']." GROUP by month(created), YEAR(created)");

			$data = array();

			foreach ($group as $key => $value) {
				$data[date("F, Y", strtotime($value->created))] = $wpdb->get_results("SELECT * FROM wp_".$blog_id."_notes where lead_id = ".$_GET['id']." and month(created) = ".$value->mn." and YEAR(created)= ".$value->yr." order by id desc");
			}

			$response = array('status' => 'Success', 'data' => $data);
		} elseif($_GET['type'] == 'endorser'){
			$blog_id = get_active_blog_for_user( $_GET['id'] )->blog_id;
			$group = $wpdb->get_results("SELECT created, month(created) as mn, YEAR(created) as yr FROM wp_".$blog_id."_notes where endorser_id = ".$_GET['id']." GROUP by month(created), YEAR(created)");

			$data = array();


			foreach ($group as $key => $value) {
				$data[date("F, Y", strtotime($value->created))] = $wpdb->get_results("SELECT * FROM wp_".$blog_id."_notes where endorser_id = ".$_GET['id']." and month(created) = ".$value->mn." and YEAR(created)= ".$value->yr." order by id desc");
			}

			$response = array('status' => 'Success', 'data' => $data);
		} else{
			$response = array('status' => 'Error', 'msg' => 'Invalid data');
		}
		
		echo json_encode($response);
		die(0);
	}


	function ic_endorser_profile(){
		global $wpdb;

		$blog_id = get_active_blog_for_user( $_GET['id'] )->blog_id;

		$total_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_endorsements where type!='Redeem Point' and endorser_id = ".$_GET['id']);

		$redeem_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_endorsements where type='Redeem Point' and endorser_id = ".$_GET['id']);

		$invitations = $wpdb->get_row("select count(*) as count from wp_".$blog_id."_endorsements where endorser_id = ".$_GET['id']);

		$open = $wpdb->get_row("select count(*) as count from wp_".$blog_id."_endorsements where open_status=1 and endorser_id = ".$_GET['id']);

		$clicked = $wpdb->get_row("select count(*) as count from wp_".$blog_id."_endorsements where track_status=1 and endorser_id = ".$_GET['id']);

		$fb_invitation = get_user_meta($_GET['id'], "tracked_fb_invitation", true);

		$tw_invitation = get_user_meta($_GET['id'], "tracked_tw_invitation", true);

		$leads = $wpdb->get_results("select * from wp_leads where endorser_id = ".$_GET['id']);

		$data = array(
			'total_points' => $total_points->points ? $total_points->points : 0,
			'redeem_points' => $redeem_points->points ? $redeem_points->points : 0,
			'invitation_sent' => $invitations->count ? $invitations->count : 0,
			'invitation_open' => $open->count ? $open->count : 0,
			'invitation_clicked' => $clicked->count ? $clicked->count : 0,
			'fb_invitation' => $fb_invitation ? $fb_invitation : 0,
			'tw_invitation' => $tw_invitation ? $tw_invitation : 0,
			'leads' => $leads
		);

		$response = array('status' => 'Success', 'data' => $data);
		
		echo json_encode($response);
		die(0);
	}

	function ic_get_bio(){
		$response = array('status' => 'Success', 'data' => get_option('ic_blog_info'));
		
		echo json_encode($response);
		die(0);
	}

	function ic_save_bio(){
		$response = array('status' => 'Success', 'data' => get_option('ic_blog_info'));
		
		echo json_encode($response);
		die(0);
	}


	function get_endorser_invitation(){
		global $wpdb;

		$blog_id = get_active_blog_for_user( $_GET['id'] )->blog_id;

		$results = $wpdb->get_results("select * from wp_".$blog_id."_endorsements where endorser_id = ".$_GET['id']);

		$new_results = array();
		foreach ($results as $key => $value) {
			$value = (array) $value;
			$user = get_user_by( 'email', $value['email'] );
			if(isset($user->ID)){
				$value['user_id'] = $user->ID;
			} else {
				$value['user_id'] = 0;
			}
			$new_results[] = $value;
		}

		$response = array('status' => 'Success', 'data' => $new_results);
		
		echo json_encode($response);
		die(0);
	}

	function get_user_activity(){
		global $wpdb;

		$recordsTotal = $wpdb->get_results("select * from tracking_log where user_id = ".$_GET['id']);
		
		$type = (isset($_GET['type']) && $_GET['type']) ? $_GET['type'] : 'all';

		$results = array();
		foreach ($recordsTotal as $key => $value) {

			$vaue = (array)$value;

			$vaue['input_data'] = unserialize($vaue['input_data']);
			$vaue['output_data'] = unserialize($vaue['output_data']);
			
			if($type == 'all'){
				$results[] = $vaue;
			} elseif($type == 'success' && (!isset($vaue['output_data']['status']) || $vaue['output_data']['status'] == 'Success')){
				$results[] = $vaue;
			} elseif($type == 'error' && (isset($vaue['output_data']['status']) && $vaue['output_data']['status'] == 'Success')){
				$results[] = $vaue;
			}
			
		}

		$response = array('status' => 'Success', 'data' => $results);
		
		echo json_encode($response);
		die(0);
	}

	function track_api($api, $blog_id, $user_id, $ip=array(), $op=array()){
		global $wpdb;

		$wpdb->insert('tracking_log', 
				array(
					'api' => $api,
					'blog_id' => $blog_id,
					'user_id' => $user_id,
					'track_time' => date('Y-m-d H:i:s'),
					'input_data' => serialize($ip),
					'output_data' => serialize($op)
				)
		);
	}

	function ic_track_invitation_open(){

		global $wpdb;

		$track_link = explode("#&$#", base64_decode(base64_decode($_GET['ref'])));

		if(count($track_link) == 3)
		{
			$blog_id = get_active_blog_for_user( $track_link[1] )->blog_id;
			$wpdb->update("wp_".$blog_id."_endorsements", array(
				"open_status" => 1,
				"open_time" => date('Y-m-d H:i:s'),
		), array('id' => $track_link[0]));

			$this->track_api('ic_track_invitation_open', $blog_id, $track_link[1]);
		}

		header('Content-Type: image/gif');
		echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
		exit;
	}

	function ic_get_predefined_notes(){
		global $wpdb;

		
		$recordsTotal = $wpdb->get_results("select * from predefined_notes where campaign_id = ".$_GET['campaign_id']);
		
		$response = array('status' => 'Success', 
							'data' => $recordsTotal
						);
		
		echo json_encode($response);
		die(0);
	}

	function ic_notes_action(){
		global $wpdb;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		
		$perform = $_GET['perform'];
		$vmsg = $_POST;
		$msg_id = '';

		if($perform == 'add'){
			$vmsg['created'] = date('Y-m-d H:i:s');
			$res = $wpdb->insert("predefined_notes", $vmsg);
			$msg_id = $wpdb->insert_id;
		} elseif($perform == 'update') {
			$msg_id = $vmsg['id'];
			unset($vmsg['msg_id']);
			$res = $wpdb->update("predefined_notes", $vmsg, array('id' => $msg_id));
		} elseif($perform == 'delete') {
			$msg_id = $vmsg['id'];
			$res = $wpdb->delete("predefined_notes", array("id" => $msg_id));
		}
		
		if($msg_id){
			$response = array('status' => 'Success', 'id' => $msg_id);
		} else {
			$response = array('status' => 'Error', 'msg' => 'Try again later!!');
		}
		echo json_encode($response);
		die(0);
	}

	function ic_follow_up_email(){
		global $wpdb, $ntm_mail;

		$data = (array)get_users(array('role' => 'endorser'));
		$cnt = 0;
		foreach ($data as $key => $value) {
			$status = get_user_meta($value->ID, 'end_follow_up', true);

			if(!$status){

				$blog_id = get_active_blog_for_user($value->ID)->blog_id;
				$agent_id = get_blog_option($blog_id, 'agent_id');
				$agent_info = get_userdata($agent_id);
				$campaign = get_user_meta($value->ID, 'campaign', true);
				$templates = $wpdb->get_row("select * from ".$wpdb->prefix."campaign_templates where name = 'Followup mail' and campaign_id=".$campaign);

				$date1=date_create($value->user_registered);
				$date2=date_create(date('Y-m-d'));
				$diff=date_diff($date1,$date2);

				$subject = stripslashes(stripslashes($templates->subject)) ? stripslashes(stripslashes($templates->subject)) : 'Welcome to financialinsiders';
				$preheader_text = stripslashes(stripslashes($templates->preheader_text));
				$content = str_replace("<br />", "", stripslashes(stripslashes($templates->template)));

				$content 	=	str_ireplace('[ENDORSER]', get_user_meta($value->ID, 'first_name', true).' '.get_user_meta($value->ID, 'last_name', true), $content);
				$content 	=	str_ireplace('[AGENT]', $agent_info->user_login, $content);
				$content 	=	str_ireplace('[AGENT_EMAIL]', $agent_info->user_email, $content);				
				$content	= 	str_ireplace('[SITE]', get_option('blogname'), $content);
				$content	= 	str_ireplace('[DAYS]', $diff->format("%a"), $content);
				
				$fromName = get_option('blogname');
				$fromEmail = get_option('admin_email');		
				$message	=	$ntm_mail->get_mail_template($content, $preheader_text);
							
				if($ntm_mail->send_mail($value->user_email, $subject , $message, $fromName, $fromEmail )){
					$cnt++;
					$this->track_api('ic_follow_up_email', $blog_id, $value->ID);
				}
			}
		}

		$response = array('status' => 'Success', 
								'msg' => $cnt.' follow up mail sent'
							);
		echo json_encode($response);
		die(0);
	}

	function ic_send_giftbit_campaign(){
		global $wpdb;

		$_POST = (array) json_decode(file_get_contents('php://input'));

		$points = $_POST['points'];
		$user_id = $_POST['user_id'];

		$blog_id = get_active_blog_for_user($user_id)->blog_id;
		$agent_id = get_blog_option($blog_id, 'agent_id');
		$response = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 0 and endorser_id=".$user_id);
		$avail_points = $response->points ? $response->points : 0;

		if($points > $avail_points){
			$response = array('status' => 'Error', 
								'msg' => 'Invalid Point selection'
							);
		}
		else {
			$gift_id = $_POST['brand_code'];
			$option = get_option('giftbit');
			
			$headers = array('Authorization: Bearer '.$option['api']);
			$amount = ($points / get_option('points_per_dollar')) * 100;
			$user_info = get_userdata($user_id);

			$headers = array('Authorization: Bearer ' . $option['api'], 'Accept: application/json', 'Content-Type: application/json');
			$data_string = array(
							 'subject' => 'Endorser Gift',
							 'message' => 'Test message',
							 'gift_template' => 'NORLZ',
							 'contacts' => array(
							 	array(
							 		'firstname' => get_user_meta( $user_id, 'first_name', true), 
							 		'lastname' => get_user_meta( $user_id, 'last_name', true), 
							 		'email' => $user_info->user_email)
							 	),
							 'price_in_cents' => $amount,
							 'expiry' => date('Y-m-d', strtotime('+6 months')),
							 "brand_codes" => [$gift_id],
							 'delivery_type' => 'GIFTBIT_EMAIL',
							 'id' => time()
							);
			//echo json_encode($data_string);				
			if(isset($option['sandbox']))
				$ch = curl_init("https://testbedapp.giftbit.com/papi/v1/campaign");
			else	
				$ch = curl_init("https://api.giftbit.com/papi/v1/campaign");
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_string));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$curl_response2 = curl_exec($ch);
			curl_close($ch);
			
			$gift_response = (array)json_decode($curl_response2);

			if($gift_response['status'] == 200){
				$option = get_option('giftbit');
				$option['amount'] = $option['amount'] - $amount;
				update_option("giftbit", $option);

				$data_string['uuid'] = $gift_response['campaign']->uuid;

				$data = array(
								'endorser_id' =>$user_id,
								'agent_id' => $agent_id,
								'points' => -$points,
								'type' => 'Redeem Point',
								'notes' => serialize($data_string),
								'created'	=> date("Y-m-d H:i:s")
								);
				$wpdb->insert("wp_".$blog_id."_points_transaction", $data);

				$results1 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where created like '".date("Y-m-")."%' and type in ('email_invitation', 'fbShare', 'liShare') and queue = 0 and endorser_id='".$user_id."'");
				$results2 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 0 and endorser_id='".$user_id."'");
				$results3 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 1 and endorser_id='".$user_id."'");

				$endorser_points1 = $results1->points ? $results1->points : 0;
				$endorser_points2 = $results2->points ? $results2->points : 0;
				$endorser_points3 = $results3->points ? $results3->points : 0;


				$response = array('status' => 'Success', 
									'msg' => 'Gift coupon request initiated, sent to your mail',
									'points' => $endorser_points2,
									'allowance' => $endorser_points1,
									'non_release_points' => $endorser_points3,
								);
				$this->track_api('ic_send_giftbit_campaign', $blog_id, $user_id, array('points' => $points, 'brand' => $gift_id), $response);
			} else {
				$response = array('status' => 'Error', 
									'msg' => $gift_response['message']
								);
				$this->track_api('ic_send_giftbit_campaign', $blog_id, $user_id, array('points' => $points, 'brand' => $gift_id), $response);
			}
			
		}

		
		echo json_encode($response);
		die(0);
	}

	function ic_get_giftbit_region(){

		$option = get_option('giftbit');
		
		$headers = array('Authorization: Bearer '.$option['api']);
		
		if(isset($option['sandbox']))
			$ch = curl_init("https://testbedapp.giftbit.com/papi/v1/marketplace/region");
		else	
			$ch = curl_init("https://api.giftbit.com/papi/v1/marketplace/region");
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$curl_response = curl_exec($ch);
		curl_close($ch);
        
        $regions = array();
		$subregions = array();
		foreach(json_decode($curl_response)->regions as $res)
		{
			if(isset($res->parent_id))
				$subregions[] = $res;
			else
				$regions[] = $res;
		}
		
		$response = array('status' => 'Success', 
							'data' => array(
								'region' => $regions,
								'subregion' => $subregions
							)
						);
		echo json_encode($response);
		die(0);
	}

	function ic_get_giftbit_brands(){
		$option = get_option('giftbit');
		
		$headers = array('Authorization: Bearer '.$option['api']);

		if(isset($option['sandbox']))
				$ch = curl_init("https://testbedapp.giftbit.com/papi/v1/brands/?min_price_in_cents=".$_GET['min_amount']."&max_price_in_cents=".$_GET['max_amount']."&region=".$_GET['region']);
			else	
				$ch = curl_init("https://api.giftbit.com/papi/v1/brands/?min_price_in_cents=".$_GET['min_amount']."&max_price_in_cents=".$_GET['max_amount']."&region=".$_GET['region']);
			
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$curl_response3 = curl_exec($ch);
			curl_close($ch);

			$response = array('status' => 'Success', 
							'data' => json_decode($curl_response3)
						);
		echo json_encode($response);
		die(0);
	}

	function ic_get_tmp_userbk(){
		global $wpdb;

		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');

		$recordsTotal = $wpdb->get_results("select * from tmp_user where agent_id=$agent_id and status = 0");
		$start = $_GET['start'];
		$length = $_GET['length'];
		$offset = $start * $length;
		$order = $_GET['columns'][$_GET['order'][0]['column']]['data'];
		$orderby = $_GET['order'][0]['dir'];
		$recordsFiltered = $wpdb->get_results("select * from tmp_user where agent_id=$agent_id and status = 0 order by $order $orderby limit $offset, $length ");

		$response = array('status' => 'Success', 
							'data' => $recordsFiltered,
						  	'recordsTotal' => count($recordsTotal),
						  	'recordsFiltered' => count($recordsFiltered),
						);
		echo json_encode($response);
		die(0);
	}

	function ic_update_user_status(){
		global $wpdb, $ntm_mail;

		$_POST = (array) json_decode(file_get_contents('php://input'));

		$results = $wpdb->update("tmp_user", array(
				'status' => $_POST['status']
			), array('id' => $_POST['id'])
		);

		$blog_id = get_active_blog_for_user($_POST['id'])->blog_id;
		$this->track_api('ic_update_user_status', $blog_id, 0, array('tmp_id' => $_POST['id'],'status' => $_POST['status']));

		if($_POST['status'] == 2){
			$tmp_user = (array)$wpdb->get_row("select * from tmp_user where id='".$_POST['id']."'");
			$user = array();
			$user['role'] = 'endorser';
			$user['user_email'] = $tmp_user['email'];
			$user['user_login'] = strtolower($tmp_user['firstname'].'_'.$tmp_user['lastname']);
			
			$user_id = username_exists( $user['user_login'] );
			if ( !$user_id and email_exists($user['user_email']) == false ) {
				$user['user_pass'] = wp_generate_password( $length=12, $include_standard_special_chars=false );
				$user_id = wp_insert_user( $user ) ;
				if (  is_wp_error( $user_id ) ) {
					$response = array('status' => 'Error', 'msg' => 'Something went wrong. Try Again!!!.');
				}
				else
				{
					update_user_meta($user_id, 'endorser_letter', $_POST['endorser_letter']);
					update_user_meta($user_id, 'endorsement_letter', $_POST['endorsement_letter']);
					$ntm_mail->send_welcome_mail($user['user_email'], $user_id, $user['user_login'].'#'.$user['user_pass']);
					$ntm_mail->send_notification_mail($user_id);

					$response = array('status' => 'Success', 'msg' => 'User created successfully.');
					$this->track_api('ic_update_user_status', $blog_id, $user_id, array('status' => $_POST['status']), $response);
				}
			} else {
				$response = array('status' => 'Error', 'msg' => 'User already exists.  Password inherited.');
				$this->track_api('ic_update_user_status', $blog_id, 0, array('tmp_id' => $_POST['id'],'status' => $_POST['status']), $response);
			}
		} else {

		}
		
		echo json_encode($response);
		die(0);
	}

	function ic_endorser_register() {
		global $ntm_mail;

		$user = isset($_POST['firstname']) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$user['role'] = 'endorser';
		$user['user_email'] = $user['user_email'];
		$user['user_login'] = strtolower($user['firstname'].'_'.$user['lastname']).rand(1111,9999);
		$this->track_api('ic_endorser_register', $blog_id, $_POST['agent_id'], $res);
		if(isset($user['agent_id'])) {	
			$agent_id = $user['agent_id'];	
			$siteID = get_active_blog_for_user( $user['agent_id'] )->blog_id;
			switch_to_blog( $siteID );
		}
		if(isset($user['agentID'])) {	
			$agent_id = $user['agentID'];	
			$siteID = get_active_blog_for_user( $user['agentID'] )->blog_id;
			switch_to_blog( $siteID );
		}
		$user_id = username_exists( $user['user_login'] );
		if ( !$user_id) {
			$user_id = email_exists($user['user_email']) ;
			if($user_id){
				$exist = 1;
				$password = wp_generate_password( $length=12, $include_standard_special_chars=false );
				wp_set_password( $password, $user_id);
				$user_info = get_userdata($user_id);
      			$user_login = $user_info->user_login;
				$autologin = $user_login.'#'.$password;
			} else {
				$exist = 0;
				if(isset($user['password'])) {
					$user['user_pass'] = $user['password'];
				} else {
					$user['user_pass'] = wp_generate_password( $length=12, $include_standard_special_chars=false );
				}
				$autologin = $user['user_login'].'#'.$user['user_pass'];
				$user_id = wp_insert_user( $user ) ;
			}
			
			if (  is_wp_error( $user_id ) ) {
				$response = array('status' => 'Error', 'msg' => 'Something went wrong. Try Again!!!.');
			}
			else
			{
				update_user_meta($user_id, 'agent_id', $agent_id);
				update_user_meta($user_id, 'first_name', $user['first_name']);
				update_user_meta($user_id, 'last_name', $user['last_name']);
				update_user_meta($user_id, 'campaign', $user['bot']);
				update_user_meta($user_id, 'issuePoints', 0);
				//$ntm_mail->send_welcome_mail($user['user_email'], $user_id, $user['user_login'].'#'.$user['user_pass']);
				//$ntm_mail->send_notification_mail($user_id);
				
				$response = array('status' => 'Success', 'data' => $user_id, 'msg' => 'Endorser created successfully', 'exist' => $exist, 'link' => get_permalink($user['bot']).'?autologin='.base64_encode(base64_encode($autologin)));
			}
		} else {
			$response = array('status' => 'Error', 'msg' => 'User already exists.  Password inherited.');
		}

		echo json_encode($response);
		die(0);

	}

	function ic_timekit_add_gmail() {
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$blog_id = get_active_blog_for_user($_POST['agent_id'])->blog_id;
		if(update_user_meta($_POST['agent_id'], 'timekits_gmail_email', $_POST['timekit_gmail_email'])) {
                   update_user_meta($_POST['agent_id'], 'timekits_time_zone', $_POST['timekit_time_zone']);			
                   $response = array('status' => 'Success');
                   $this->track_api('ic_timekit_add_gmail', $blog_id, $_POST['agent_id'], $_POST, $response);
			
		   } else {
			 $response = array('status' => 'Failed to update');
			 $this->track_api('ic_timekit_add_gmail', $blog_id, $_POST['agent_id'], $_POST, $response);
		   }
		   echo json_encode($response);
                   die(0);
	}


	function ic_agent_update(){
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		update_user_meta($_GET['user_id'], 'timekit_resource_id', $_POST['timekit_resource_id']);
		update_user_meta($_GET['user_id'], 'timekit_calendar_id', $_POST['timekit_calendar_id']);
		$blog_id = get_active_blog_for_user($_GET['user_id'])->blog_id;
		$this->track_api('ic_agent_update', $blog_id, $_GET['user_id'], $_POST);

		die(0);
	}

	function ic_get_agent_details(){
		$res =  array();
		$res['timekit_resource_id'] = get_user_meta($_GET['user_id'], 'timekit_resource_id', true);
		$res['timekit_calendar_id'] = get_user_meta($_GET['user_id'], 'timekit_calendar_id', true);

		echo json_encode($res);

		die(0);
	}

	function ic_agent_billing_transaction(){
		global $wpdb;

		$results = $wpdb->get_results("select * from agent_billing where user_id='".$_GET['user_id']."'");

		$response = array('status' => 'Success', 'data' => $results);
		echo json_encode($response);
		die(0);
	}

	function ic_cron_agent_billing(){
		global $wpdb;

		$users = get_users(array('userrole' => 'agent'));
		foreach ($users as $key => $value) {
			$blog_id = get_user_meta($value->ID, 'blog_id', true);
			$results = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where created like '".date("Y-m-")."%' and agent_id='".$value->ID."'");

			$amount = $results->points;
			$res = $wpdb->insert("agent_billing", array(
				'particulars' => 'Bill for the month of '.date("F"),
				'amount' => $amount,
				'credit' => 1,
				'agent_id' => $value->ID,
				'created' => date('Y-m-d H:i:s')
			));

			/* Debit the billing amount from agent cc will add here*/

		}
		
		die(0);
	}

	function ic_agent_save_endorsement_settings(){

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		update_user_meta($_GET['agent_id'], 'endorsement_settings', $_POST);
		$res = get_user_meta($_GET['agent_id'], 'endorsement_settings', true);


		$blog_id = get_active_blog_for_user($_GET['user_id'])->blog_id;
		$this->track_api('ic_agent_save_endorsement_settings', $blog_id, $_GET['user_id'], $_POST);
		echo json_encode($res);
		die(0);
	}

	function ic_agent_endorsement_settings(){

		$res = get_user_meta($_GET['agent_id'], 'endorsement_settings', true);
		
		if($res)echo json_encode($res);
		die(0);
	}

	function test_email(){
		global $ntm_mail;
		$ntm_mail->send_welcome_mail('dhanavel237vino@gmail.com', $_GET['id'], 'sdfsf#dfsdf');
		die(0);
	}

	function ic_strategy(){
		$args = array(
			'posts_per_page'   => -1,
			'post_type' => 'strategy'
		);
		function strategy_format($a){
		return array('id' => $a->ID, 'title' => $a->post_title, 'link' => stripslashes(get_post_meta($a->ID, 'strategy_link', true)));
		}
		$strategy = array_map("strategy_format", get_posts($args));

		echo json_encode($strategy);
		die(0);
	}

	function ic_get_template_style(){
		$mail_template_css = get_option('mail_template_css');

		echo json_encode(array('css' => stripslashes(strip_tags($mail_template_css))));
		die(0);
	}

	function ic_test_template(){
		global $ntm_mail;
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$_POST['name'] = isset($_POST['name']) ? $_POST['name'] : 'Test Campaign Template';

		$template = '<!DOCTYPE html>
			<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
			<head>
			    <meta charset="utf-8"> <!-- utf-8 works for most cases -->
			    <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn\'t be necessary -->
			    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
			    <meta name="x-apple-disable-message-reformatting">  <!-- Disable auto-scale in iOS 10 Mail entirely -->
			    <title></title> <!-- The title tag shows in email notifications, like Android 4.4. --><style>';

		$template .= stripslashes(strip_tags(get_option('mail_template_css')));

		$template .= '</style>
			    <!-- Progressive Enhancements : END -->

			    <!-- What it does: Makes background images in 72ppi Outlook render at correct size. -->
			    <!--[if gte mso 9]>
			    <xml>
			        <o:OfficeDocumentSettings>
			            <o:AllowPNG/>
			            <o:PixelsPerInch>96</o:PixelsPerInch>
			        </o:OfficeDocumentSettings>
			    </xml>
			    <![endif]-->

			</head>
			<body width="100%" style="margin: 0; mso-line-height-rule: exactly;">';

		$template .= stripslashes($_POST['template']);

		$template .= '</body></html>';

		$res = $ntm_mail->send_mail('neil.personalconsult@gmail.com', $_POST['name'], $template, '', '');
		$res = $ntm_mail->send_mail('Neil@financialinsiders.ca', $_POST['name'], $template, '', '');
		$res = $ntm_mail->send_mail('dhanavel237vino@gmail.com', $_POST['name'], $template, '', '');

		$response = array('status' => 'Success', 'res' => $res);
		echo json_encode($response);
		die(0);
	}

	function ic_get_default_campaign(){
		$response = array('status' => 'Success', 'res' => get_user_meta($_GET['user_id'], 'default_campaign', true));
		echo json_encode($response);
		die(0);
	}

	function ic_set_default_campaign(){
		global $wpdb;
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		

		if(isset($_POST['prev_id'])) {
			 $res = $wpdb->update($wpdb->prefix . "campaigns", array(			
					'is_default' => 0,
				), array('id' => $_POST['prev_id']));
		}

		$res = $wpdb->update($wpdb->prefix . "campaigns", array(
				'is_default' => 1,
				), array('id' => $_POST['new_id'])); 
		
		
		$camps = get_user_meta($_POST['user_id'], 'default_campaign', true);

		$camps = $camps ? $camps : [];

		$camps[$_POST['template']] = $_POST['new_id'];

		update_user_meta($_POST['user_id'], 'default_campaign', $camps);

		$blog_id = get_active_blog_for_user($_POST['user_id'])->blog_id;
		$this->track_api('ic_set_default_campaign', $blog_id, $_POST['user_id'], $_POST);
		
		$response = array('status' => 'Success', 'res' => get_user_meta($_POST['user_id'], 'default_campaign', true));
		echo json_encode($response);
		die(0);
	}

	function ic_video_message(){
		global $wpdb;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$_POST['created'] = date("Y-m-d H:i:s");

		$res = $wpdb->insert($wpdb->prefix . "video_message", $_POST);
		if($res){
		$response = array('status' => 'Success', 'id' => $wpdb->insert_id);
		} else {
			$response = array('status' => 'Error', 'msg' => 'Try again later!!');
		}
		echo json_encode($response);
		die(0);
	}

	function ic_video_message_delete(){
		global $wpdb;

		$results = $wpdb->delete($wpdb->prefix . "video_message", array('id' => $_GET['id']));

		$response = array('status' => 'Success');
		echo json_encode($response);
		die(0);
	}

	function ic_message_by_type(){
		global $wpdb;

		$results = $wpdb->get_results("select * from ". $wpdb->prefix . "video_message where message_type='".$_GET['type']."'");

		$response = array('status' => 'Success', 'data' => $results);
		echo json_encode($response);
		die(0);
	}
	
	function ic_video_message_by_id(){
		global $wpdb;

		$results = $wpdb->get_results("select * from ". $wpdb->prefix . "video_message where video_id='".$_GET['video_id']."'");
		$data = (array) $results;
		$response = array('status' => 'Success', 'data' => $data);
		echo json_encode($response);
		die(0);
	}

	function ic_message_with_video(){
		global $wpdb;

		$results = $wpdb->get_results("select *,l.id as video_id, v.id as msg_id, v.status_message as status_message, l.status_message as video_text from ". $wpdb->prefix . "video_message v left join ".$wpdb->prefix . "video_library l on v.video_id = l.id");

		$response = array('status' => 'Success', 'data' => $results);
		echo json_encode($response);
		die(0);
	}

	function ic_video_message_update($perform = '', $data = array()){
		global $wpdb;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		if($perform){
			$vmsg = $data;
		} else {
			$perform = $_GET['perform'];
			$vmsg = $_POST;
		}

		if($perform == 'add'){
			$res = $wpdb->insert($wpdb->prefix . "video_message", $vmsg);
		} elseif($perform == 'update') {
			$msg_id = $vmsg['msg_id'];
			unset($vmsg['msg_id']);
			$res = $wpdb->update($wpdb->prefix . "video_message", $vmsg, array('id' => $msg_id));
		} elseif($perform == 'delete') {
			$msg_id = $vmsg['msg_id'];
			$res = $wpdb->delete($wpdb->prefix . "video_message", array("id" => $msg_id));
		}
		
		if(isset($_GET['perform'])){
			if($res){
			$response = array('status' => 'Success');
			} else {
				$response = array('status' => 'Error', 'msg' => 'Try again later!!');
			}
			echo json_encode($response);
			die(0);
		}
	}

	function ic_new_video(){
		global $wpdb;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$_POST['created'] = date("Y-m-d H:i:s");

		$video_message = isset($_POST['video_message']) ? (array)$_POST['video_message'] : array();
		unset($_POST['video_message']);
		$res = $wpdb->insert($wpdb->prefix . "video_library", $_POST);
		if($res){
			$video_message['video_id'] = $wpdb->insert_id;
			if(isset($video_message['status_message'])){
				$this->ic_video_message_update('add', $video_message);
			}
			$response = array('status' => 'Success', 'id' => $video_message['video_id']);
		} else {
			$response = array('status' => 'Error', 'msg' => 'Try again later!!');
		}
		echo json_encode($response);
		die(0);
	}

	function ic_update_video(){
		global $wpdb;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$video_message = isset($_POST['video_message']) ? (array)$_POST['video_message'] : array();
		unset($_POST['video_message']);
		$res = $wpdb->update($wpdb->prefix . "video_library", $_POST, array('id'=>$_GET['id']));
		if($res){
			if(isset($video_message['status_message'])){
				$video_message['video_id'] = $_GET['id'];
				$this->ic_video_message_update('edit', $video_message);
			}
			$response = array('status' => 'Success');
		} else {
			$response = array('status' => 'Error', 'msg' => 'Try again later!!');
		}
		echo json_encode($response);
		die(0);
	}

	function ic_delete_video(){
		global $wpdb;

		$results = $wpdb->delete($wpdb->prefix . "video_library", array("id" => $_GET['id']));

		$response = array('status' => 'Success');
		echo json_encode($response);
		die(0);
	}

	function ic_video_by_id(){
		global $wpdb;

		$results = $wpdb->get_row("select * from ". $wpdb->prefix . "video_library where id=".$_GET['id']);

		$data = (array) $results;
		$data['messages'] = $wpdb->get_results("select * from ". $wpdb->prefix . "video_message where video_id=".$data['id']);

		$response = array('status' => 'Success', 'data' => $data);
		echo json_encode($response);
		die(0);
	}

	function ic_video_list(){
		global $wpdb;

		
		$results = $wpdb->get_results("select * from ". $wpdb->prefix . "video_library");

		$data = [];

		foreach ($results as $key => $value) {
			$val = (array) $value;
			$val['messages'] = $wpdb->get_results("select * from ". $wpdb->prefix . "video_message where video_id=".$val['id']);
			$data[] = $val;
		}

		$response = array('status' => 'Success', 'data' => $data);
		echo json_encode($response);
		die(0);
	}

	function ic_new_campaign(){
		global $wpdb;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		
		$complete = 0;
		
		if(isset($_POST['completed'])) {
			$complete = 1;
		}
		
		$res = $wpdb->insert($wpdb->prefix . "campaigns", array(
					'title' => $_POST['title'],
					'type' => $_POST['type'],
					//'is_default' => $_POST['is_default'],
					'is_main_site' => is_main_site(),
					'strategy' => $_POST['strategy'],
					'landing_page' => $_POST['landing_page'],
					'completed' => $complete
				));
		
		if($res) {
			$id = $wpdb->insert_id;
			foreach ($_POST['templates'] as $key => $value) {
				$value = (array) $value;
				$wpdb->insert($wpdb->prefix . "campaign_templates", array(
					'campaign_id' => $id,
					'name' => addslashes($value['name']),
					'template' => addslashes(nl2br($value['template'])),
					'media' => $value['media'] ? $value['media'] : '',
					'subject' => addslashes($value['subject']),
					'preheader_text' => addslashes($value['preheader_text'])
				));
			}


			if(is_main_site()){
				print_r($_POST['sites']);
				foreach ($_POST['sites'] as $key1 => $value1) {
					$res = $wpdb->insert("wp_".$value1."_campaigns", array(
						'title' => $_POST['title'],
						'type' => $_POST['type'],
						'landing_page' => $_POST['landing_page'],
						'strategy' => $_POST['strategy']
					));

					$id1 = $wpdb->insert_id;
					foreach ($_POST['templates'] as $key => $value) {
						$value = (array) $value;
						$wpdb->insert("wp_".$value1."_campaign_templates", array(
							'campaign_id' => $id1,
							'name' => addslashes($value['name']),
							'template' => addslashes(nl2br($value['template'])),
							'media' => $value['media'] ? $value['media'] : '',
							'subject' => addslashes($value['subject']),
							'preheader_text' => addslashes($value['preheader_text'])
						));
					}
				}
			}


			$response = array('status' => 'Success', 'id' => $id);
			$blog_id = get_current_blog_id();
			$agent_id = get_blog_option($blog_id, 'agent_id');
			$this->track_api('ic_new_campaign', $blog_id, $agent_id, $response);
		} else {
			$response = array('status' => 'Error', 'msg' => 'Try again later!!');
			$blog_id = get_current_blog_id();
			$agent_id = get_blog_option($blog_id, 'agent_id');
			$this->track_api('ic_new_campaign', $blog_id, $agent_id, $response);
		}
		
		echo json_encode($response);
		die(0);
	}

	function ic_update_campaign(){
		global $wpdb;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		
		$complete = 0;
		
		if(isset($_POST['completed'])) {
			$complete = 1;
		}
		
		$res = $wpdb->update($wpdb->prefix . "campaigns", array(
					'title' => $_POST['title'],
					'type' => $_POST['type'],
					'strategy' => $_POST['strategy'],
					'landing_page' => $_POST['landing_page'],
					'completed' => $complete,
				), array('id' => $_POST['id']));
		
		///if($res) {
			$id = $_POST['id'];
			foreach ($_POST['templates'] as $key => $value) {
				$value = (array) $value;
				if(isset($value['id'])){
					$wpdb->update($wpdb->prefix . "campaign_templates", array(
						'name' => addslashes($value['name']),
						'template' => addslashes(nl2br($value['template'])),
						'media' => $value['media'] ? $value['media'] : '',
						'subject' => addslashes($value['subject']),
						'preheader_text' => addslashes($value['preheader_text'])
					), array('id' => $value['id']));
				} else {
					$wpdb->insert($wpdb->prefix . "campaign_templates", array(
						'campaign_id' => $id,
						'name' => addslashes($value['name']),
						'template' => addslashes(nl2br($value['template'])),
						'media' => $value['media'] ? $value['media'] : '',
						'subject' => addslashes($value['subject']),
						'preheader_text' => addslashes($value['preheader_text'])
					));
				}
			}

			$response = array('status' => 'Success', 'msg' => 'Campaign updated successfully');
		/*} else {
			$response = array('status' => 'Error', 'msg' => 'Try again later!!');
		}*/
		
		echo json_encode($response);
		die(0);
	}

	function ic_delete_campaign(){
		global $wpdb;

		$wpdb->delete($wpdb->prefix . "campaigns", array( 'id' => $_GET['id'] ) );
		$wpdb->delete($wpdb->prefix . "campaign_templates", array( 'campaign_id' => $_GET['id'] ) );

		$response = array('status' => 'Success', 'msg' => 'Campaign Letter template deleted successfully');
		echo json_encode($response);
		die(0);
	}

	function ic_delete_campaign_letter(){
		global $wpdb;

		$wpdb->delete($wpdb->prefix . "campaign_templates", array( 'id' => $_GET['id'] ) );

		$response = array('status' => 'Success', 'msg' => 'Campaign Letter template deleted successfully');
		echo json_encode($response);
		die(0);
	}

	function ic_campaigns(){
		global $wpdb;

		$campaigns = [];
		
		/*if(!is_main_site()){
			$results = $wpdb->get_results("select * from wp_campaigns");
			foreach ($results as $key => $value) {
				$value = (array) $value;

				$templates = $wpdb->get_results("select * from wp_campaign_templates where campaign_id=".$value['id']);
				$value['templates'] = [];
				foreach ($templates as $key => $value2) {
					$value2->preheader_text = stripslashes(stripslashes($value2->preheader_text));
					$value2->subject = stripslashes(stripslashes($value2->subject));
					$value2->template = str_replace("<br />", "", stripslashes(stripslashes($value2->template)));
					$value['templates'][$value2->name] = $value2;
				}
				$campaigns[] = $value;
			}
		}*/

		$dropdown = '';
		if(isset($_GET['dropdown']) && $_GET['dropdown'] == 'true'){
			$dropdown = 'and completed = 1';
		}

		if(isset($_GET['type'])) {
			$type = $_GET['type'];
			$results = $wpdb->get_results("select * from ".$wpdb->prefix . "campaigns where type = '$type' $dropdown");
		} else {
			
			if(isset($_GET['default'])) {
				$results = $wpdb->get_results("select * from ".$wpdb->prefix . "campaigns where is_default = 1 $dropdown");
			} elseif($dropdown) {
				$results = $wpdb->get_results("select * from ".$wpdb->prefix . "campaigns where completed = 1");
			} else {
				$results = $wpdb->get_results("select * from ".$wpdb->prefix . "campaigns");
			}
		}


		foreach ($results as $key => $value) {
			$value = (array) $value;

			$templates = $wpdb->get_results("select * from ".$wpdb->prefix . "campaign_templates where campaign_id=".$value['id']);
			$value['templates'] = [];
			foreach ($templates as $key => $value2) {
				$value2->preheader_text = stripslashes(stripslashes($value2->preheader_text));
				$value2->subject = stripslashes(stripslashes($value2->subject));
				$value2->template = str_replace("<br />", "", stripslashes(stripslashes($value2->template)));
				$value['templates'][$value2->name] = $value2;
			}

			$campaigns[] = $value;

		}

		$response = array('status' => 'Success', 'data' => $campaigns);
		echo json_encode($response);
		die(0);
	}

	function ic_reset_password(){
		$_POST = (array) json_decode(file_get_contents('php://input'));

		if(isset($_POST['id'])){
			wp_set_password( $_POST['password'], $_POST['id'] );
			$response = array('status' => 'Success');
		} elseif(isset($_POST['token'])) {
			$ct = strtotime('now');
			$encode_token = explode("#", base64_decode($_POST['token']));
			if(count($encode_token) == 2 && ($ct - $encode_token[1]) < 3600){
				wp_set_password( $_POST['password'], $encode_token[0] );
				$response = array('status' => 'Success');
			} else {
				$response = array('status' => 'Error', 'msg' => 'Invalid token');
			}
			
		} else {
			$response = array('status' => 'Error', 'msg' => 'Invalid data');
		}

		echo json_encode($response);
		die(0);
	}

	

	function ic_forgot_password(){
		global $ntm_mail;

		$_POST = (array) json_decode(file_get_contents('php://input'));

		$password = wp_generate_password( $length=12, $include_standard_special_chars=false );

		$user = get_user_by( 'email', $_POST['email'] );

		if(isset($user->ID)){
			wp_set_password( $password, $user->ID);

			$token = base64_encode($user->ID.'#'.strtotime("now"));

			$link = strpos($_POST['link'], '?') ? $_POST['link'].'&token='.$token : $_POST['link'].'?token='.$token;

			$reset_link = '<div>
			<h2>Hi '.$user->user_login.',</h2>
			<p>Click the below link to reset your password</p>
			<a href="'.$link.'">'.$link.'</a>
			</div>';
			$ntm_mail->send_mail($_POST['email'], 'Reset your password', $reset_link);

			$response = array('status' => 'Success', 'msg' => 'Reset link sent to your email');
		}
		else {
			$response = array('status' => 'Error', 'msg' => 'Invalid Email');
		}
		echo json_encode($response);
		die(0);
	}

	function ic_change_email(){
		global $wpdb;
		$_POST = (array) json_decode(file_get_contents('php://input'));

		$user = get_user_by( 'email', $_POST['email'] );
		if(isset($user->ID)){
			$response = array('status' => 'Error', 'msg' => 'Email already exist.');
		} else {
			$wpdb->update('wp_user', array('user_email' => $_POST['email']), array('ID' => $_POST['id']));
			$response = array('status' => 'Success');
		}

		echo json_encode($response);
		die(0);
	}

	function ic_endorser_login(){
		global $wpdb, $ntm_mail;

		$_POST = (array) json_decode(file_get_contents('php://input'));

		$creds = array();
		$creds['user_login'] = $_POST['email'];
		$creds['user_password'] = $_POST['password'];
		$creds['remember'] = true;
		$current_user = wp_signon( $creds, false );

		if(!is_wp_error($current_user)) {
			$blog_id = get_active_blog_for_user( $current_user->ID )->blog_id;

			$agent_id = get_blog_option($blog_id, 'agent_id');
			$current_agent_data = get_userdata($agent_id);
    		update_user_meta( $current_user->ID, 'last_login', time() );
    		$current_user_data = get_userdata($current_user->ID);
    		$siteUrl = get_site_url(get_user_meta((int)$current_user->data->ID, 'primary_blog', true));
			$points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 0 and endorser_id=".$current_user->ID);

			$points2 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 1 and endorser_id=".$current_user->ID);
			$invitation_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where created like '".date("Y-m-")."%' and type in ('email_invitation', 'fbShare', 'liShare') and endorser_id='".$current_user->ID."'");

			$campaign = get_user_meta($current_user->ID, 'campaign', true);
			$intro_bot = get_user_meta($current_user->ID, 'intro_bot', true);
			$templates = $wpdb->get_row("select * from wp_".$blog_id."_campaign_templates where name = 'Endorsement Letter' and campaign_id=".$campaign);

			$content = str_replace("<br />", "", stripslashes(stripslashes($templates->template)));

			$mailtemplate = '<html><head><style>'.stripslashes(strip_tags(get_option('mail_template_css'))).'</style></head><body>'.$content.'</body></html>';


			$campaign = get_user_meta($current_user->ID, 'campaign', true);
			$dcampaign = $wpdb->get_row("select * from wp_".$blog_id."_campaigns where id=".$campaign);
			
			switch_to_blog($blog_id);
	        $pagelink = get_post_meta($dcampaign->strategy, 'strategy_link', true);
	        restore_current_blog();
			

			$content = str_replace("<br />", "", stripslashes(stripslashes($templates->template)));
			//$splittemplate = explode('[ENDORSERS NOTES]', $mailtemplate);
			$templates = $wpdb->get_row("select * from wp_".$blog_id."_campaign_templates where name = 'Endorser Letter' and campaign_id=".$campaign);

			$landingPageContent = get_user_meta($current_user->ID, 'landingPageContent', true);

			$video = $templates->media ? $templates->media : get_user_meta($current_user->ID, 'video', true) ;
			$endorsement_settings = get_user_meta($agent_id, 'endorsement_settings', true);
			$data = array(
					'endorser' => $current_user,
					'endorser_first_name' => $current_user_data->first_name,
					'endorser_last_name' => $current_user_data->last_name,
					'agent_first_name' => $current_agent_data->first_name,
					'agent_last_name' => $current_agent_data->last_name,
					'points' => $points->points ? $points->points : 0,
					'non_release_points' => $points2->points ? $points2->points : 0,
					'monthly_limit_points' => $invitation_points->points ? $invitation_points->points : 0,
					//'fb_ref_link' => $pagelink.'?ref='.base64_encode(base64_encode($current_user->ID.'#&$#fb')).'&video='.$video,
					//'li_ref_link' => $pagelink.'?ref='.base64_encode(base64_encode($current_user->ID.'#&$#li')).'&video='.$video,
					//'tw_ref_link' => $pagelink.'?ref='.base64_encode(base64_encode($current_user->ID.'#&$#tw')).'&video='.$video,
					//'mailtemplate' => str_replace('[ENDORSERS NOTES]', '<div id="dynamicNoteContainer" ng-click="editNote()" dynamic="bodyContent" style="background-color: white;"></div><a href="javascript:void(0)" style="float: right; top: -30px; position: relative; right: 10px;" ng-click="editNote()">Edit</a>', $content),
					'blog_id' => $blog_id,
					'agent_id' => $agent_id,
					'twitter_text' => get_option('twitter_text'),
					'points_per_dollar' => get_option('points_per_dollar'),
					'fb_text' => $dcampaign->facebook,
					'tw_text' => $dcampaign->twitter,
					'li_text' => $dcampaign->linkedin,
					'agent_avatar' => get_avatar_url($agent_id),
					'point_settings' =>  $endorsement_settings,
					'campaign' => $campaign,
					'intro_bot' => $intro_bot,
					'landing_page' => $landingPageContent,
					'strategy_link' => $pagelink,
					'video' => $video,
					'apiURL' => $siteUrl
				);
			$response = array('status' => 'Success', 'data' => $data);
		} else {
			$response = array('status' => 'Error', 'msg' => $current_user->get_error_message());
		}

		echo json_encode($response);
		die(0);
		exit;
	}

	function ic_auto_login(){
		global $wpdb, $ntm_mail;

		
		$wp_short_link = array();
		if(is_numeric($_GET['autologin'])){
			$rres = (array) $wpdb->get_row('select * from wp_short_link where id = '.$_GET['autologin']);
			$wp_short_link = (array) unserialize($rres['params']);
			$autologin = explode("#", base64_decode(base64_decode($wp_short_link['autologin'])));
			//print_r($autologin);
		} else {
			$autologin = explode("#", base64_decode(base64_decode($_GET['autologin'])));
		}

		$creds = array();
		$creds['user_login'] = $autologin[0];
		$creds['user_password'] = $autologin[1];
		$creds['remember'] = true;
		$current_user = wp_signon( $creds, false );
		
		$split = explode("-", $creds['user_password']);

		if(count($split) == 3 && $split[0] == 'exp' && ((strtotime('now') - $split[2]) / 86400) <= 7){
			$current_user = (object) array('ID' => $split[1]);
		}

		if(!is_wp_error($current_user)) {
			$blog_id = get_active_blog_for_user( $current_user->ID )->blog_id;
			$agent_id = get_blog_option($blog_id, 'agent_id');
			$agentProfileImg = get_user_meta($agent_id, 'profile_img', true);
			$agentCredentials = get_user_meta($agent_id, 'agent_designation', true);

			$li = get_user_meta($current_user->ID, 'login_link_opened', true);
			$li = $li ? $li : 0;
			update_user_meta($current_user->ID, 'login_link_opened', $li+1);

			$current_user_data = get_userdata($current_user->ID);
			$points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 0 and endorser_id=".$current_user->ID);

				$points2 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 1 and endorser_id=".$current_user->ID);
			$invitation_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where created like '".date("Y-m-")."%' and type in ('email_invitation', 'fbShare', 'liShare') and endorser_id='".$current_user->ID."'");
			

			/*$endorser_letter = get_user_meta($current_user->ID, 'endorsement_letter', true);
			if($endorser_letter)
			{
				$res = $wpdb->get_row("select * from ".$wpdb->prefix . "mailtemplates where id=".$endorser_letter);
				$mailtemplate = $res->content;
			}
			else
			{
				$mailtemplate 	 	= 	$ntm_mail->get_invitation_mail ();
				$mailtemplate = $mailtemplate['content'];
			}*/

			$campaign = get_user_meta($current_user->ID, 'campaign', true);
			
				$templates = $wpdb->get_row("select * from wp_".$blog_id."_campaign_templates where name = 'Endorsement Letter' and campaign_id=".$campaign);

				$content = str_replace("<br />", "", stripslashes(stripslashes($templates->template)));

				$mailtemplate = '<html><head><style>'.stripslashes(strip_tags(get_option('mail_template_css'))).'</style></head><body>'.$content.'</body></html>';

			$mailtemplate = '<html><head><style>'.stripslashes(strip_tags(get_option('mail_template_css'))).'</style></head><body>'.$content.'</body></html>';
			//$landingPageContent = $wpdb->get_results("select landing_page from wp_".$blog_id."_campaigns where id=".$campaign);


			update_user_meta( $current_user->ID, 'last_login', time() );
			$blog_id = get_active_blog_for_user( $current_user->ID )->blog_id;
			$agent_id = get_blog_option($blog_id, 'agent_id');
			$current_agent_user = get_userdata($agent_id);
			$campaign = get_user_meta($current_user->ID, 'campaign', true);
			$dcampaign = $wpdb->get_row("select * from wp_".$blog_id."_campaigns where id=".$campaign);
			
			switch_to_blog($blog_id);
	        $pagelink = get_post_meta($dcampaign->strategy, 'strategy_link', true);
	        restore_current_blog();

			$templates = $wpdb->get_row("select * from wp_".$blog_id."_campaign_templates where name = 'Endorser Letter' and campaign_id=".$campaign);

			$landingPageContent = get_user_meta($current_user->ID, 'landingPageContent', true);

			$video = $templates->media ? $templates->media : get_user_meta($current_user->ID, 'video', true) ;
			$endorsement_settings = get_user_meta($agent_id, 'endorsement_settings', true);
			$data = array(
					'endorser' => $current_user,
					'endorser_first_name' => $current_user_data->first_name,
					'endorser_last_name' => $current_user_data->last_name,
					'agent_first_name' => $current_agent_user->first_name,
					'agent_last_name' => $current_agent_user->last_name,
					'points' => $points->points ? $points->points : 0,
					'non_release_points' => $points2->points ? $points2->points : 0,
					'monthly_limit_points' => $invitation_points->points ? $invitation_points->points : 0,
					//'fb_ref_link' => $pagelink.'?ref='.base64_encode(base64_encode($current_user->ID.'#&$#fb')).'&video='.$video,
					//'li_ref_link' => $pagelink.'?ref='.base64_encode(base64_encode($current_user->ID.'#&$#li')).'&video='.$video,
					//'tw_ref_link' => $pagelink.'?ref='.base64_encode(base64_encode($current_user->ID.'#&$#tw')).'&video='.$video,
					//'mailtemplate' => str_replace('[ENDORSERS NOTES]', '<div id="dynamicNoteContainer" ng-click="editNote()" dynamic="bodyContent" style="background-color: white;"></div><a href="javascript:void(0)" style="float: right; top: -30px; position: relative; right: 10px;" ng-click="editNote()">Edit</a>', $content),
					'blog_id' => $blog_id,
					'agent_id' => $agent_id,
					'agent_profile_img' => $agentProfileImg,
					'agent_credentials' => $agentCredentials,
					'points_per_dollar' => get_option('points_per_dollar'),
					'twitter_text' => get_option('twitter_text'),
					'fb_text' => $dcampaign->facebook,
					'tw_text' => $dcampaign->twitter,
					'li_text' => $dcampaign->linkedin,
					'agent_avatar' => get_avatar_url($agent_id),
					'point_settings' => $endorsement_settings,
					'campaign' => $campaign,
					'landing_page' => $landingPageContent,
					'strategy_link' => $pagelink,
					'video' => $video

				);
			$response = array('status' => 'Success', 'data' => $data, 'shortlinkDetail' => $wp_short_link);
		} else {
			$response = array('status' => 'Error', 'msg' => 'Invalid link/expired!!');
		}

		echo json_encode($response);
		die(0);
		exit;
	}

	function ic_get_endorser_info(){
		global $wpdb;

		$endorser_id = $_GET['id'];
		$blog_id = get_active_blog_for_user( $endorser_id )->blog_id;
		$endorser = get_userdata($endorser_id);
		$agent_id = get_blog_option($blog_id, 'agent_id');

		//$response = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where endorser_id=".$endorser_id);
		$total_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where type!='Redeem Point' and queue = 0 and endorser_id = ".$endorser_id);

		$non_queue_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where type!='Redeem Point' and queue = 1 and endorser_id = ".$endorser_id);

		$redeem_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where type='Redeem Point' and endorser_id = ".$endorser_id);

		$fbShare = $wpdb->get_row("select count(*) as cnt from wp_".$blog_id."_points_transaction where type = 'fbShare' and endorser_id='".$endorser_id."'");

		$liShare = $wpdb->get_row("select count(*) as cnt from wp_".$blog_id."_points_transaction where type = 'liShare' and endorser_id='".$endorser_id."'");


		$invitations = $wpdb->get_row("select count(*) as count from wp_".$blog_id."_endorsements where endorser_id = ".$endorser_id);

		$open = $wpdb->get_row("select count(*) as count from wp_".$blog_id."_endorsements where open_status=1 and endorser_id = ".$endorser_id);

		$clicked = $wpdb->get_row("select count(*) as count from wp_".$blog_id."_endorsements where track_status=1 and endorser_id = ".$endorser_id);

		$chat_conversion = $wpdb->get_row("select count(*) as cnt from wp_".$blog_id."_points_transaction where type='chat_conversion' and endorser_id = ".$endorser_id);

		$meeting_conversion = $wpdb->get_row("select count(*) as cnt from wp_".$blog_id."_points_transaction where type='meeting_conversion' and endorser_id = ".$endorser_id);

		$fb_invitation = get_user_meta($endorser_id, "tracked_fb_invitation", true);

		$tw_invitation = get_user_meta($endorser_id, "tracked_tw_invitation", true);

		$leads = $wpdb->get_results("select * from wp_leads where endorser_id = ".$endorser_id);
		$last_login = get_user_meta($endorser_id, 'last_login', true);
		$the_login_date = human_time_diff($last_login);
		$data = array(
			'total_points' => $total_points->points ? $total_points->points : 0,
			'non_release_points' => $non_queue_points->points ? $non_queue_points->points : 0,
			'redeem_points' => $redeem_points->points ? $redeem_points->points : 0,
			'invitation_sent' => $invitations->count ? $invitations->count : 0,
			'invitation_open' => $open->count ? $open->count : 0,
			'invitation_clicked' => $clicked->count ? $clicked->count : 0,
			'fb_invitation' => $fb_invitation ? $fb_invitation : 0,
			'tw_invitation' => $tw_invitation ? $tw_invitation : 0,
			'fbShare' => $fbShare->cnt ? $fbShare->cnt : 0,
			'liShare' => $liShare->cnt ? $liShare->cnt : 0,
			'chat_conversion' => $chat_conversion->cnt ? $chat_conversion->cnt : 0,
			'meeting_conversion' => $meeting_conversion->cnt ? $meeting_conversion->cnt : 0,
			'leads' => $leads,
			'name' => get_user_meta($endorser_id, 'first_name', true). ' '. get_user_meta($endorser_id, 'last_name', true),
			'email' => $endorser->user_email,
			'phone' => get_user_meta($endorser_id, 'phone', true),
			'agent_id' => $agent_id,
			'site_id' => $blog_id,
			'campaign' => get_user_meta($endorser_id, 'campaign', true),
			'login_link_opened' => get_user_meta($endorser_id, 'login_link_opened', true),
			'last_login' => $the_login_date
		);

		

		$response = array('status' => 'Success', 'data' => $data);

		echo json_encode($response);
		die(0);
		exit;
	}

	function ic_get_lead_info(){
		global $wpdb;

		$lead_id = $_GET['id'];
		


		$lead_info = $wpdb->get_results("select * from wp_leads where id = ".$lead_id);
		

		

		$response = array('status' => 'Success', 'data' => $lead_info[0]);

		echo json_encode($response);
		die(0);
		exit;
	}

	function ic_update_meeting_data() {
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$event = (array) $_POST['event'];
		$meeting_id = $event['what'];

		$wpdb->update($wpdb->prefix . "meeting", 
			array(
				'event_id' => $event['id'],
				'meeting_date' => explode('+', $event['start'])[0]
			), 
			array('id' => $meeting_id));

		if($_POST['state'] == 'cancelled') {
			$wpdb->update($wpdb->prefix . "meeting_participants", 
			array(
				'status' => 4
			), 
			array(
				'id' => $meeting_id,
				'email' => $_POST['customers']['email']
			));
		}

		die(0);
		exit;
	}

	function ic_update_active_time() {
		global $wpdb;

		$wpdb->update($wpdb->prefix . "meeting", 
						array('active_time' => strtotime("now")),
						array("id" => $_GET['id'])
			);

		die(0);
		exit;
	}

	function ic_generate_token() {
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$token = opentok_token(isset($_POST['sessionId'])?$_POST['sessionId']:'');
		
		echo json_encode($token);
		die(0);
		exit;
	}

	function ic_get_active_meeting_list() {
		global $wpdb;

		$response = $wpdb->get_results("select * from ".$wpdb->prefix . "meeting a left join ".$wpdb->prefix . "meeting_participants b on a.id = b.meeting_id left join wp_leads l on a.email = l.email
			where a.meeting_date > '".date("Y-m-d H:i:s")."'");

		echo json_encode($response);
		die(0);
	}
	
	function ic_lead_meeting() {
		global $wpdb;
		$params = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		//$params = (array)json_decode(file_get_contents('php://input'));
		

		$lead = $wpdb->get_results('select * from wp_leads where id = "'. $params['lead_id'].'"');
		$type = $params['type'];
		if(count($lead)){
			$agent_id = $lead[0]->agent_id;

			switch($type) {

					case "appointment":
					$msg = "Appointment Meeting Created";
					$appointmentMeeting = $this->ic_appointment_meeting($agent_id, $params['lead_id']);
					
					$admin_id = $appointmentMeeting['admin_id'];
					$user_id = $appointmentMeeting['user_id'];

					break;

					case "instant":
					$msg = "Instant Meeting Created";
					$instantMeeting = $this->ic_instant_meeting($agent_id, $params['lead_id']);
					$emailSuccess  = $instantMeeting['email_msg'];
					$admin_id = $instantMeeting['admin_id'];
					$user_id = $instantMeeting['user_id'];
					break;
				}
				$response = array('status' => 'Success', 'msg' => $msg, 'type' => $type, 'meeting_user_id' => $user_id, 'meeting_admin_id' => $admin_id, 'email_success'=> $emailSuccess, ' test' => $params['lead_id']);


		} else {
			$response = array('status' => 'Fail', 'msg' => "No meeting was created", 'type' => $type);

		}

		echo json_encode($response);
		die(0);
	}

	function ic_new_lead_nomail() {
		global $wpdb;
		
		$lead = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		//$lead = (array)json_decode(file_get_contents('php://input'));
		
		$resuts = array();
		if($lead['email']){
			$resuts = $wpdb->get_results('select * from wp_leads where email = "'. $lead['email'].'"');
		}
		
		
		$leadtoinsert = array('endorser_id' => $lead['endorser_id'], 'email' => $lead['email'], 'first_name' => $lead['first_name'], 'last_name' => $lead['last_name'], 'agent_id' => $lead['agent_id'], 'created' => date("Y-m-d H:i:s"), 'fb_ref' => $lead['fb_ref']);
		

		if(count($resuts)){
			
			$wpdb->update("wp_leads", $leadtoinsert, array('email' => $lead['email'])); //need to update the date here
			$lead_id = $resuts[0]->id;
			$msg = 'Lead already exist, data updated';
		} else {
			
			$ress = $wpdb->insert("wp_leads", $leadtoinsert);
			//is_wp_error();
			//print_r($ress);

			$msg = 'Lead created successfully';
			$lead_id = $wpdb->insert_id;

		}


		if($lead_id) {
			if(isset($lead['type'])){ 
				$type = $lead['type'];

				switch($type) {

					case "appointment":
					$msg = "Appointment Meeting Created";
					$appointmentMeeting = $this->ic_appointment_meeting($lead['agent_id']);
					
					$admin_id = $appointmentMeeting['admin_id'];
					$user_id = $appointmentMeeting['user_id'];

					break;

					case "instant":
					$instantMeeting = $this->ic_instant_meeting($lead['agent_id'], $lead_id);
					
					$admin_id = $instantMeeting['admin_id'];
					$user_id = $instantMeeting['user_id'];

					break;

					default:
					$type = "lead";
					$msg = "inputed lead but no action taken";
					break;

				}
				$response = array('status' => 'Success', 'id' => $lead_id, 'msg' => $msg, 'type' => $type, 'meeting_user_id' => $user_id, 'meeting_admin_id' => $admin_id);
			
			} else {

				$response = array('status' => 'Success', 'id' => $lead_id, 'msg' => $msg);
			}

			
			
		} else {
			

			$response = array('status' => 'Error', 'msg' => 'Try again later!!', 'type' => $type);
		}


		
		echo json_encode($response);
		die(0);
	}

	function ic_update_lead() {
		global $wpdb;

		$lead = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$wpdb->update("wp_leads", $lead, array('id' => $_GET['email']));
		$lead_id = $wpdb->insert_id;

		if($lead_id) {
			$response = array('status' => 'Success', 'msg' => 'Lead updated successfully');
		} else {
			$response = array('status' => 'Error', 'msg' => 'Try again later!!');
		}
		
		echo json_encode($response);
		die(0);
	}

	function ic_update_meeting_date()
	{
		global $wpdb;
		
		$_POST = (array) json_decode(file_get_contents('php://input'));

		file_put_contents('log.json', json_encode($_POST));

		$where = explode('=', $_POST['where']);
		
		$agent_id = str_replace('&id', '', $where[1]);

		$siteID = get_active_blog_for_user( $agent_id )->blog_id;
		switch_to_blog( $siteID );

		$encd = explode('#',base64_decode(base64_decode($where[2])));
		
		$wpdb->update($wpdb->prefix . "meeting", array('meeting_date' => '2019-09-26 17:00:00', 'timekit_meeting_id' => $_POST['id']), array('id' => $encd[0]));

		die(0);
		exit;
	}

	function ic_update_meeting_timekit()
	{
		global $wpdb;
		
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$wpdb->update($wpdb->prefix . "meeting", array('timekit_meeting_id' => $_POST['timekit_meeting_id']), array('id' => $_POST['id']));

		die(0);
		exit;
	}

	function ic_update_meeting_eventid()
	{
		global $wpdb;
		
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$wpdb->update($wpdb->prefix . "meeting", array('event_id' => $_POST['event_id']), array('id' => $_POST['id']));

		die(0);
		exit;
	}

	function ic_appointment_meeting($agent_id)
	{
		global $wpdb;
		
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		if(isset($_POST['agent_id'])) {
			$agent_id = $_POST['agent_id'];
		}
		$meetingId = time();
		
		$opentok = opentok_token();
		
		$wpdb->insert($wpdb->prefix . "meeting", array('agent_id' => $agent_id, 'created' => date("Y-m-d H:i:s"), 'session_id' => $opentok['sessionId'], 'token' => $opentok['token']));
		$meeting_id = $wpdb->insert_id;
		
		$opentok['id'] = $meeting_id;
		$status = $_GET['st'] ? 3 : 2;

		$d = (array)$_POST['participants'];

		$d['meeting_id'] = $meeting_id;
		$d['meeting_date'] = date("Y-m-d H:i:s");
		$d['status'] = $status;

		$wpdb->insert($wpdb->prefix . "meeting_participants", $d);
		$pid = $wpdb->insert_id;

		$admin_id = base64_encode(base64_encode($meeting_id.'#0'));
		$user_id = base64_encode(base64_encode($meeting_id.'#'.$pid));
		
		$response = array('admin_id' => $admin_id, 'user_id' => $user_id, 'main_id' => $meeting_id);
		
		if(isset($_POST['agent_id'])){
			echo json_encode($response);
		
			die(0);
			exit;
		} else {
			return $response;
		}
	}
	
	function ic_instant_meeting($agent_id=0, $id=0)
	{
		global $wpdb, $ntm_mail;
		

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$meetingId = time();
		
		$meeting = $wpdb->get_results("select * from ".$wpdb->prefix . "meeting where (".strtotime("now")." - active_time) < 15 ");

		if(isset($_POST['agent_id'])) {
			$agent_id = $_POST['agent_id'];
		}
		

		if(count($meeting)){
			$nm = 'existing';
			$meeting_id = $meeting[0]->id;
		} else {
			
			$opentok = opentok_token();
			
			$wpdb->insert($wpdb->prefix . "meeting", array('agent_id' => $agent_id, 'created' => date("Y-m-d H:i:s"), 'session_id' => $opentok['sessionId'], 'token' => $opentok['token']));
			$nm = 'new';
			$meeting_id = $wpdb->insert_id;
			
		}
		
		$opentok['id'] = $meeting_id;
		$status = $_GET['st'] ? 3 : 2;

		if(!$id) {
			foreach($_POST['participants'] as $d){
			$d = (array)$d;
			$d['meeting_id'] = $meeting_id;
			$d['meeting_date'] = date("Y-m-d H:i:s");
			$d['status'] = $status;

			$wpdb->insert($wpdb->prefix . "meeting_participants", $d);
			$pid = $wpdb->insert_id;
		}
		} else{
			$lead = $wpdb->get_row('select * from wp_leads where id = '.$id);
			$d['meeting_id'] = $meeting_id;
			$d['meeting_date'] = date("Y-m-d H:i:s");
			$d['status'] = $status;
			$d['email'] = $lead->email;
			$d['name'] = $lead->first_name.' '.$lead->last_name;
			$wpdb->insert($wpdb->prefix . "meeting_participants", $d);
			$pid = $wpdb->insert_id;
			
		}
		
		
		$finonce = time().rand(11111,99999);
		setcookie('finonce', $finonce);

		$admin_id = base64_encode(base64_encode($meeting_id.'#0'));
		$user_id = base64_encode(base64_encode($meeting_id.'#'.$pid)); // I did it here.
		if($id) {
			
 			$message = 'Here is your meeting link. <br><br><a href="'.site_url().'/meeting?id='.$user_id.'">Click here to start your meeting</a>';
				$subject = "Financial Insiders Meeting Link";
		
				$emailMsg = "Email Sent";
				$ntm_mail->send_mail($lead->email, 'Meeting Link', $message);
		}

		$response = array('user_id' => $user_id, 'admin_id' => $admin_id, 'finonce' => $finonce, 'pid' => $wpdb->insert_id, 'status' => $nm, 'email_msg' => $emailMsg, 'test' => $lead);
		if($id) {
			return $response;
		} else {
			

			echo json_encode($response);
			
			die(0);
			exit;
		}
	}




	function ic_update_fb_id(){
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		update_user_meta($_POST['user_id'], 'firebase_id', $_POST['firebase_id']);

		$response = array('status' => 'Success');
		echo json_encode($response);
		die(0);
	}

	function ic_get_fb_id(){
		$firebase_id = get_user_meta($_GET['user_id'], 'firebase_id', true);

		$response = array('status' => 'Success', 'firebase_id' => $firebase_id);
		echo json_encode($response);
		die(0);
	}

	function ic_get_points(){
		global $wpdb;

		$blog_id = get_active_blog_for_user( $_GET['endorser_id'] )->blog_id;

		$response = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 0 and endorser_id=".$_GET['endorser_id']);

		$response2 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 1 and endorser_id=".$_GET['endorser_id']);

		$results = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where created like '".date("Y-m-")."%' and type in ('email_invitation', 'fbShare', 'liShare') and endorser_id='".$_GET['endorser_id']."'");

		$endorser_points = $results->points ? $results->points : 0;

		$response = array('status' => 'Success', 
			'points' => $response->points ? $response->points : 0, 
			'non_release_points' => $response2->points ? $response2->points : 0, 
			'allowance' => $endorser_points);
		echo json_encode($response);
		die(0);
	}

	function ic_get_points_by_type(){
		global $wpdb;

		$blog_id = get_active_blog_for_user( $_GET['endorser_id'] )->blog_id;

		$response = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where type = '".$_GET['type']."' and endorser_id=".$_GET['endorser_id']);

		$response = array('status' => 'Success', 'points' => $response->points ? $response->points : 0, 'allowance' => $endorser_points);
		echo json_encode($response);
		die(0);
	}

	function ic_add_points(){
		global $wpdb, $ntm_mail;

		//$_POST = (array) json_decode(file_get_contents('php://input'));

		$blog_id = get_active_blog_for_user( $_POST['endorser_id'] )->blog_id;

		if($_POST['type']){
			

			if($_POST['type'] == 'fbShare'){
				$type = 'fb_point_value';
			} elseif($_POST['type'] == 'liShare'){
				$type = 'li_point_value';
			} else{
				$type = $_POST['type'].'_point_value';
			}

			$agent_id = get_blog_option($blog_id, 'agent_id');
			$settings = get_user_meta($agent_id, 'endorsement_settings', true);
			$points = $settings[$type] ;

			$monthly_invitation_allowance = $settings['monthly_invitation_allowance'];
			$results = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where created like '".date("Y-m-")."%' and type in ('email_invitation', 'fbShare', 'liShare') and endorser_id='".$_POST['endorser_id']."'");

			$endorser_points = $results->points ? $results->points : 0;

			if($endorser_points < $monthly_invitation_allowance){

				if(($points + $endorser_points) > $monthly_invitation_allowance){
					$points = $monthly_invitation_allowance - $endorser_points;
				}

				$endorser_points = $endorser_points + $points;


				$balance = $this->ic_agent_balance($agent_id);
				$point_value = $this->ic_get_point_value($points) * 100;
				$queue = $balance >= $point_value ? 0 : 1;

				$data = array(
								'endorser_id' =>$_POST['endorser_id'],
								'agent_id' => $agent_id,
								'points' => $points,
								'queue' => $queue, 
								'type' => $_POST['type'],
								'notes' => $_POST['notes'],
								'created'	=> date("Y-m-d H:i:s")
								);
				$wpdb->insert("wp_".$blog_id."_points_transaction", $data);

				if($queue == 0){
					$wpdb->insert("wp_". $blog_id ."_agent_wallet", 
							array(
								'agent_id' => $agent_id,
						  		'points' => $points,
						  		'balance' => $balance-$point_value,
						  		'notes' => 'Debited',
						  		'endorser_id' => $_POST['endorser_id'],
						  		'amount' => -$point_value,
						  		'transaction_id' => $wpdb->insert_id,
						  		'created' => date('Y-m-d H-i-s')
							)
					);
				} else {
					// noti mail to agent and endorser
					$user_info1 = get_userdata($agent_id);
					$user_info2 = get_userdata($_POST['endorser_id']);

					$template1 = 'Your Wallet is empty, Please purchase';
					$template2 = 'Agent Wallet is empty, Your points are in queue it will release once your agent purchase wallet points';

					$ntm_mail->send_mail($user_info1->user_email, 'Wallet is empty', $template1, '', '');
					$ntm_mail->send_mail($user_info2->user_email, 'Agent Wallet is empty', $template2, '', '');
				}

				$track = array('type' => $_POST['type'],  'points_earned' => $points
				);
				$this->track_api('ic_add_points', $blog_id, $_POST['endorser_id'], $track, $response);
			}

			update_user_meta($_POST['endorser_id'], 'end_follow_up', 1);

			$results = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 0 and endorser_id='".$_POST['endorser_id']."'");
			$endorser_points2 = $results->points ? $results->points : 0;
			$results2 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 1 and endorser_id='".$_POST['endorser_id']."'");
			$endorser_points3 = $results2->points ? $results2->points : 0;
			$response = array('status' => 'Success', 
				'msg' => 'Invitation send', 
				'points' => $endorser_points2, 
				'non_release_points' => $endorser_points3, 
				'allowance' => $endorser_points);
		} else{
			$response = array('status' => 'Error', 'msg' => 'Invalid data');
		}
		
		
		echo json_encode($response);
		die(0);
	}

	function ic_site_pages() {

		echo json_encode(array('data' => get_pages()));
		die(0);
	}

	function ic_new_endorsement_invitation(){
		global $wpdb, $ntm_mail;
		$_POST = (array) json_decode(file_get_contents('php://input'));
		$botId = $_POST['botId'];
		$contact_list = $_POST['contacts'];
		$notes = $_POST['template'];
		$endorser = get_userdata($_POST['id']);
		$blog_id = get_active_blog_for_user( $_POST['id'] )->blog_id;
		$subject = 'Endorser Invitation';

		$email_invitation = get_post_meta($botId, 'email_invitation', true);

		$valid = 0;
		$contact_list_res = [];
		foreach($contact_list as $res)
		{

			$res = (array)$res;

			$check = $wpdb->get_results('select * from wp_'.$blog_id.'_endorsements where email = "'.$res['email'].'"');
			
			if(!count($check)){

				$info = array(
					"name" => $res['name'], 
					"created" => date("Y-m-d H:i:s"), 
					"email" => $res['email'],
					"endorser_id" => $_POST['id'],
					"tracker_id" => wp_generate_password( $length=12, $include_standard_special_chars=false )
				);
				$wpdb->insert("wp_".$blog_id."_endorsements", $info);
				//$content = file_get_contents('../emailtemplate/invitation.html');
				$content = "<!doctype html>
<html>
  <head>
    <meta name='viewport' content='width=device-width'>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
    <title>Simple Transactional Email</title>
    <style>
    /* -------------------------------------
        INLINED WITH htmlemail.io/inline
    ------------------------------------- */
    /* -------------------------------------
        RESPONSIVE AND MOBILE FRIENDLY STYLES
    ------------------------------------- */
    @media only screen and (max-width: 620px) {
      table[class=body] h1 {
        font-size: 28px !important;
        margin-bottom: 10px !important;
      }
      table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
        font-size: 16px !important;
      }
      table[class=body] .wrapper,
            table[class=body] .article {
        padding: 10px !important;
      }
      table[class=body] .content {
        padding: 0 !important;
      }
      table[class=body] .container {
        padding: 0 !important;
        width: 100% !important;
      }
      table[class=body] .main {
        border-left-width: 0 !important;
        border-radius: 0 !important;
        border-right-width: 0 !important;
      }
      table[class=body] .btn table {
        width: 100% !important;
      }
      table[class=body] .btn a {
        width: 100% !important;
      }
      table[class=body] .img-responsive {
        height: auto !important;
        max-width: 100% !important;
        width: auto !important;
      }
    }

    /* -------------------------------------
        PRESERVE THESE STYLES IN THE HEAD
    ------------------------------------- */
    @media all {
      .ExternalClass {
        width: 100%;
      }
      .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
        line-height: 100%;
      }
      .apple-link a {
        color: inherit !important;
        font-family: inherit !important;
        font-size: inherit !important;
        font-weight: inherit !important;
        line-height: inherit !important;
        text-decoration: none !important;
      }
      .btn-primary table td:hover {
        background-color: #34495e !important;
      }
      .btn-primary a:hover {
        background-color: #34495e !important;
        border-color: #34495e !important;
      }
    }
    </style>
  </head>
  <body class='' style='background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;'>
    <table border='0' cellpadding='0' cellspacing='0' class='body' style='border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;'>
      <tr>
        <td style='font-family: sans-serif; font-size: 14px; vertical-align: top;'>&nbsp;</td>
        <td class='container' style='font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;'>
          <div class='content' style='box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;'>

            <!-- START CENTERED WHITE CONTAINER -->
            <span class='preheader' style='color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;'>This is preheader text. Some clients will show this text as a preview.</span>
            <table class='main' style='border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;'>

              <!-- START MAIN CONTENT AREA -->
              <tr>
                <td class='wrapper' style='font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;'>
                  <table border='0' cellpadding='0' cellspacing='0' style='border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;'>
                    <tr>
                      <td style='font-family: sans-serif; font-size: 14px; vertical-align: top;'>
                        <p style='font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;'>Hi [USERNAME],</p>
                        <p style='font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;'>
                          [EMAILINVIATION] [BOTLINK] [TRACKIMAGE]
                        </p>
                        <p style='font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;'>[PERSONALNOTE].</p>
                        <p style='font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;'>Good luck! Hope it works.</p>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

            <!-- END MAIN CONTENT AREA -->
            </table>

            <!-- START FOOTER -->
            <div class='footer' style='clear: both; Margin-top: 10px; text-align: center; width: 100%;'>
              <table border='0' cellpadding='0' cellspacing='0' style='border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;'>
                <tr>
                  <td class='content-block' style='font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;'>
                    <span class='apple-link' style='color: #999999; font-size: 12px; text-align: center;'>Company Inc, 3 Abbey Road, San Francisco CA 94102</span>
                    <br> Don't like these emails? <a href='http://i.imgur.com/CScmqnj.gif' style='text-decoration: underline; color: #999999; font-size: 12px; text-align: center;'>Unsubscribe</a>.
                  </td>
                </tr>
                <tr>
                  <td class='content-block powered-by' style='font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;'>
                    Powered by <a href='http://htmlemail.io' style='color: #999999; font-size: 12px; text-align: center; text-decoration: none;'>HTMLemail</a>.
                  </td>
                </tr>
              </table>
            </div>
            <!-- END FOOTER -->

          <!-- END CENTERED WHITE CONTAINER -->
          </div>
        </td>
        <td style='font-family: sans-serif; font-size: 14px; vertical-align: top;'>&nbsp;</td>
      </tr>
    </table>
  </body>
</html>";
				$content 	=	str_ireplace('[USERNAME]', $res['name'], $content);
				$content 	=	str_ireplace('[ENDORSERNAME]', get_user_meta($_POST['id'], 'first_name', true), $content);
				$content 	=	str_ireplace('[BOTLINK]', get_permalink($botId).'?ref='.base64_encode(base64_encode($botId.'#&$#'.$endorser.'#&$#'.$wpdb->insert_id)).'&video='.$video, $content);

				$content 	=	str_ireplace('[EMAILINVIATION]', $email_invitation, $content);
				$content 	=	str_ireplace('[PERSONALNOTE]', $notes, $content);

				$image = "<img src='".site_url('wp-admin/admin-ajax.php?action=ic_track_invitation_open&ref='.base64_encode(base64_encode($eeid.'#&$#'.$_POST['id'].'#&$#'.$info['tracker_id'])))."' width='1' height='1'>";
				$endorse_letter = $content = str_ireplace("[TRACKIMAGE]", $image, $content);
				
				$ntm_mail->send_mail($info['email'], $subject, $content);
				$valid++;
				$res['valid'] = true;
			} else {
				$res['valid'] = false;
			}

			$contact_list_res[] = $res;
		}

		if($valid){
			
			$agent_id = get_blog_option($blog_id, 'agent_id');
			$points = get_user_meta($agent_id, 'endorsement_settings', true)['email_point_value'];
			$note_points = get_user_meta($agent_id, 'endorsement_settings', true)['note_point_value'];
			

			$monthly_invitation_allowance = get_user_meta($agent_id, 'endorsement_settings', true)['monthly_invitation_allowance'];
			
			$results = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where created like '".date("Y-m-")."%' and type in ('email_invitation', 'fbShare', 'liShare') and endorser_id='".$_POST['id']."'");

			$endorser_points = $results->points ? $results->points : 0;

			if($endorser_points < $monthly_invitation_allowance){

				$total_points = $points * $valid;

				if(strlen($notes) > 100){
					$total_points += $note_points;
				}


				if(($total_points + $endorser_points) > $monthly_invitation_allowance){
					$total_points = $monthly_invitation_allowance - $endorser_points;
				}


				$endorser_points = $endorser_points + $total_points;
				$balance = $this->ic_agent_balance($agent_id);
				$point_value = $this->ic_get_point_value($total_points) * 100;
				$queue = $balance >= $point_value ? 0 : 1;
				$data = array(
								'endorser_id' => $_POST['id'],
								'agent_id' => $agent_id,
								'points' => $total_points,
								'queue' => $queue,
								'type' => 'email_invitation',
								'created'	=> date("Y-m-d H:i:s")
								);
				$wpdb->insert("wp_".$blog_id."_points_transaction", $data);

				if($queue == 0){
					$wpdb->insert("wp_". $blog_id ."_agent_wallet", 
							array(
								'agent_id' => $agent_id,
						  		'points' => $points,
						  		'balance' => $balance-$point_value,
						  		'endorser_id' => $_POST['endorser_id'],
						  		'amount' => -$point_value,
						  		'notes' => 'Debited',
						  		'transaction_id' => $wpdb->insert_id,
						  		'created' => date('Y-m-d H-i-s')
							)
					);
				}
				
				update_user_meta($_POST['id'], "invitation_sent", (get_user_meta($_POST['id'], "invitation_sent", true) + $valid));
			}

			update_user_meta($_POST['id'], 'end_follow_up', 1);

			$results = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 0 and endorser_id='".$_POST['id']."'");
			$endorser_points2 = $results->points ? $results->points : 0;

			$results2 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 1 and endorser_id='".$_POST['id']."'");
			$endorser_points3 = $results2->points ? $results2->points : 0;

			$response = array('status' => 'Success', 'msg' => 'Invitation send', 'points' => $endorser_points2, 'non_release_points' => $endorser_points3, 'allowance' => $endorser_points, 'valid_email' => $valid);

			$track = array('contacts' => $contact_list_res,  'valid_email' => $valid,
				'points_earned' => $total_points
			);
			$this->track_api('ic_new_endorsement_invitation', $blog_id, $_POST['id'], $track, $response);
		} else{
			$response = array('status' => 'Error', 'msg' => 'No valid Email, already shared invitation for this email');

			$this->track_api('ic_new_endorsement_invitation', $blog_id, $_POST['id'], array(), $response);
		}

		echo json_encode($response);
		die(0);
	
	}

	function ic_send_endorsement_invitation() {
		global $wpdb, $ntm_mail;
		$_POST = (array) json_decode(file_get_contents('php://input'));

		
		$contact_list = $_POST['contacts'];
		$notes = $_POST['template'];

		$blog_id = get_active_blog_for_user( $_POST['id'] )->blog_id;
		$campaign = get_user_meta($_POST['id'], 'campaign', true);
		$templates = $wpdb->get_row("select * from wp_".$blog_id."_campaign_templates where name = 'Endorsement Letter' and campaign_id=".$campaign);

		$subject = 'Endorser Invitation';
		$content = str_ireplace("<br />", "", stripslashes(stripslashes($templates->template)));
		$content = str_ireplace("[ENDORSERS NOTES]", $notes.'[TRACKIMAGE]', $content);
		$valid = 0;
		$contact_list_res = [];
		foreach($contact_list as $res)
		{

			$res = (array)$res;

			$check = $wpdb->get_results('select * from wp_'.$blog_id.'_endorsements where email = "'.$res['email'].'"');
			
			if(!count($check)){

				$info = array(
					"name" => $res['name'], 
					"created" => date("Y-m-d H:i:s"), 
					"email" => $res['email'],
					"endorser_id" => $_POST['id'],
					"tracker_id" => wp_generate_password( $length=12, $include_standard_special_chars=false )
				);
				$wpdb->insert("wp_".$blog_id."_endorsements", $info);
				$eeid = $wpdb->insert_id;
				$image = "<img src='".site_url('wp-admin/admin-ajax.php?action=ic_track_invitation_open&ref='.base64_encode(base64_encode($eeid.'#&$#'.$_POST['id'].'#&$#'.$info['tracker_id'])))."' width='1' height='1'>";
				$endorse_letter = $content = str_ireplace("[TRACKIMAGE]", $image, $content);
				$ntm_mail->send_invitation_mail($info, $_POST['id'], $eeid, $endorse_letter);
				$valid++;
				$res['valid'] = true;
			} else {
				$res['valid'] = false;
			}

			$contact_list_res[] = $res;
		}

		$blog_id = get_active_blog_for_user( $_POST['id'] )->blog_id;

		if($valid){
			
			$agent_id = get_blog_option($blog_id, 'agent_id');
			$points = get_user_meta($agent_id, 'endorsement_settings', true)['email_point_value'];
			$note_points = get_user_meta($agent_id, 'endorsement_settings', true)['note_point_value'];
			

			$monthly_invitation_allowance = get_user_meta($agent_id, 'endorsement_settings', true)['monthly_invitation_allowance'];
			
			$results = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where created like '".date("Y-m-")."%' and type in ('email_invitation', 'fbShare', 'liShare') and endorser_id='".$_POST['id']."'");

			$endorser_points = $results->points ? $results->points : 0;

			if($endorser_points < $monthly_invitation_allowance){

				$total_points = $points * $valid;

				if(strlen($notes) > 100){
					$total_points += $note_points;
				}


				if(($total_points + $endorser_points) > $monthly_invitation_allowance){
					$total_points = $monthly_invitation_allowance - $endorser_points;
				}


				$endorser_points = $endorser_points + $total_points;
				$balance = $this->ic_agent_balance($agent_id);
				$point_value = $this->ic_get_point_value($total_points) * 100;
				$queue = $balance >= $point_value ? 0 : 1;
				$data = array(
								'endorser_id' => $_POST['id'],
								'agent_id' => $agent_id,
								'points' => $total_points,
								'queue' => $queue,
								'type' => 'email_invitation',
								'created'	=> date("Y-m-d H:i:s")
								);
				$wpdb->insert("wp_".$blog_id."_points_transaction", $data);

				if($queue == 0){
					$wpdb->insert("wp_". $blog_id ."_agent_wallet", 
							array(
								'agent_id' => $agent_id,
						  		'points' => $points,
						  		'balance' => $balance-$point_value,
						  		'endorser_id' => $_POST['endorser_id'],
						  		'amount' => -$point_value,
						  		'notes' => 'Debited',
						  		'transaction_id' => $wpdb->insert_id,
						  		'created' => date('Y-m-d H-i-s')
							)
					);
				}
				
				update_user_meta($_POST['id'], "invitation_sent", (get_user_meta($_POST['id'], "invitation_sent", true) + $valid));
			}

			update_user_meta($_POST['id'], 'end_follow_up', 1);

			$results = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 0 and endorser_id='".$_POST['id']."'");
			$endorser_points2 = $results->points ? $results->points : 0;

			$results2 = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where queue = 1 and endorser_id='".$_POST['id']."'");
			$endorser_points3 = $results2->points ? $results2->points : 0;

			$response = array('status' => 'Success', 'msg' => 'Invitation send', 'points' => $endorser_points2, 'non_release_points' => $endorser_points3, 'allowance' => $endorser_points, 'valid_email' => $valid);

			$track = array('contacts' => $contact_list_res,  'valid_email' => $valid,
				'points_earned' => $total_points
			);
			$this->track_api('ic_send_endorsement_invitation', $blog_id, $_POST['id'], $track, $response);
		} else{
			$response = array('status' => 'Error', 'msg' => 'No valid Email, already shared invitation for this email');

			$this->track_api('ic_send_endorsement_invitation', $blog_id, $_POST['id'], array(), $response);
		}

		echo json_encode($response);
		die(0);
	}

	function ic_send_invitation() {
		global $wpdb;
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		foreach ($_POST['mail_list'] as $mail){

			$subject = $_POST['subject'];
			$message = $_POST['message'];

			NTM_mail_template::send_endorser_invitation_mail($mail['mail']);
		}

		$response = array('status' => 'Success', 'msg' => 'Invitation send');
		echo json_encode($response);
		die(0);
	}

	function ic_get_sites(){
		$data = [];
		foreach(get_sites() as $sites ) {
			$data[] = array('site_id' => $sites->blog_id, 'domain' => $sites->domain.$sites->path, 'domainname' => str_replace('/', '', $sites->path));
		}
		echo json_encode($data);
		die(0);
	}
	
	function ic_send_gift(){
		global $wpdb;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$data = array(
						'endorser_id' =>$_POST['endorser_id'],
						'amout' => $_POST['gift_amount'],
						'fb_count'	=> get_user_meta($_POST['id'], "tracked_fb_counter", true),
						'twitter_count'	=> get_user_meta($_POST['id'], "tracked_tw_counter", true),
						"agent_id" => $current_user->ID,
						'created'	=> date("Y-m-d H:i:s")
						);
		$wpdb->insert($wpdb->prefix . "gift_transaction", $data);
		$gift_id = $wpdb->insert_id;
		$get_results = $wpdb->get_results("select * from ".$wpdb->prefix . "endorsements where endorser_id=".$_POST['endorser_id']." and track_status is not null and gift_status is null");
		
		foreach($get_results as $res)
		{
			$wpdb->insert($wpdb->prefix . "giftendorsements", array(
																"gift_id" => $gift_id, 
																"endorser_id" => $_POST['endorser_id'], 
																"endorsement_id" => $res->id
																)
							);
			$wpdb->update($wpdb->prefix . "endorsements", array('gift_status' => 1), array('id' => $res->id));
		}
		
		update_user_meta($_POST['endorser_id'], "tracked_fb_counter", 0);
		update_user_meta($_POST['endorser_id'], "tracked_tw_counter", 0);
		update_user_meta($_POST['endorser_id'], "tracked_counter", 0);
		NTM_mail_template::send_gift_mail('get_gift_mail', $_POST['endorser_id'], $gift_id);

		$response = array('status' => 'Success', 'msg' => 'Gift send');
		echo json_encode($response);
		die(0);
	}

	function ic_resend_gift(){
		global $wpdb;

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$data = array(
						'endorser_id' =>$_POST['endorser_id'],
						'amout' => $_POST['gift_amount'],
						'agent_id' => get_current_user_id(),
						'created'	=> date("Y-m-d H:i:s")
						);
		$wpdb->insert($wpdb->prefix . "gift_transaction", $data);
		$gift_id = $wpdb->insert_id;
		foreach($_POST['endorsement'] as $res)
		{
			$wpdb->insert($wpdb->prefix . "giftendorsements", array(
																"gift_id" => $gift_id, 
																"endorser_id" => $_POST['endorser_id'], 
																"endorsement_id" => $res
																)
							);
			$wpdb->update($wpdb->prefix . "endorsements", array('gift_status' => 2), array('id' => $res->id));
		}
		NTM_mail_template::send_gift_mail('get_regift_mail', $_POST['endorser_id'], $gift_id);

		$response = array('status' => 'Success', 'msg' => 'Gift Resend');
		echo json_encode($response);
		die(0);
	}

	function ic_upgrade_membership(){
		global $wpdb;
		$membership = (array) $wpdb->get_row("select * from wp_pmpro_memberships_orders where user_id=".$_GET['id']);
		$membership['user_id'] = $_POST['id'];
		$membership['membership_id'] = 2;
		$membership['timestamp'] = date("Y-m-d H:i:s");
		
		$membership_level = $wpdb->get_row("select * from wp_pmpro_memberships_levels where user_id=2");

		$fa_lead_options = get_option('fa_lead_settings');
		Stripe::setApiKey($fa_lead_options['api_key']);
		Stripe::setAPIVersion("2015-07-13");
		$invoice_item = Stripe_InvoiceItem::create( array(
			'customer'    => $_POST['id'], // the customer to apply the fee to
			'amount'      => $membership_level->billing_amount * 100,
			'currency'    => 'usd',
			'description' => 'One-time setup fee' // our fee description
		) );
	 
		$invoice = Stripe_Invoice::create( array(
			'customer'    => $_POST['id'], // the customer to apply the fee to
		) );

		//Stripe Integration here
		$wpdb->insert("wp_pmpro_membership_orders", $membership);
		$wpdb->update("wp_pmpro_memberships_users", array('user_id' => $_GET['id']));
		
		$response = array('status' => 'Success');
		echo json_encode($response);
		die(0);
	}

	function ic_agent_login(){
		global $wpdb;
		$creds = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$stripeAPI = pmpro_getOption("stripe_publishablekey");
		$user = wp_signon( $creds, false );
		

		if ( is_wp_error($user) ) {
			$response = array('status' => 'Error', 'msg' => 'Invalid Credentials');
		}
		else{
			$data = (array) $user->data;
			$userBlogs = get_blogs_of_user((int)$user->data->ID);
			$timekitGmail = get_user_meta((int)$user->data->ID, 'timekits_gmail_email', true);
			$timekit_resource_id = update_user_meta((int)$user->data->ID, 'timekit_resource_id', true);
			$timekit_calendar_id = update_user_meta((int)$user->data->ID, 'timekit_calendar_id', true);
			$timekitTimeZone = get_user_meta((int)$user->data->ID, 'timekits_time_zone', true);
			$siteUrl = get_site_url(get_user_meta((int)$user->data->ID, 'primary_blog', true));
			$stripeCustomerId = get_user_meta((int)$user->data->ID, "pmpro_stripe_customerid");
			$blog_id = get_active_blog_for_user( $user->data->ID )->blog_id;
			$membership = $wpdb->get_row("select * from wp_pmpro_memberships_users where user_id=".$user->data->ID);
			$data['membership'] = isset($membership->membership_id) ? $membership->membership_id : 0;
			$data['timekit_gmail'] = $timekitGmail;
			$data['stripePublishAPI'] = $stripeAPI;
            $data['timekit_time_zone'] = $timekitTimeZone;
            $data['timekit_resource_id'] = $timekit_resource_id;
            $data['timekit_calendar_id'] = $timekit_calendar_id;
            $data['stripe_customer_id'] = $stripeCustomerId[0];
            $data['points_per_dollar'] = get_blog_option($blog_id, 'points_per_dollar');
            $data['admin_fee'] = get_blog_option($blog_id, 'admin_fee');
            $data['blog_id'] = $blog_id;
			$data['dollar_per_point'] = 1/$data['points_per_dollar'];
			$response = array('status' => 'Success', 'data' => $data, 'msg' => 'Logged in successfully', 'site_url' => $siteUrl);
		}
		echo json_encode($response);
		die(0);
	}

	function ic_add_endorser(){
		global $ntm_mail;

		$user = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$user['role'] = 'endorser';
		$user['user_login'] = strtolower($user['first_name'].'_'.$user['last_name']).rand(1111,9999);
		if(isset($user['agentID'])) {		
			$siteID = get_active_blog_for_user( $user['agentID'] )->blog_id;
			switch_to_blog( $siteID );
		}
		$user_id = username_exists( $user['user_login'] );
		if ( !$user_id and email_exists($user['user_email']) == false ) {
			if(isset($user['password'])) {
				$user['user_pass'] = $user['password'];
			} else {
				$user['user_pass'] = wp_generate_password( $length=12, $include_standard_special_chars=false );
			}
				$user_id = wp_insert_user( $user ) ;
			if (  is_wp_error( $user_id ) ) {
				$response = array('status' => 'Error', 'msg' => 'Something went wrong. Try Again!!!.');
			}
			else
			{
				update_user_meta($user_id, 'agent_id', $user['agent_id']);
				update_user_meta($user_id, 'first_name', $user['first_name']);
				update_user_meta($user_id, 'last_name', $user['last_name']);
				update_user_meta($user_id, 'phone', $user['phone']);
				
				update_user_meta($user_id, 'endorser_letter', $user['endorser_letter']);
				update_user_meta($user_id, 'endorsement_letter', $user['endorsement_letter']);
				update_user_meta($user_id, 'campaign', $user['campaign']);
				update_user_meta($user_id, 'intro_bot', $user['intro_bot']);
				update_user_meta($user_id, 'endorser_bot', array($user['intro_bot']));
				update_user_meta($user_id, 'social_campaign', $user['social_campaign']);
				update_user_meta($user_id, 'video', $user['video']);
				update_user_meta($user_id, 'landingPageContent', $user['landingPageContent']);
				update_user_meta($user_id, 'have_intro_points', $_POST['have_intro_points']);
				update_user_meta($user_id, 'intro_points', $_POST['intro_points']);
				
				if(isset($user['password'])) {
					update_user_meta($user_id, 'issuePoints', $user['issue_points'] ? 1 : 0);
				}
				if( isset($user['video']) ){
					$ntm_mail->send_welcome_mail($user['user_email'], $user_id, $user['user_login'].'#'.$user['user_pass'], $user['video']);
				} else {
					$ntm_mail->send_welcome_mail($user['user_email'], $user_id, $user['user_login'].'#'.$user['user_pass']);
				}
				$ntm_mail->send_notification_mail($user_id);

				$response = array('status' => 'Success', 'data' => $user_id, 'msg' => 'Endorser created successfully');
			}
		} else {
			$response = array('status' => 'Error', 'msg' => 'User already exists.  Password inherited.');
		}

		echo json_encode($response);
		die(0);
	}

	function getIntro(){
		$user_id = $_GET['endorserId'];

		$video = get_user_meta($user_id, 'video', true);
		$landingPageContent = get_user_meta($user_id, 'landingPageContent', true);

		$response = array('status' => 'Success', 'data' => array('video' => $video, 'landingPageContent' => $landingPageContent));

		echo json_encode($response);
		die(0);
	}

	function ic_update_endorser() {
		$user = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$user_id = $user['id'];
		unset($user['id']);

		foreach ($user as $key => $value) {
			update_user_meta($user_id, $key, $value);
		}

		$response = array('status' => 'Success', 'msg' => 'Endorser updated successfully');
		echo json_encode($response);
		die(0);
	}

	function objectToArray($d){
		if (is_object($d)) {
			$d = get_object_vars($d);
		}
 
		if (is_array($d)) {
			return array_map(__FUNCTION__, $d);
		}
		else {
			return $d;
		}
	}

	function ic_get_endorser_list() {
		
		global $wpdb;
		$arr = array('role'=>'endorser');
		$arr['order'] = $_GET['columns'][$_GET['order'][0]['column']]['data'];
		$arr['orderby'] = $_GET['order'][0]['dir'];
		$data = (array)get_users();
		
		$search = $_GET['search']['value'];
		$ss = '';
		if($search){
			$ss = "(email like '%$search%' or firstname like '%$search%' or lastname like '%$search%') and ";
		}

		$recordsTotal = $wpdb->get_results("select * from tmp_user where $ss status = 0");
		$start = $_GET['start'];
		$length = $_GET['length'];
		$offset = $start * $length;
		$order = 
		
		$recordsFiltered = $wpdb->get_results("select * from tmp_user where $ss status = 0 order by $order $orderby limit $offset, $length ");
		$response = array('status' => 'Success', 
							'data' => $recordsFiltered,
						  	'recordsTotal' => count($recordsTotal),
						  	'recordsFiltered' => count($recordsFiltered),
						);


		$newdat = array();
		foreach($data as $v){
			$v = (array)$v;
			$item = (array)$v['data'];
			$item['ID'] = $item['ID'];
			if(!get_user_meta($v['ID'], 'imcomplete_profile', true)){
				$last_login = get_user_meta($item['ID'], 'last_login', true);
    			$the_login_date = human_time_diff($last_login);
				$item['last_login'] = $the_login_date;
				
				
				
				$newdat[] = $item;
			}
		}
		$response = array('status' => 'Success', 'data' => $newdat);
		echo json_encode($response);
		die(0);


	}

	function ic_endorser_list(){
		global $wpdb;
		$blog_id = get_current_blog_id();
		$arr = array('blog_id' => $blog_id, 'role'=>'endorser');

		$data = (array)get_users($arr);
		$search = $_GET['search']['value'];
		$newdat = array();
		foreach($data as $v){
			$v = (array)$v;
			$item = array('id' => $v['ID'], 'ID' => $v['ID']);
			$item['issuePoints'] = get_user_meta($item['ID'], 'issuePoints', true);
			if($item['issuePoints'] == '' || $item['issuePoints'] == 1){ // get_user_meta($item['ID'], 'agent_id', true) == $_GET['agent_id']

				$endorser_id = $item['ID'];
				$endorser = get_userdata($endorser_id);

				$item['name'] = get_user_meta($endorser_id, 'first_name', true). ' '. get_user_meta($endorser_id, 'last_name', true);
				$item['email'] = $endorser->user_email;
				
				$total_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where type!='Redeem Point' and queue = 0 and endorser_id = ".$endorser_id);

				$non_queue_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where type!='Redeem Point' and queue = 1 and endorser_id = ".$endorser_id);

				$redeem_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where type='Redeem Point' and endorser_id = ".$endorser_id);

				$chat_conversion = $wpdb->get_row("select count(*) as cnt from wp_".$blog_id."_points_transaction where type='chat_conversion' and endorser_id = ".$endorser_id);

				$meeting_conversion = $wpdb->get_row("select count(*) as cnt from wp_".$blog_id."_points_transaction where type='meeting_conversion' and endorser_id = ".$endorser_id);


				$item['total_points'] = $total_points ? $total_points->points : 0;
				$item['non_queue_points'] = $non_queue_points ? $non_queue_points->points : 0;
				$item['redeem_points'] = $redeem_points ? $redeem_points->points : 0;
				$item['chat_conversion'] = $chat_conversion ? $chat_conversion->cnt : 0;
				$item['meeting_conversion'] = $meeting_conversion ? $meeting_conversion->cnt : 0;

				$last_login = get_user_meta($endorser_id, 'last_login', true);
				$the_login_date = human_time_diff($last_login);
				$item['last_login'] = $the_login_date;

				if($search && (strpos($item['name'], $search) || strpos($item['email'], $search))){
					$newdat[] = $item;
				} else {
					$newdat[] = $item;
				}
			}
		}

		$recordsTotal = $newdat;
		$start = $_GET['start'];
		$length = $_GET['length'];
		

		function sortByOrder($a, $b) {
			$order = $_GET['columns'][$_GET['order'][0]['column']]['data'];
			$orderby = $_GET['order'][0]['dir'];
			if(strtoupper($orderby) == 'ASC')
				return $a[$order] > $b[$order] ? 1 : -1;
			else
				return $b[$order] > $a[$order] ? 1 : -1;
		}

		usort($newdat, 'sortByOrder');
		$recordsFiltered = array_slice($newdat, $start, $length);

		$response = array('status' => 'Success', 
							'draw' => (int)$_GET['draw'],
							'data' => $recordsFiltered,
						  	'recordsTotal' => count($recordsTotal),
						  	'recordsFiltered' => count($recordsTotal),
						);

		echo json_encode($response);
		die(0);
	}

	function ic_get_tmp_user(){
		global $wpdb;
		$blog_id = get_current_blog_id();
		$arr = array('blog_id' => $blog_id, 'role'=>'endorser');

		$data = (array)get_users($arr);
		$search = $_GET['search']['value'];
		$newdat = array();
		foreach($data as $v){
			$v = (array)$v;
			$item = array('id' => $v['ID'], 'ID' => $v['ID']);
			$item['issuePoints'] = get_user_meta($item['ID'], 'issuePoints', true);
			if($item['issuePoints'] != '' && $item['issuePoints'] != 1){ // get_user_meta($item['ID'], 'agent_id', true) == $_GET['agent_id']

				$endorser_id = $item['ID'];
				$endorser = get_userdata($endorser_id);

				$item['name'] = get_user_meta($endorser_id, 'first_name', true). ' '. get_user_meta($endorser_id, 'last_name', true);
				$item['email'] = $endorser->user_email;
				
				$total_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where type!='Redeem Point' and queue = 0 and endorser_id = ".$endorser_id);

				$non_queue_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where type!='Redeem Point' and queue = 1 and endorser_id = ".$endorser_id);

				$redeem_points = $wpdb->get_row("select sum(points) as points from wp_".$blog_id."_points_transaction where type='Redeem Point' and endorser_id = ".$endorser_id);

				$chat_conversion = $wpdb->get_row("select count(*) as cnt from wp_".$blog_id."_points_transaction where type='chat_conversion' and endorser_id = ".$endorser_id);

				$meeting_conversion = $wpdb->get_row("select count(*) as cnt from wp_".$blog_id."_points_transaction where type='meeting_conversion' and endorser_id = ".$endorser_id);


				$item['total_points'] = $total_points ? $total_points->points : 0;
				$item['non_queue_points'] = $non_queue_points ? $non_queue_points->points : 0;
				$item['redeem_points'] = $redeem_points ? $redeem_points->points : 0;
				$item['chat_conversion'] = $chat_conversion ? $chat_conversion->cnt : 0;
				$item['meeting_conversion'] = $meeting_conversion ? $meeting_conversion->cnt : 0;

				$last_login = get_user_meta($endorser_id, 'last_login', true);
				$the_login_date = human_time_diff($last_login);
				$item['last_login'] = $the_login_date;

				if($search && (strpos($item['name'], $search) || strpos($item['email'], $search))){
					$newdat[] = $item;
				} else {
					$newdat[] = $item;
				}
			}
		}

		$recordsTotal = $newdat;
		$start = $_GET['start'];
		$length = $_GET['length'];
		

		function sortByOrder($a, $b) {
			$order = $_GET['columns'][$_GET['order'][0]['column']]['data'];
			$orderby = $_GET['order'][0]['dir'];
			if(strtoupper($orderby) == 'ASC')
				return $a[$order] > $b[$order] ? 1 : -1;
			else
				return $b[$order] > $a[$order] ? 1 : -1;
		}

		usort($newdat, 'sortByOrder');
		$recordsFiltered = array_slice($newdat, $start, $length);

		$response = array('status' => 'Success', 
							'draw' => (int)$_GET['draw'],
							'data' => $recordsFiltered,
						  	'recordsTotal' => count($recordsTotal),
						  	'recordsFiltered' => count($recordsTotal),
						);

		echo json_encode($response);
		die(0);
		
	}

	function ic_add_create_email_template() {
		global $wpdb;


		$letter = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$letter['created'] = date("Y-m-d H:i:s");
		$res = $wpdb->insert($wpdb->prefix . "mailtemplates", $letter);

		if (  is_wp_error( $res ) ) {
			$response = array('status' => 'Error', 'msg' => 'Something went wrong. Try Again!!!.');
		} else {
			$response = array('status' => 'Success', 'data' => $res, 'msg' => 'Letter template created successfully');
		}

		echo json_encode($response);
		die(0);
	}

	function ic_all_letter_list() {
		global $wpdb;

		$response = $wpdb->get_results('select * from '.$wpdb->prefix . "mailtemplates");
		$newres = [];
		foreach($response as $res){
			$res = (array)$res;
			$res['pagename'] = get_the_title($res['page']);
			$newres[] = $res;
		}

		echo json_encode(array('status' => 'Success', 'data' => $newres));
		die(0);
	}

	function ic_endorser_letter_list() {
		global $wpdb;

		$response = array('status' => 'Success', 'data' => $wpdb->get_results('select * from '.$wpdb->prefix . "mailtemplates where type = 'Endorser'")); 

		echo json_encode($response);
		die(0);
	}

	function ic_endorsement_letter_list() {
		global $wpdb;

		$response = array('status' => 'Success', 'data' => $wpdb->get_results('select * from '.$wpdb->prefix . "mailtemplates where type = 'Endorsement'")); 

		echo json_encode($response);
		die(0);
	}

	function ic_update_email_template(){
		global $wpdb;


		$letter = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$id = $letter['id'];
		unset($letter['pagename']);
		unset($letter['id']);
		$res = $wpdb->update($wpdb->prefix . "mailtemplates", $letter, array('id' => $id));

		if (  is_wp_error( $res ) ) {
			$response = array('status' => 'Error', 'msg' => 'Something went wrong. Try Again!!!.');
		} else {
			$response = array('status' => 'Success', 'data' => $res, 'msg' => 'Letter template updated successfully');
		}

		echo json_encode($response);
		die(0);
	}

	function ic_delete_endorser(){
		wpmu_delete_user($_GET['id']);

		$response = array('status' => 'Success', 'msg' => 'Endorser deleted successfully');
		
		echo json_encode($response);
		die(0);
	}

	function approve_endorser(){
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$user_id = $_POST['id'];
		update_user_meta($user_id, 'issuePoints', 1);
		update_user_meta($user_id, 'campaign', $_POST['campaign']);
		update_user_meta($user_id, 'intro_bot', $_POST['intro_bot']);
		update_user_meta($user_id, 'endorser_bot', array($_POST['intro_bot']));
		update_user_meta($user_id, 'social_campaign', $_POST['social_campaign']);
		update_user_meta($user_id, 'video', $_POST['video']);
		update_user_meta($user_id, 'landingPageContent', $_POST['landingPageContent']);
		update_user_meta($user_id, 'have_intro_points', $_POST['have_intro_points']);
		update_user_meta($user_id, 'intro_points', $_POST['intro_points']);
		$response = array('status' => 'Success', 'msg' => 'Endorser approved successfully');
		
		echo json_encode($response);
		die(0);
	}

	function dis_approve_endorser(){
		update_user_meta($_GET['id'], 'issuePoints', 0);

		$response = array('status' => 'Success', 'msg' => 'Endorser disapproved successfully');
		
		echo json_encode($response);
		die(0);
	}

	function ic_delete_letter(){
		global $wpdb;

		$wpdb->delete($wpdb->prefix . "mailtemplates", array( 'id' => $_GET['id'] ) );

		$response = array('status' => 'Success', 'msg' => 'Mail Letter template deleted successfully');
		
		echo json_encode($response);
		die(0);
	}

	function ic_noti_to_user(){

		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$subject = 'Notification - You have new message from agent';
		$message = 'Agent waiting for your reply Please join the chat.';
		
		if(NTM_mail_template::send_mail($_POST['email'], $subject, $message))
			$response = array('status' => 'Success', 'msg' => 'Notifications send to user');
		else
			$response = array('status' => 'Error', 'msg' => 'Notifications failed to send');
		
		echo json_encode($response);
		die(0);
	}

	function ic_noti_to_agent(){
		
		$_POST = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));
		$blog_id = get_current_blog_id();
		$agent_id = get_blog_option($blog_id, 'agent_id');
		$user_info = get_userdata($agent_id);
		$subject = 'Notification - You have new message from '.$_POST['email'];
		$message = 'User waiting for your repy.';

		if(NTM_mail_template::send_mail($user_info->user_email, $subject, $message))
			$response = array('status' => 'Success', 'msg' => 'Notifications send to agent');
		else
			$response = array('status' => 'Error', 'msg' => 'Notifications failed to send');
		
		echo json_encode($response);
		die(0);
	}

	function ic_new_lead() {
		global $wpdb;

		$lead = count($_POST) ? $_POST : (array) json_decode(file_get_contents('php://input'));

		$wpdb->insert("wp_leads", $lead);
		$lead_id = $wpdb->insert_id;

		if($lead_id) {
			$message = 'Thanks for signing up FinancialInsiders. <a href="'.site_url().'?action=update_lead_status&id='.base64_encode(base64_encode($lead_id)).'">Click here to confirm your registration</a>';
		
			NTM_mail_template::send_mail($lead['email'], 'Registered with FinancialInsiders successfly.', $message);

			$response = array('status' => 'Success', 'msg' => 'Lead created successfully');
		} else {
			$response = array('status' => 'Error', 'msg' => 'Try again later!!');
		}
		
		echo json_encode($response);
		die(0);
	}

	function ic_get_endorsement() {
		global $wpdb;

		$res = $wpdb->get_results("select * from ".$wpdb->prefix . "endorsements");

		$newres = [];
		foreach ($res as $key => $value) {
			$value = (array)$value;
			$value['track_link'] = base64_encode(base64_encode($value['id'].'#&$#'.$value['endorser_id'].'#&$#'.$value['tracker_id']));
			$value['track_status'] = $value['track_status'] ? "Yes" : "No";
			$value['gift_status'] = $value['gift_status'] ? "Yes" : "No";
			$value['endorser_id'] = get_user_meta($value['endorser_id'], 'first_name', true).' '.get_user_meta($value['endorser_id'], 'last_name', true);
			$value['created'] = date('Y/m/d', strtotime($value['created']));
			$newres[] = $value;
		}

		$response = array('status' => 'Success', 'data' => $res);
		echo json_encode($response);
		die(0);
	}
}