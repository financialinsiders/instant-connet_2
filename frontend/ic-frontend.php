<?php
class IC_front{
	
	function __construct() {
		add_action( 'wp_footer', array( &$this, 'instant_connect_embed_chat'), 100 );
	}

	function instant_connect_embed_chat()
	{
		
		$siteidd = isset($_COOKIE['fi_agent_site']) ? $_COOKIE['fi_agent_site']:  get_current_blog_id();

		$agent_id = get_blog_option($_COOKIE['fi_agent_site'], 'agent_id');

		if($agent_id){
		?>
		<script type="text/javascript">

            var $fiApp = $fiApp || {};
            $fiApp.instanceId = "<?php echo $agent_id ?>";
            $fiApp.welcomeMsg = "<?php echo stripslashes(get_post_meta( get_the_id(), 'chat_welcome_message', true ));?>";
	    	
			
            function getCookie(cname) {
			    var name = cname + "=";
			    var ca = document.cookie.split(';');
			    for(var i = 0; i < ca.length; i++) {
			        var c = ca[i];
			        while (c.charAt(0) == ' ') {
			            c = c.substring(1);
			        }
			        if (c.indexOf(name) == 0) {
			            return c.substring(name.length, c.length);
			        }
			    }
			    return "";
			}

            var $cookie_track_link = atob(atob(decodeURIComponent(getCookie('endorsement_track_link')))).split('#&$#');
			$fiApp.endorserId = $cookie_track_link.length ? $cookie_track_link[1] : "";
			
			$fiApp.page = window.location.href;
			$fiApp.siteId = "<?php echo $siteidd;?>";
			$fiApp.blogUrl = "<?php echo get_site_url($siteidd);?>";
			$fiApp.visited_page = getCookie('fa_surfing_page');
			$fiApp.introVideo = "<?php if(!empty($_GET['video'])) { echo $_GET['video']; } ?>";
			<?php $previewMode = isset($_GET['preview_mode']) ? 'previewMode=1' : ''; ?>
			<?php
			if(get_post_type( get_the_ID() ) == 'ic-chat-bot'){
				$botId = get_the_ID();
				$chat_type = get_post_meta($botId, 'chat_type', true);
				$appearance = (array) get_post_meta($botId, 'appearance', true);
				$fullScreen = $appearance['fullscreen'];

				if($chat_type == 'Endorser Send Invitation'){
					?>
					var d = document, s = d.createElement('iframe'); 
					s.src = "https://financialinsiders.github.io/endorsers/#!/cb/<?= $botId?>?detailInfo=<?= $_GET['detailInfo']?>&<?= $previewMode?>";
					s.style.position = 'fixed';
					s.style.width = "100%";
					s.style.height = "100%";
					s.style.left = 0;
					s.style.top = 0;
					s.style.border = "none";
					s.style['z-index'] = 9999;
					s.allow = 'camera; microphone';
					d.body.appendChild(s);
					<?php
				} elseif($chat_type == 'Endorser Registeration'){
					?>
					var d = document, s = d.createElement('iframe'); 
					s.src = "https://financialinsiders.github.io/endorsers/#!/register/<?= $botId?>/<?= $agent_id?>?<?= $previewMode?>";
					s.style.position = 'fixed';
					s.style.width = "100%";
					s.style.height = "100%";
					s.style.left = 0;
					s.style.top = 0;
					s.style.border = "none";
					s.style['z-index'] = 9999;
					d.body.appendChild(s);
					s.allow = 'camera; microphone';
					<?php
				} else {
					?>
						$fiApp.botId = "<?php echo $botId;?>";
						$fiApp.meetingId = "<?php echo $_GET['meetingId'];?>";
						$fiApp.params = {};
						<?php 
							foreach ($_GET as $key => $value) {
								?>
								$fiApp.params['<?= strtoupper($key);?>'] = '<?= $value;?>';
								<?php
							}

							if(isset($_GET['endorser_id'])){?>
								$fiApp.params['ENDORSER_FIRST_NAME'] = '<?= get_user_meta($_GET['endorser_id'], 'first_name', true);?>';
								$fiApp.params['ENDORSER_LAST_NAME'] = '<?= get_user_meta($_GET['endorser_id'], 'last_name', true);?>';
							<?php }
						?>
						<?php if(isset($fullScreen)) { ?>
						$fiApp.isFullScreen = "<?php echo $fullScreen;?>";
						<?php  } ?>
						$fiApp.previewMode = false;
						<?php if(isset($_GET['preview_mode'])) { ?>
								$fiApp.previewMode = true;
						 		
						<?php  } ?>
						if($fiApp.endorserId || $fiApp.siteId){
							var d = document, s = d.createElement('script'); 
				            s.type = "text/javascript";
				            s.src = "https://financialinsiders.ca/fichat/fichat.script.js";
				            d.body.appendChild(s);
				            s.allow = 'camera; microphone';
						}
					<?php
				}


			} else {
				$botId = get_user_meta($agent_id, 'status_data_Welcome', true);
				// $fullScreen = "false";

				if(isset($_GET['greetingID']) || isset($_GET['conversationID'])){
					$botId = $_GET['greetingID'];
					$botId2 = $_GET['conversationID'];
				} elseif(get_the_title() == 'Home'){
					$botId = get_user_meta($agent_id, 'conversation_bot_id', true);
					$botId2 = get_user_meta($agent_id, 'greetings_bot_id', true);
				} elseif(get_the_title() == 'About') {
					$botId = get_user_meta($agent_id, 'about_conversation_bot_id', true);
					$botId2 = get_user_meta($agent_id, 'about_greetings_bot_id', true);
				} elseif(get_the_title() == 'Our approach'){
					$botId = get_user_meta($agent_id, 'approach_conversation_bot_id', true);
					$botId2 = get_user_meta($agent_id, 'approach_greetings_bot_id', true);
				}
				?>
					$fiApp.botId = "<?php echo $botId;?>";
					$fiApp.botId2 = "<?php echo $botId2;?>";
					<?php if(isset($fullScreen)) { ?>
					$fiApp.isFullScreen = "<?php echo $fullScreen;?>";
					<?php  } ?>
					if($fiApp.endorserId || $fiApp.siteId){
						var d = document, s = d.createElement('script'); 
			            s.type = "text/javascript";
			            s.src = "https://financialinsiders.ca/fichat/fichat.script.js";
			            d.body.appendChild(s);
			            s.allow = 'camera; microphone';
					}
				<?php
			}
			?>
			
        </script>
		<?php
		}
	}
	
	function instant_connect_chat_icon()
	{
		$general = get_option('general');

		$lst_login_time = get_user_meta($general['agent'], 'user_logintime', true);
		if(strtotime("now") - strtotime($lst_login_time) > 60)
		{
			update_user_meta($general['agent'], 'user_current_status', 2);
			update_user_meta($general['agent'], 'agent_communication_mode', 1);
		}

		$com_mode = get_user_meta($general['agent'], 'agent_communication_mode', true);

		$arr = array(1 => 'question_mode', 2 => 'chat_mode', 3 => 'ic');

		$fn = 'instant_connect_chat_icon_'.$arr[$com_mode];

		//$this->$fn();
		include($arr[$com_mode].'.php');
	}

	function instant_connect_chat_icon_question_mode()
	{
		global $wpdb;
		$option = get_option('chat_position');
		$general = get_option('general');
		
		$is_waiting = isset($_COOKIE['instant_connect_waiting_id']) ? $_COOKIE['instant_connect_waiting_id'] : 0;
		
		if($is_waiting)
		{
			$wuser = $wpdb->get_row("select * from ".$wpdb->prefix . "meeting_participants where (status = 1 or status = 2) and id = ".$is_waiting);
			if(!count($wuser))
			{
				$is_waiting = 0;
				setcookie("instant_connect_waiting_id", "", time()-3600, "/");
			}
		}
		
		
		$user_info = get_userdata($general['agent']);
		$arr = array(1 => 'Online', 2 => 'Offline', 3 => 'Meeting', 4 => 'Away');

		$user_current_status = get_user_meta($general['agent'], 'user_current_status', true);
		$com_mode = get_user_meta($general['agent'], 'agent_communication_mode', true);
		?>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular.min.js" type="text/javascript" charset="utf-8"></script>
		<script src='https://cdn.firebase.com/js/client/2.2.1/firebase.js'></script>
		<script type="text/javascript" src="//wurfl.io/wurfl.js"></script>
		<script src='https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/components/core.js'></script>
		<script src='https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/components/md5-min.js'></script>
		<style>
		.instant_connect_form{transition:all 350ms ease 0s; right:100px; position:fixed; background:#DBDBDB; bottom:120px; width:300px; padding:20px;}
		.instant_connect_form.join_chat{right:40px; }
		.instant_connect_form label, .instant_connect_form input{display:block; width:100%;}
		.submit_btn{position:relative;}
		.submit_btn img{display:none; position:absolute; top:13px;}
		.instant_connect_form.submitted{opacity:0.7; cursor_pointer:none;}
		.instant_connect_form.submitted .submit_btn img{display:inline-block}
		</style>
		<div ng-app="demo" ng-controller="ActionController" class="instant_connect_form hide_when_start" >
			<form  id="instant_connect_form" onSubmit="return false;">
				<div id="question_mode" <?= $com_mode != 1 ? 'style="display:none;"' : '';?>>
					<div style="display: inline-block; overflow: hidden; border-radius: 50%; border: 5px solid rgb(204, 204, 204); width: 50px; height: 50px;border-radius: 50%;">
						<?php echo get_avatar( $user_info->user_email, 50 ); ?>
					</div>
					<p style="font-size: 14px;"><?= $user_info->username; ?>(Agent) is available to take your questions please put your name and email.</p>
					
				</div>
				<div ng-if="chat.length" id="messagesDiv" style="height:250px;overflow:auto;">
					<p ng-repeat="c in chat track by $index" on-finish-render ng-class="{align_right: c.email != data.email}" ng-if="c.msg">
						<img ng-if="c.email == data.email" ng-src="http://www.gravatar.com/avatar/{{c.hash}}/?s=30"> 
						{{c.msg}}
						<img ng-if="c.email != data.email" ng-src="http://www.gravatar.com/avatar/{{c.hash}}/?s=30"> 
						<hr>
					</p>
				</div>
				
				<div ng-hide="getinput || chat.length == 1" class="submit_btn">
					<p>
						<textarea placeholder="Question" ng-model="data.msg"></textarea>
					</p>
					<input type="submit" ng-click="add();" name="Submit" value="Submit">
					<img src="<?= IC_PLUGIN_URL; ?>294.gif">
				</div>
				<div ng-show="getinput" class="submit_btn">
					<p>
						<input placeholder="Name" ng-model="data.name">
					</p>
					<p>
						<input placeholder="Email" ng-model="data.email">
					</p>
					<input type="submit" ng-click="update_user();" name="Submit" value="Submit">
					<img src="<?= IC_PLUGIN_URL; ?>294.gif">
				</div>

			</form>
			
		</div>

		<script type="text/javascript">
			var textchatref;

			angular.module('demo', []).controller('ActionController', ['$scope', '$timeout', '$http', function($scope, $timeout, $http) {
					
					$scope.chat = [];
					$scope.data = {name:"", email:"", msg:""};
					$scope.meeting = {};

					$scope.getinput = false;

					$scope.start_chating = function(res){


						textchatref = new Firebase('https://vinogautam.firebaseio.com/pusher/individual_chat/'+res+'/');
						textchatref.push($scope.data);
						$scope.data.msg = '';
						textchatref.on('child_added', function(snapshot) {
							v = snapshot.val();
							console.log(v);
							if(typeof v.msg != "undefined")
							{
								hn = v.email ? v.email : v.name;
								v.hash = CryptoJS.MD5(hn).toString();
								if(!$scope.$$phase) {
								$scope.$apply(function(){
									$scope.chat.push(v);
								});
								}
								else
								{
									$scope.chat.push(v);
								}
							}
						});
					};
					
					$scope.participant = 0;
					$scope.video_container = false;
					$scope.add = function(){
						if($scope.chat.length)
						{
							textchatref.push($scope.data);
							$scope.data.msg = '';
						}
						else
						{
							data = {action:'join_chat', meeting: {mode:1, is_mobile: WURFL.is_mobile, question: $scope.data.msg, complete_device_name: WURFL.complete_device_name, form_factor:WURFL.form_factor, status: 1}};
							jQuery.post('<?php echo site_url();?>/wp-admin/admin-ajax.php', data, function(res){
									$scope.start_chating(res);
									$scope.participant = res;
									$timeout(function(){
										if($scope.chat.length == 1)
										$scope.getinput = true;
									}, 600);

									var online_status = new Firebase('https://vinogautam.firebaseio.com/pusher/online_status');
									cccnt = 1;
									setInterval(function(){
										online_status.update({ count:res+"-"+cccnt++});
									}, 5000);
									var myDataRef = new Firebase('https://vinogautam.firebaseio.com/pusher/new_user');
									myDataRef.update({ count:res});


									var meetingRef = new Firebase('https://vinogautam.firebaseio.com/pusher/new_meeting');
									var meetingstatus = 0;
									meetingRef.on('value', function(snapshot) {
										mid = snapshot.val().id;
										if(typeof mid != "number")
											mid = mid.split("-")[0];
										
										meetingstatus++;
										if(meetingstatus != 1)
										{
											jQuery.get('<?php echo site_url();?>/wp-admin/admin-ajax.php?action=check_meeing&participants='+res+'&meeting_id='+mid, function(res1){
												if(!res1) return;
												res1 = JSON.parse(res1);
												console.log(res1);
												if(res1.status == 2)
												{	
													$scope.$apply(function(){
														$scope.video_container = true;
													});
												}
											});
										}
										
									});
								}
							);
						}
					};
					
					$scope.update_user = function(){
						jQuery.post('<?php echo site_url();?>/wp-admin/admin-ajax.php', {action:'update_participant_data', id: $scope.participant, data: {name: $scope.data.name, email: $scope.data.email}}, function(res){
							$scope.getinput = false;

						});
					};
			}]);
		</script>

	<?php 
	}

	function instant_connect_chat_icon_chat_mode()
	{
		global $wpdb;
		$option = get_option('chat_position');
		$general = get_option('general');
		
		$is_waiting = isset($_COOKIE['instant_connect_waiting_id']) ? $_COOKIE['instant_connect_waiting_id'] : 0;
		
		if($is_waiting)
		{
			$wuser = $wpdb->get_row("select * from ".$wpdb->prefix . "meeting_participants where (status = 1 or status = 2) and id = ".$is_waiting);
			if(!count($wuser))
			{
				$is_waiting = 0;
				setcookie("instant_connect_waiting_id", "", time()-3600, "/");
			}
		}
		
		
		$user_info = get_userdata($general['agent']);
		$arr = array(1 => 'Online', 2 => 'Offline', 3 => 'Meeting', 4 => 'Away');

		$user_current_status = get_user_meta($general['agent'], 'user_current_status', true);
		$com_mode = get_user_meta($general['agent'], 'agent_communication_mode', true);
		?>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular.min.js" type="text/javascript" charset="utf-8"></script>
		<script src='https://cdn.firebase.com/js/client/2.2.1/firebase.js'></script>
		<script type="text/javascript" src="//wurfl.io/wurfl.js"></script>
		<script src='https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/components/core.js'></script>
		<script src='https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/components/md5-min.js'></script>
		<style>
		.instant_connect_form{transition:all 350ms ease 0s; right:100px; position:fixed; background:#DBDBDB; bottom:120px; width:300px; padding:20px;}
		.instant_connect_form.join_chat{right:40px; }
		.instant_connect_form label, .instant_connect_form input{display:block; width:100%;}
		.submit_btn{position:relative;}
		.submit_btn img{display:none; position:absolute; top:13px;}
		.instant_connect_form.submitted{opacity:0.7; cursor_pointer:none;}
		.instant_connect_form.submitted .submit_btn img{display:inline-block}
		</style>
		<div ng-app="demo" ng-controller="ActionController" class="instant_connect_form hide_when_start" >
			<form  id="instant_connect_form" onSubmit="return false;">
				<div id="question_mode" <?= $com_mode != 1 ? 'style="display:none;"' : '';?>>
					<div style="display: inline-block; overflow: hidden; border-radius: 50%; border: 5px solid rgb(204, 204, 204); width: 50px; height: 50px;border-radius: 50%;">
						<?php echo get_avatar( $user_info->user_email, 50 ); ?>
					</div>
					<p style="font-size: 14px;"><?= $user_info->username; ?>(Agent) is available to take your questions please put your name and email.</p>
					
				</div>
				<div ng-if="chat.length" id="messagesDiv" style="height:250px;overflow:auto;">
					<p ng-repeat="c in chat track by $index" on-finish-render ng-class="{align_right: c.email != data.email}" ng-if="c.msg">
						<img ng-if="c.email == data.email" ng-src="http://www.gravatar.com/avatar/{{c.hash}}/?s=30"> 
						{{c.msg}}
						<img ng-if="c.email != data.email" ng-src="http://www.gravatar.com/avatar/{{c.hash}}/?s=30"> 
						<hr>
					</p>
				</div>
				<div ng-hide="enable_chat" class="submit_btn">
					<p>
						<input placeholder="Name" ng-model="data.name">
					</p>
					<p>
						<input placeholder="Email" ng-model="data.email">
					</p>
					<input type="submit" ng-click="add();" name="Submit" value="Submit">
					<img src="<?= IC_PLUGIN_URL; ?>294.gif">
				</div>
				
				<div ng-show="enable_chat" class="submit_btn">
					<p>
						<textarea placeholder="Question" ng-model="data.msg"></textarea>
					</p>
					<input type="submit" ng-click="addmsg();" name="Submit" value="Submit">
					<img src="<?= IC_PLUGIN_URL; ?>294.gif">
				</div>
			</form>
			
		</div>

		<script type="text/javascript">
			var textchatref;

			angular.module('demo', []).controller('ActionController', ['$scope', '$timeout', '$http', function($scope, $timeout, $http) {
					
					$scope.chat = [];
					$scope.data = {name:"", email:"", msg:""};
					$scope.meeting = {};

					$scope.getinput = false;

					$scope.start_chating = function(res){


						textchatref = new Firebase('https://vinogautam.firebaseio.com/pusher/individual_chat/'+res+'/');
						textchatref.push($scope.data);
						$scope.data.msg = '';
						textchatref.on('child_added', function(snapshot) {
							v = snapshot.val();
							console.log(v);
							if(typeof v.msg != "undefined")
							{
								hn = v.email ? v.email : v.name;
								v.hash = CryptoJS.MD5(hn).toString();
								if(!$scope.$$phase) {
								$scope.$apply(function(){
									$scope.chat.push(v);
								});
								}
								else
								{
									$scope.chat.push(v);
								}
							}
						});
					};
					
					$scope.participant = 0;
					$scope.video_container = false;
					$scope.enable_chat = false;
					
					$scope.addmsg = function(){
						textchatref.push($scope.data);
						$scope.data.msg = '';
					}

					$scope.add = function(){
						if($scope.chat.length)
						{
							textchatref.push($scope.data);
							$scope.data.msg = '';
						}
						else
						{
							data = {action:'join_chat', meeting: {mode:2, is_mobile: WURFL.is_mobile, name: $scope.data.name, email: $scope.data.email, complete_device_name: WURFL.complete_device_name, form_factor:WURFL.form_factor, status: 1}};
							jQuery.post('<?php echo site_url();?>/wp-admin/admin-ajax.php', data, function(res){
									$scope.start_chating(res);
									$scope.participant = res;
									$scope.enable_chat = true;
									$timeout(function(){
										if($scope.chat.length == 1)
										$scope.getinput = true;
									}, 600);

									var online_status = new Firebase('https://vinogautam.firebaseio.com/pusher/online_status');
									cccnt = 1;
									setInterval(function(){
										online_status.update({ count:res+"-"+cccnt++});
									}, 5000);
									var myDataRef = new Firebase('https://vinogautam.firebaseio.com/pusher/new_user');
									myDataRef.update({ count:res});


									var meetingRef = new Firebase('https://vinogautam.firebaseio.com/pusher/new_meeting');
									var meetingstatus = 0;
									meetingRef.on('value', function(snapshot) {
										mid = snapshot.val().id;
										if(typeof mid != "number")
											mid = mid.split("-")[0];
										
										meetingstatus++;
										if(meetingstatus != 1)
										{
											jQuery.get('<?php echo site_url();?>/wp-admin/admin-ajax.php?action=check_meeing&participants='+res+'&meeting_id='+mid, function(res1){
												if(!res1) return;
												res1 = JSON.parse(res1);
												console.log(res1);
												if(res1.status == 2)
												{	
													$scope.$apply(function(){
														$scope.video_container = true;
													});
												}
											});
										}
										
									});
								}
							);
						}
					};
					
					$scope.update_user = function(){
						jQuery.post('<?php echo site_url();?>/wp-admin/admin-ajax.php', {action:'update_participant_data', id: $scope.participant, data: {name: $scope.data.name, email: $scope.data.email}}, function(res){
							$scope.getinput = false;

						});
					};
			}]);
		</script>

	<?php 
	}

	function instant_connect_chat_icon_ic()
	{
		global $wpdb;
		$option = get_option('chat_position');
		$general = get_option('general');
		
		$is_waiting = isset($_COOKIE['instant_connect_waiting_id']) ? $_COOKIE['instant_connect_waiting_id'] : 0;
		
		if($is_waiting)
		{
			$wuser = $wpdb->get_row("select * from ".$wpdb->prefix . "meeting_participants where (status = 1 or status = 2) and id = ".$is_waiting);
			if(!count($wuser))
			{
				$is_waiting = 0;
				setcookie("instant_connect_waiting_id", "", time()-3600, "/");
			}
		}
		
		
		$user_info = get_userdata($general['agent']);
		$arr = array(1 => 'Online', 2 => 'Offline', 3 => 'Meeting', 4 => 'Away');

		$user_current_status = get_user_meta($general['agent'], 'user_current_status', true);
		$com_mode = get_user_meta($general['agent'], 'agent_communication_mode', true);
		?>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular.min.js" type="text/javascript" charset="utf-8"></script>
		<script src='https://cdn.firebase.com/js/client/2.2.1/firebase.js'></script>
		<script type="text/javascript" src="//wurfl.io/wurfl.js"></script>
		<style>
		<?php if($option == 1){?>
		.chat_icon{width:70px; height:70px; border-radius:50%; background:#5C93C1; text-align:center; position:fixed; top:30px; left:30px; cursor:pointer;}
		.chat_icon i{font-size:35px; color:#fff; display:inline-block; padding:15px;}
		.instant_connect_form{transition:all 350ms ease 0s; left:-400px; position:fixed; background:#DBDBDB; top:120px; width:300px; padding:20px;}
		.instant_connect_form.join_chat{left:40px; }
		.instant_connect_form label, .instant_connect_form input{display:block; width:100%;}
		<?php }elseif($option == 2){?>
		.chat_icon{width:70px; height:70px; border-radius:50%; background:#5C93C1; text-align:center; position:fixed; right:30px; top:30px; cursor:pointer;}
		.chat_icon i{font-size:35px; color:#fff; display:inline-block; padding:15px;}
		.instant_connect_form{transition:all 350ms ease 0s; right:-400px; position:fixed; background:#DBDBDB; top:120px; width:300px; padding:20px;}
		.instant_connect_form.join_chat{right:40px; }
		.instant_connect_form label, .instant_connect_form input{display:block; width:100%;}
		<?php }elseif($option == 3){?>
		.chat_icon{width:70px; height:70px; border-radius:50%; background:#5C93C1; text-align:center; position:fixed; left:30px; bottom:30px; cursor:pointer;}
		.chat_icon i{font-size:35px; color:#fff; display:inline-block; padding:15px;}
		.instant_connect_form{transition:all 350ms ease 0s; left:-400px; position:fixed; background:#DBDBDB; bottom:120px; width:300px; padding:20px;}
		.instant_connect_form.join_chat{left:40px; }
		.instant_connect_form label, .instant_connect_form input{display:block; width:100%;}
		<?php }elseif($option == 4){?>
		.chat_icon{width:70px; height:70px; border-radius:50%; background:#5C93C1; text-align:center; position:fixed; right:30px; bottom:30px; cursor:pointer;}
		.chat_icon i{font-size:35px; color:#fff; display:inline-block; padding:15px;}
		.instant_connect_form{transition:all 350ms ease 0s; right:-400px; position:fixed; background:#DBDBDB; bottom:120px; width:300px; padding:20px;}
		.instant_connect_form.join_chat{right:40px; }
		.instant_connect_form label, .instant_connect_form input{display:block; width:100%;}
		<?php }else{?>
		.chat_icon{width:70px; height:70px; border-radius:50%; background:#5C93C1; text-align:center; position:fixed; right:30px; bottom:30px; cursor:pointer;}
		.chat_icon i{font-size:35px; color:#fff; display:inline-block; padding:15px;}
		.instant_connect_form{transition:all 350ms ease 0s; right:-400px; position:fixed; background:#DBDBDB; bottom:120px; width:300px; padding:20px;}
		.instant_connect_form.join_chat{right:40px; }
		.instant_connect_form label, .instant_connect_form input{display:block; width:100%;}
		<?php }?>
		.submit_btn{position:relative;}
		.submit_btn img{display:none; position:absolute; top:13px;}
		.instant_connect_form.submitted{opacity:0.7; cursor_pointer:none;}
		.instant_connect_form.submitted .submit_btn img{display:inline-block}
		</style>
		<div class="chat_icon hide_when_start">
			<i class="fa fa-comments"></i>
		</div>
		<div class="instant_connect_form hide_when_start" >
			<form  id="instant_connect_form" onSubmit="return false;">
				<?php if($is_waiting == 0){?>
				<div id="question_mode" <?= $com_mode != 1 ? 'style="display:none;"' : '';?>>
					<div style="display: inline-block; overflow: hidden; border-radius: 50%; border: 5px solid rgb(204, 204, 204); width: 50px; height: 50px;border-radius: 50%;">
						<?php echo get_avatar( $user_info->user_email, 50 ); ?>
					</div>
					<p style="font-size: 14px;"><?= $user_info->username; ?>(Agent) is available to take your questions please put your name and email.</p>
					<p>
						<textarea placeholder="Question" name="meeting[question]" id="questionInput"></textarea>
					</p>
				</div>
				<div id="chatnicmode" <?= $com_mode == 1 ? 'style="display:none;"' : '';?>>
					<p>
						<input placeholder="Name" type="text" name="meeting[name]" id="nameInput">
					</p>
					<p>
						<input placeholder="Email" type="text" name="meeting[email]" id="emailInput">
					</p>
				</div>
				<input type="hidden" name="action" value="join_chat">
				<input type="hidden" name="meeting[mode]" value="<?= $com_mode;?>">
				<input type="hidden" id="is_mobile" name="meeting[is_mobile]" value="join_chat">
				<input type="hidden" id="complete_device_name" name="meeting[complete_device_name]" value="join_chat">
				<input type="hidden" id="form_factor" name="meeting[form_factor]" value="join_chat">
				<input type="hidden" name="meeting[status]" value="1">
				<div class="submit_btn">
					<input type="submit" id="instant_connect_formsubmit" name="Submit" value="Join Live Chat">
					<img src="<?= IC_PLUGIN_URL; ?>294.gif">
				</div>
				<?php }else{?>
					<div style="display: inline-block; overflow: hidden; border-radius: 50%; border: 5px solid rgb(204, 204, 204); width: 150px; height: 150px;">
						<?php echo get_avatar( $user_info->user_email, 150 ); ?>
					</div>
					<div id="agent_status"><?php _e($arr[$user_current_status]); ?></div>
					<br>
					<p>Hello <?php echo $wuser->name;?>, Please wait for a meeting.</p>
				<?php }?>
			</form>
			
		</div>
		<?php if($is_waiting){?>
		<div ng-app="demo" ng-controller="ActionController" class="text_chat_container" style="display:none;position: fixed; top: 0; bottom:0;right:0;width: 300px; background: rgb(255, 255, 255) none repeat scroll 0% 0%;border:1px solid #000;">
						<iframe ng-if="meeting" ng-src="{{'<?= str_replace("http://financialinsiders.ca/", "https://financialinsiders.ca/", site_url()); ?>/meeting/?id='+meeting.mid+'&only_video'}}"></iframe>
						<div style="position:absolute;bottom:0;">
							<div id="messagesDiv" style="height:250px;overflow:auto;">
								<p ng-repeat="c in chat track by $index" on-finish-render ng-class="{align_right: c.email != data.email}" ng-if="c.msg">
									<img ng-if="c.email == data.email" ng-src="http://www.gravatar.com/avatar/{{c.hash}}/?s=30"> 
									{{c.msg}}
									<img ng-if="c.email != data.email" ng-src="http://www.gravatar.com/avatar/{{c.hash}}/?s=30"> 
									<hr>
								</p>
							</div>
							<p ng-show="noti">{{noti.name}} is typing...<p>
							<form>
								<input size="43" type="hidden" ng-model="data.name" placeholder="Name">
								<input size="43" type="hidden" ng-model="data.email" placeholder="Email">
								<textarea rows="2" cols="33" ng-model="data.msg" ng-keyup="send_noti()" placeholder="Message" ng-enter="add();"></textarea>
								<div style="text-align:center;">
									<button ng-click="add();">Post</button>
								</div>
							</form>
						</div>
			</div>
			
		<script src='https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/components/core.js'></script>
		<script src='https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/components/md5-min.js'></script>
		<script src="//static.opentok.com/v2.6/js/opentok.js" type="text/javascript" charset="utf-8"></script>
		<script src="<?= IC_PLUGIN_URL; ?>js/opentok-layout.js" type="text/javascript" charset="utf-8"></script>
        <script src="<?= IC_PLUGIN_URL; ?>js/opentok-angular.js" type="text/javascript" charset="utf-8"></script>
        <?php }?>
		<script>
			
			
			var count = 0,textchatref,scope;
			jQuery(document).ready(function($){
					
					$("#is_mobile").val(WURFL.is_mobile);
					$("#complete_device_name").val(WURFL.complete_device_name);
					$("#form_factor").val(WURFL.form_factor);

					<?php if(isset($wuser) && $wuser->status==2){?>
					jQuery.get('<?php echo site_url();?>/wp-admin/admin-ajax.php?action=check_meeing&participants=<?php echo $is_waiting;?>&meeting_id=<?= $wuser->meeting_id?>', function(res){
								if(!res) return;
								res = JSON.parse(res);
								console.log(res);
								if(res.status == 3)
									window.location.assign("<?= wp_nonce_url(str_replace("http://financialinsiders.ca", "https://financialinsiders.ca", site_url("/meeting/")),'finonce','finonce');?>&id="+res.mid+"&pid="+res.pid);
								else if(res.status == 2)
								{	
									jQuery(".text_chat_container").show();
									jQuery(".hide_when_start").hide();
									scope = angular.element(jQuery(".text_chat_container")).scope();
									scope.$apply(function(){
										scope.start_chating(res);
									});
									
								}
							});
					<?php }?>

					jQuery(".chat_icon").click(function(){
						jQuery(".instant_connect_form").toggleClass("join_chat");
					});
					
					//http://45.58.38.227/bigbluebutton/api/join?meetingID=Demo+Meeting&fullName=&password=mp&checksum=54dd35ccbc2e1377ab5667ae7d84d6ed815af332
					
					<?php if($is_waiting){?>
					
					var myDataRef = new Firebase('https://vinogautam.firebaseio.com/pusher/new_user');
					myDataRef.update({ count:<?php echo $is_waiting;?>});
					
					var statusRef = new Firebase('https://vinogautam.firebaseio.com/pusher/status_change');
					statusRef.on('value', function(snapshot) {
						jQuery.get('<?php echo site_url();?>/wp-admin/admin-ajax.php?action=get_status', function(res){
							jQuery("#agent_status").text(res);
							jQuery(".instant_connect_form").addClass("join_chat");
						});
					});
					jQuery(".instant_connect_form").addClass("join_chat");
					
					var meetingRef = new Firebase('https://vinogautam.firebaseio.com/pusher/new_meeting');
					var meetingstatus = 0;
					meetingRef.on('value', function(snapshot) {
						mid = snapshot.val().id;
						if(typeof mid != "number")
							mid = mid.split("-")[0];
						
						meetingstatus++;
						if(meetingstatus != 1)
						{
							jQuery.get('<?php echo site_url();?>/wp-admin/admin-ajax.php?action=check_meeing&participants=<?php echo $is_waiting;?>&meeting_id='+mid, function(res){
								if(!res) return;
								res = JSON.parse(res);
								console.log(res);
								if(res.status == 3)
									window.open("<?= wp_nonce_url(str_replace("http://financialinsiders.ca", "https://financialinsiders.ca", site_url("/meeting/")),'finonce','finonce');?>&id="+res.mid+"&pid="+res.pid);
								else if(res.status == 2)
								{	
									jQuery(".text_chat_container").show();
									jQuery(".hide_when_start").hide();
									scope = angular.element(jQuery(".text_chat_container")).scope();
									scope.$apply(function(){
										scope.start_chating(res);
									});
									
								}
							});
						}
						
					});
					
					var online_status = new Firebase('https://vinogautam.firebaseio.com/pusher/online_status');
					cccnt = 1;
					setInterval(function(){
						online_status.update({ count:"<?php echo $is_waiting;?>-"+cccnt++});
					}, 5000);
					
					<?php }else{?>
					
					//var myDataRef = new Firebase('https://vinogautam.firebaseio.com/pusher/new_user');
					jQuery("#instant_connect_formsubmit").click(function(){
						jQuery(".instant_connect_form").addClass("submitted");
						jQuery.post('<?php echo site_url();?>/wp-admin/admin-ajax.php',jQuery("#instant_connect_form").serialize(), function(res){
							//var name = jQuery('#nameInput').val();
							//var email = jQuery('#emailInput').val();
							//myDataRef.push({name: name, email: email});
							console.log(res);
							//myDataRef.update({ count:res});
							setTimeout(function(){location.reload();}, 3000);
						});
					});
					
					<?php }?>
			});
			
			<?php if($is_waiting){?>
			angular.module('demo', ['opentok'])
			.directive('ngEnter', function() {
			return function(scope, element, attrs) {
				element.bind("keydown keypress", function(event) {
					if(event.which === 13) {
							scope.$apply(function(){
									scope.$eval(attrs.ngEnter);
							});
							
							event.preventDefault();
					}
				});
			};
		})
		.directive('onFinishRender', function ($timeout) {
    return {
        restrict: 'A',
        link: function (scope, element, attr) {
            if (scope.$last === true) {
                $timeout(function () {
                    //scope.$emit(attr.onFinishRender);
					jQuery("#messagesDiv").scrollTop(jQuery("#messagesDiv")[0].scrollHeight);
                });
            }
        }
    }
})
			.controller('ActionController', ['$scope', '$timeout', '$http', 'OTSession', function($scope, $timeout, $http, OTSession) {
					
					$scope.chat = [];
					$scope.data = {name:"", email:"", msg:""};
					$scope.meeting = {};
					$scope.start_chating = function(res){
						console.log("start cghat");

						setTimeout(function(){
							$http.get('<?php echo site_url();?>/wp-admin/admin-ajax.php?action=add_chat_points&id='+res.pid).then(function(res){

							});
						}, 300000);


						textchatref = new Firebase('https://vinogautam.firebaseio.com/opentok/'+res.pid+'/'+res.sessionId);
						$scope.data.name = res.name;
						$scope.data.email = res.email;
						$scope.meeting = res;

						textchatref.on('child_added', function(snapshot) {
									//angular.forEach(snapshot.val(), function(v,k){
										v = snapshot.val();
										console.log(v);
										if(typeof v.msg != "undefined")
										{
											hn = v.email ? v.email : v.name;
											v.hash = CryptoJS.MD5(hn).toString();
											if(!$scope.$$phase) {
											$scope.$apply(function(){
												$scope.chat.push(v);
											});
											}
											else
											{
												$scope.chat.push(v);
											}
										}
										
									//});
								});

						OTSession.init('<?= API_KEY;?>', $scope.meeting.sessionId, $scope.meeting.token, function (err) {
								if (!err) {
									console.log(OTSession.session);

									OTSession.session.on('signal:user-notifications', function (event) {
									console.log(event);
									if(typeof event.data != "undefined" && event.data.indexOf("video") != -1 && parseInt(event.data.split("_")[2]) == $scope.meeting.pid)
									{
										console.log(event.data.split("_"));
										$scope.$apply(function(){
											$scope.show_video = parseInt(event.data.split("_")[1]);
											if($scope.show_video == 0)
												$scope.pvideo = 0;
										});
									}
									else if(typeof event.data != "undefined" && event.data.indexOf("whiteboard") != -1 && parseInt(event.data.split("_")[2]) == $scope.meeting.pid)
									{
										console.log(event.data.split("_"));
										$scope.$apply(function(){
											$scope.show_whiteboard = parseInt(event.data.split("_")[1]);
										});
									}
									else if(typeof event.data != "undefined" && event.data.indexOf("exituser") != -1 && parseInt(event.data.split("_")[1]) == $scope.meeting.pid)
									{
										console.log(event.data.split("_"));
										allowtoleave = true;
										window.location.assign(event.data.split("_")[2]);
									}
									else if(typeof event.data != "undefined" && event.data.indexOf("exitalluser") != -1)
									{
										allowtoleave = true;
										window.location.assign(event.data.split("_")[1]);
									}
									else if(typeof event.data != "undefined" && event.data.indexOf("acktocheckuserison") != -1 && parseInt(event.data.split("_")[1]) == $scope.meeting.pid)
									{
										$scope.send_noti("imhere_"+event.data.split("_")[1]);
									}
									else if(typeof event.data != "undefined" && event.data.indexOf("maximize_") != -1)
									{
										$scope.$apply(function(){
											$scope.maximize = event.data.split("_")[1] == "true" ? true : false;
										});
									}
									else if(typeof event.data != "undefined" && event.data.indexOf("switchtomeeting") != -1 && event.data.split("switchtomeeting_")[1] == $scope.meeting.pid)
									{
										window.location.assign("<?= wp_nonce_url(str_replace("http://financialinsiders.ca", "https://financialinsiders.ca", site_url("/meeting/")),'finonce','finonce');?>&id="+$scope.meeting.mid+"&pid="+$scope.meeting.pid);
									}
									
								});
								}
							});
							
								
					};
					
					$scope.add = function(){
						console.log($scope.data);
						textchatref.push($scope.data);
						$scope.data.msg = '';
					};

					

					
			}]);

			<?php }?>
		</script>
		<?php

	}
}
