<?php
use OpenTok\OpenTok;
class IC_ajax{
    
    function __construct() {
        add_action( 'wp_ajax_join_chat', array( &$this, 'join_chat') );
		add_action( 'wp_ajax_nopriv_join_chat', array( &$this, 'join_chat') );
		add_action( 'wp_ajax_waiting_participants', array( &$this, 'waiting_participants') );
		add_action( 'wp_ajax_new_participants', array( &$this, 'new_participants') );
		add_action( 'wp_ajax_get_status', array( &$this, 'agent_status') );
		add_action( 'wp_ajax_nopriv_agent_status', array( &$this, 'agent_status') );
		add_action( 'wp_ajax_create_new_meeting', array( &$this, 'create_new_meeting') );
		add_action( 'wp_ajax_update_agent_status', array( &$this, 'update_agent_status'));
		add_action( 'wp_ajax_check_meeing', array( &$this, 'check_meeing') );
		add_action( 'wp_ajax_nopriv_check_meeing', array( &$this, 'check_meeing') );
		add_action( 'wp_ajax_update_user_offline', array( &$this, 'update_user_offline'));
		add_action( 'wp_ajax_presentation_file', array( &$this, 'presentation_file'));
		add_action( 'wp_ajax_save_settings', array( &$this, 'save_settings'));
		add_action( 'wp_ajax_delete_presentation_file', array( &$this, 'delete_presentation_file'));
		add_action( 'wp_ajax_save_ppt', array( &$this, 'save_ppt'));
		add_action( 'wp_ajax_new_user_to_meeting', array( &$this, 'new_user_to_meeting'));
		add_action( 'wp_ajax_usercontrol', array( &$this, 'usercontrol'));
    }
	
	function usercontrol()
	{
		global $wpdb;
		$_POST = (array) json_decode(file_get_contents('php://input'));
		$wpdb->update($wpdb->prefix . "meeting_participants", 
						array($_POST['type'] => $_POST['status']),
						array("id" => $_POST['pid'])
			);
		$joined_user = $wpdb->get_results("select * from ".$wpdb->prefix . "meeting_participants where meeting_id=".$_POST['mid']);
		$participants = $wpdb->get_results("select * from ".$wpdb->prefix . "meeting_participants where status = 1");
		
		echo json_encode(array('joined_user' => $joined_user, 'participants' => $participants));
		
		die(0);
		exit;
	}
	
	function new_user_to_meeting()
	{
		global $wpdb;
		$_POST = (array) json_decode(file_get_contents('php://input'));
		$wpdb->update($wpdb->prefix . "meeting_participants", 
						array(	'meeting_id' => $_POST['mid'], 
								'meeting_date' => date("Y-m-d H:i:s"),
								'status' => $_POST['status']
							),
						array("id" => $_POST['pid'])
			);
		$joined_user = $wpdb->get_results("select * from ".$wpdb->prefix . "meeting_participants where meeting_id=".$_POST['mid']);
		$participants = $wpdb->get_results("select * from ".$wpdb->prefix . "meeting_participants where status = 1");
		
		echo json_encode(array('joined_user' => $joined_user, 'participants' => $participants));
		
		die(0);
		exit;
	}
	
	function save_ppt()
	{
		$data = $_POST['data'];
		$file = explode("?", $data)[1];
		file_put_contents(IC_PLUGIN_DIR."/zip_files/$file.zip", file_get_contents($data));

		$zip = new ZipArchive;
		$res = $zip->open(IC_PLUGIN_DIR."/zip_files/$file.zip");
		if ($res === TRUE) {
		  $zip->extractTo(IC_PLUGIN_DIR."/extract/$file/");
		  $zip->close();
			$files = array();
			$i = 0;
			if (is_dir(IC_PLUGIN_DIR."/extract/$file/")){
					  if ($dh = opendir(IC_PLUGIN_DIR."/extract/$file/")){
						while (($filee = readdir($dh)) !== false){
						  if($i > 1)
						  $files[] = $filee;
						  $i++;
						}
						closedir($dh);
					  }
			}
			
			$option = get_option('ic_presentations');
			$option = is_array($option) ? $option : [];
			$option[] = array('folder' => $file, 'files' => $files, 'name' => $_GET['name']);
			update_option('ic_presentations', $option);

			echo json_encode(array('folder' => $file, 'files' => $files));
		} else {
		  echo 'error';
		}
		
		die(0);
		exit;
	}
	
	function delete_presentation_file()
	{
		global $wpdb;
		
		$wpdb->delete($wpdb->prefix . "meeting_presentations", array( 'id' => $_GET['id'] ));
		$presentations = $wpdb->get_results("select * from ". $wpdb->prefix . "meeting_presentations");
		echo json_encode($presentations);
		
		die(0);
		exit;
	}
	
	function save_settings()
	{
		update_option('bbb', (array)json_decode(file_get_contents('php://input')));
		
		die(0);
		exit;
	}
	
	function presentation_file()
	{
		global $wpdb;
		
		if(isset($_FILES['file']))
		{
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			$uploadedfile = $_FILES['file'];

			$upload_overrides = array( 'test_form' => false );

			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

			if ( $movefile && !isset( $movefile['error'] ) ) {
				
				unset($movefile['type']);
				
				$movefile['default_presentation'] = $_GET['default'];
				$movefile['name'] = $uploadedfile['name'];
				
				if($_GET['default'])
				{
					$wpdb->update($wpdb->prefix . "meeting_presentations", array('default_presentation' => 0), array('default_presentation' => 1));
				}
				
				$wpdb->insert($wpdb->prefix . "meeting_presentations", $movefile);
				
				$presentations = $wpdb->get_results("select * from ". $wpdb->prefix . "meeting_presentations");
				
				echo json_encode($presentations);
				
			} else {
				echo $movefile['error'];
			}
		}
		else
		{
			$presentations = $wpdb->get_results("select * from ". $wpdb->prefix . "meeting_presentations");
				
			echo json_encode($presentations);
		}
		
		die(0);
		exit;
	}
	
	function update_user_offline()
	{
		global $wpdb;
		
		$id = $_GET['id'];
		$ree = $wpdb->get_row("select * from ".$wpdb->prefix . "meeting_participants where id = ".$id);
		if($ree->status == 1)
		{
			$wpdb->update($wpdb->prefix . "meeting_participants", 
						array('status' => 0),
						array("id" => $id)
			);
		}
		
		$results = $wpdb->get_results("select * from ".$wpdb->prefix . "meeting_participants where status = 1");
		
		echo json_encode($results);
		die(0);
		exit;
	}
	
	function check_meeing() {

			global $wpdb;
			
			$meeting_id = $_GET['meeting_id'];
			$participants = $_GET['participants'];
			
			$meeting = $wpdb->get_row("select * from ".$wpdb->prefix . "meeting where id=".$meeting_id);
			
			$results = $wpdb->get_row("select * from ".$wpdb->prefix . "meeting_participants where (status = 2 or status = 3) and meeting_id=".$meeting_id." and id=".$participants);
			
			if(count($results))
			{
				echo json_encode(array("sessionId" => $meeting->session_id, "token" => $meeting->token, "status" => $results->status, "name" => $results->name, "email" => $results->email, "pid" => $participants, "mid" => $meeting_id));
				setcookie("instant_connect_waiting_id", "", time()-3600, "/");
			}	
			
			die(0);
			exit;
	}
	
	function update_agent_status() {

			global $current_user;
			update_user_meta($current_user->ID, 'user_current_status', $_GET['status']);
			update_user_meta($current_user->ID, 'user_logintime', date("Y-m-d H:i:s"));
			
			echo 'done';
			die(0);
			exit;
	}
	
	function create_new_meeting()
	{
		global $wpdb, $current_user;
		
		$meetingId = time();
		
		$opentok = opentok_token();
		
		$wpdb->insert($wpdb->prefix . "meeting", array('agent_id' => $current_user->ID, 'meeting_date' => date("Y-m-d H:i:s"), 'created' => date("Y-m-d H:i:s"), 'session_id' => $opentok['sessionId'], 'token' => $opentok['token']));
		$meeting_id = $wpdb->insert_id;
		
		$opentok['id'] = $meeting_id;
		$status = $_GET['st'] ? 3 : 2;
		foreach(json_decode(file_get_contents('php://input'))->data as $d)
		{
			$wpdb->update($wpdb->prefix . "meeting_participants", 
						array(	'meeting_id' => $meeting_id, 
								'meeting_date' => date("Y-m-d H:i:s"),
								'status' => $status
							),
						array("id" => $d)
			);
		}
		
		echo json_encode($opentok);
		
		die(0);
		exit;
	}
	
	function agent_status()
	{
		$general = get_option("general");
		$arr = array(1 => 'Online', 2 => 'Offline', 3 => 'Meeting', 4 => 'Away');
		$user_current_status = get_user_meta($general['agent'], 'user_current_status', true);
		echo $arr[$user_current_status];
		die(0);
		exit;
	}
	
	function waiting_participants()
	{
		global $wpdb;
		
		$results = $wpdb->get_results("select * from ".$wpdb->prefix . "meeting_participants where status = 1");
		
		echo json_encode($results);
		die(0);
		exit;
	}
	
	function new_participants()
	{
		global $wpdb;
		
		$results = $wpdb->get_row("select * from ".$wpdb->prefix . "meeting_participants where id = ".$_GET['id']);
		
		echo json_encode($results);
		die(0);
		exit;
	}
	
	function join_chat()
	{
		/*$option = get_option('pusher');
		
		$app_id = $option['id'];
		$app_key = $option['app'];
		$app_secret = $option['secret'];

		$pusher = new Pusher(
		  $app_key,
		  $app_secret,
		  $app_id,
		  array('encrypted' => true)
		);

		$pusher->trigger('test_channel', 'my_event', $_POST);*/
		
		global $wpdb;
		
		$wpdb->insert($wpdb->prefix . "meeting_participants", $_POST['meeting']);
		echo $wpdb->insert_id;
		
		setcookie("instant_connect_waiting_id", $wpdb->insert_id, time() + (86400 * 365), "/");
		
		die(0);
		exit;
	}
}