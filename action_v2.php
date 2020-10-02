<script type="text/javascript" charset="utf-8">
var player;
var scope;
var myDataRef = new Firebase('https://vinogautam.firebaseio.com/pusher/new_user');
var meetingRef = new Firebase('https://vinogautam.firebaseio.com/pusher/new_meeting');
var online_status = new Firebase('https://vinogautam.firebaseio.com/pusher/online_status');
var refresh_user_list = new Firebase('https://vinogautam.firebaseio.com/pusher/refresh_user_list');

					
var allowtoleave = false;	
function onYouTubeIframeAPIReady() {
scope = angular.element($("body")).scope();
player = new YT.Player( 'youtube-player', {
  events: { 'onStateChange': onPlayerStateChange }
});
console.log(player);
}

function imageOnLoad(){
	$(".presentation_thumb_active").width($("#presentation_thumb").width());
}

$(window).resize(function(){
	imageOnLoad();
});

function onPlayerStateChange(event) {
	switch(event.data) {
	  case 0:
		console.log('video ended');
		break;
	  case 1:
		console.log('video playing from '+player.getCurrentTime());
		if(scope.is_admin)
			scope.send_noti({type:'video_start', vtime:player.getCurrentTime()});
		break;
	  case 2:
		console.log('video paused at '+player.getCurrentTime());
		if(scope.is_admin)
			scope.send_noti({type:'video_pause', vtime:player.getCurrentTime()});
	}
}
function setCookie(cname, cvalue, exdays) {
var d = new Date();
d.setTime(d.getTime() + (exdays*24*60*60*1000));
var expires = "expires="+d.toUTCString();
document.cookie = cname + "=" + cvalue + "; " + expires;
}

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

angular.module('instantconnect', ['opentok', 'opentok-whiteboard'])
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
		jQuery(".chat-mothed").scrollTop(jQuery(".chat-mothed")[0].scrollHeight);
    }, 1000);
}
}
}
})
.filter('to_trusted', ['$sce', function($sce){
	return function(text) {
		return $sce.trustAsHtml(text);
	};
}])
.filter('trustAsResourceUrl', ['$sce', function($sce) {
    return function(val) {
        return $sce.trustAsResourceUrl(val);
    };
}])
.controller('MyCtrl', ['$scope', 'OTSession', 'apiKey', 'sessionId', 'token', '$timeout', '$http', '$interval', '$filter', '$rootScope', function($scope, OTSession, apiKey, sessionId, token, $timeout, $http, $interval, $filter, $rootScope) {

	$scope.tabs = [];
	$scope.current_tab = -1;
	$scope.is_admin = <?= $pid == 0 ? 1 : 0;?>;
	$scope.preloader = true;
	$scope.isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
	$scope.fullwidthvideo = false;

	$(window).load(function(){
		$scope.$apply(function(){
			$scope.preloader = false;
		});
	});

	<?php if($pid == 0){

	global $wpdb; $results = $wpdb->get_row("select * from ".$wpdb->prefix . "meeting_participants where id=".$pid);?>

	<?php if($results->endorser && $results->gift_status == 0){?>
	setTimeout(function(){
		$http.get('<?= site_url();?>/wp-admin/admin-ajax.php?action=ic_add_meeting_points&id=<?= $pid;?>').then(function(res){

		});
	}, 300000);

	<?php }}?>

	$interval(function(){
		$http.get('<?= site_url();?>/wp-admin/admin-ajax.php?action=ic_update_active_time&id=<?= $meeting_id;?>').then(function(){

			});
	}, 10000);

	window.addEventListener("beforeunload", function (e) {
	 	//var confirmationMessage = "\o/";

		if(!$scope.is_admin)
			$scope.send_noti({type:'exitalluser', id:$scope.data2.id});

		//(e || window.event).returnValue = confirmationMessage; 
		//return confirmationMessage; 
	                             
	});

	$scope.check_chat_opened = function(){
		if($(".control-sidebar.control-sidebar-dark.control-sidebar-open").length == 0 && $scope.is_admin)
		$scope.send_noti({type:'close_chat'});
	};

	$scope.urlify = function(text) {
	    var urlRegex = /(https?:\/\/[^\s]+)/g;
	    return text.replace(urlRegex, function(url) {
	        return '<a target="_blank" href="' + url + '">' + url + '</a>';
	    });
	}

	$scope.add_tab = function(type, name, data, notify)
	{
		$scope.preloader = true;

		$scope.tabs[0] = {type:type, name:name, data:data};
		$scope.current_tab = 0;
		//$scope.tabs.push({type:type, name:name, data:data});
		//$scope.current_tab = $scope.tabs.length-1;

		$scope.initiatescripts();
		
		if(notify === undefined)
		$scope.send_noti({type:'add_tab', data:{type:type, name:name, data:data}});

		$timeout(function(){
			$scope.preloader = false;
		}, 1000);
	};

	$scope.tab_type_length = function(type, id)
	{
		var len = 0;
		angular.forEach($scope.tabs, function(v,k){
			if(v.type == type)
			{
				if(id === undefined)
				{
					len++;
				}
				else if(v.data.id == id)
				{
					len++;
				}
			}
		});
		return len;
	};

	$scope.short_text = function(txt, len){

        var tmp = document.createElement("DIV");
        tmp.innerHTML = txt;
        txt = tmp.textContent || tmp.innerText || "";

        if(txt === undefined) return;

        if(txt.length > len)
        {
            ind = txt.indexOf(" ", len);
            if(ind == -1 || ind - len > 10)
                return txt.substr(0, len+10)+'...';
            else
                return txt.substr(0, ind)+'...';
        }
        else
            return txt;
    };

	$scope.randomid = function()
	{
		return new Date().getTime()+''+(Math.floor(Math.random()*90000) + 10000);
	};

	$scope.initiatescripts = function()
	{
		$timeout(function(){
			$.AdminLTE.layout.fix();

			//White board pencil tool
	        if($('.pencil').length)
	        {
	        	$('.pencil').toolbar({
		              content: '#toolbar-options',
		              position: 'top',
		              adjustment: 28,
		              event: 'click',
		             hideOnClick: true,	
		              style: 'dark'

		        });

		        $('.pencil').on('toolbarItemClick',
		              function( event, buttonClicked ) {
		                  buttonClickedID = buttonClicked.id;
		                    console.log("BUTTON: " + buttonClickedID);
		                    switch (buttonClickedID) {
		                        case 'pencil-tool':
		                            $(".pencil-tool-fa").removeClass("fa-eraser");
		                            $(".pencil-tool-fa").addClass("fa-pencil");
		                            
		                            break;
		                        case 'eraser-tool':
		                             $(".pencil-tool-fa").removeClass("fa-pencil");
		                             $(".pencil-tool-fa").addClass("fa-eraser");
		                             
		                            break;
		                    }

		                    $("toolbar-options").addClass("hidden");
		                    $(".pencil").trigger("click");
		              }
		        );
	        }

	        if($(".range-slider").length)
	        {
	        	$(".range-slider img").click(function(){
		            $(".range").toggle();
		        });

		        $(".range-slider input").change(function(){
		            $(".range").toggle();
		        });
	        }
	        
	        if($(".tab-inner-div").length)
	        {
	        	console.log("Height: " + $(".meeting-pane").height());
	        	//$(".tab-inner-div").height($(".meeting-pane").height()-40);
	        }

		}, 100);
	};

	$scope.fiter_tabs = function(){
		var tab_data2 = angular.copy($scope.tabs);
		angular.forEach(tab_data2, function(v,k){
			if(v.type == 'presentation' || v.type == 'whiteboard')
			{
				delete v.slide_image;
			}
		});
		return tab_data2;
	};

	$scope.set_tab = function(id, notify)
	{
		
		if($scope.current_tab != -1 && ($scope.tabs[$scope.current_tab].type == 'presentation' || $scope.tabs[$scope.current_tab].type == 'whiteboard'))
		{
			if(notify === undefined)
				$scope.broadcast();
			$timeout(function(){
				$scope.current_tab = id;
				$scope.initiatescripts();
			},500);
		}
		else
		{
			$scope.current_tab = id;
			$scope.initiatescripts();
		}
		if(notify === undefined)
		$scope.send_noti({type:'set_tab', id:id});
	};

	$scope.remove_tab = function(id, notify)
	{
		if($scope.current_tab != -1 && ($scope.tabs[$scope.current_tab].type == 'presentation' || $scope.tabs[$scope.current_tab].type == 'whiteboard') && notify === undefined)
		{
			if(notify === undefined)
				$scope.broadcast();

			$timeout(function(){
				$scope.tabs.splice(id,1);
				$scope.current_tab = -1;
				$scope.initiatescripts();
			}, 500);
		}
		else
		{
			$scope.tabs.splice(id,1);
			$scope.current_tab = -1;
			$scope.initiatescripts();

		}
		if(notify === undefined)
		$scope.send_noti({type:'remove_tab', id:id});
		
	};

	$scope.parseInt = function(id)
	{
		return parseInt(id);
	};

	$scope.broadcast = function()
	{
		$rootScope.$broadcast('get_image_data', {ind:$scope.current_tab, tab:$scope.tabs[$scope.current_tab]});
	};

	$scope.whiteboard_control = $scope.is_admin ? true : false;
	$scope.active_whiteboard_user = {};

	$rootScope.$on('stopsharing', function(event, data){
		$scope.remove_tab(0);
	});

	$rootScope.$on('draw_status', function(event, data){
		if(!$scope.is_admin || $scope.data2.id == data.user.id) return;

		if(data.st == 'start'){
			$scope.whiteboard_control = false;
			$scope.active_whiteboard_user = data.user;
			//$.notify(data.user.name+" is drawing. You will wait to finish up", "info");
		}
		else{
			$scope.whiteboard_control = true;
			$scope.active_whiteboard_user = {};
			//$.notify(data.user.name+" draw end. You can draw now", "info");
		}
	});

	$rootScope.$on('Presentation_changed', function(event, data){
		if($scope.current_tab === -1) return;
		if($scope.tabs[$scope.current_tab].type == 'presentation')
        {    
        	if(!$scope.$$phase) {
        		$scope.$apply(function(){
	        		$scope.tabs[$scope.current_tab].slide_image[$scope.tabs[$scope.current_tab].currentpresentationindex] = data;
	        	});
        	}
        	else
        	{
        		$scope.tabs[$scope.current_tab].slide_image[$scope.tabs[$scope.current_tab].currentpresentationindex] = data;
        	}
        }
	});

	$rootScope.$on('Whiteboard_changed', function(event, data){
		if(data.tab.type == 'whiteboard')
        {
        	if(!$scope.$$phase) {
        		$scope.$apply(function(){
	        		$scope.tabs[data.ind].slide_image = data.image;
	        	});
        	}
        	else
        	{
        		$scope.tabs[data.ind].slide_image = data.image;
        	}
        }
	});

	

	<?php 
	$option = get_option('ic_presentations');
	$option = is_array($option) ? $option : []; 
	?>

	$scope.presentation_files = <?= json_encode($option)?>;
	
	<?php 
	$option = get_option('youtube_videos');
	$option = is_array($option) ? $option : []; 
	?>

	$scope.youtube_list = <?= json_encode($option)?>;

	$scope.currentPage = 0;
    $scope.vsearch = {name:''};
    $scope.psearch = {name:''};
    $scope.reset = function()
    {
    	$scope.currentPage = 0;
    	$scope.vsearch = {name:''};
    	$scope.psearch = {name:''};
    };
		
	$scope.addnew_video = function(){
		if($scope.newvideo.url.split("/embed/").length == 2)
            $scope.newvideo.url = "https://www.youtube.com/embed/"+$scope.newvideo.url.split("/embed/")[1];
        else if($scope.newvideo.url.split("?v=").length == 2)
            $scope.newvideo.url = "https://www.youtube.com/embed/"+$scope.newvideo.url.split("?v=")[1];
        else
            return;

		if($scope.newvideo)
		{
			$http.post('<?php echo site_url();?>/wp-admin/admin-ajax.php?action=addnew_video', $scope.newvideo).then(function(res){
				$scope.newvideo.id = $scope.randomid();
				$scope.youtube_list.push($scope.newvideo);
				$scope.newvideo = {};
			});

			$scope.reset();
		}
	};

	$scope.numberOfPages=function(arr, search){
        return Math.ceil($filter('filter')($scope[arr], $scope[search]).length/5);                
    };

    $scope.numberOfPagesArray=function(arr, search){
        return new Array($scope.numberOfPages(arr, search));                
    };

    $scope.connected = false;
	OTSession.init(apiKey, sessionId, token, function (err) {
		if (!err) {
			$scope.$apply(function () {
				$scope.connected = true;
			});
		}
	});
	console.log(OTSession);
	$scope.adminstreamm = OTSession.adminstream;
	$scope.userstreams = OTSession.userstreams;
	$scope.streams = OTSession.streams;
	$scope.screenshare = OTSession.screenshare;
	$scope.publisher = OTSession.publishers;

	$scope.initiate_screen_sharing = function(){
		if(!(($scope.is_admin && !$scope.user_have_admin_control()) || $scope.full_control))
			return;
		
		OTSession.initiate_screenshring();
	};

	$scope.trigger_draw_image = function()
	{
		if(!$scope.is_admin && !$scope.full_control)
			return;

		$timeout(function(){
			$(".presentation-thumbs ul li:eq("+$scope.parseInt($scope.tabs[$scope.current_tab].currentpresentationindex)+")").trigger("click");
		}, 500);
	};

	$scope.trigger_draw_whiteboard_image = function()
	{
		if(!$scope.is_admin && !$scope.full_control)
			return;

		$timeout(function(){
			$(".draw_whiteboard").trigger("click");
		}, 500);
	};

	$scope.thumb_position = function()
	{
		height = jQuery(".presentation-thumbs ul")[0].scrollHeight/$scope.tabs[$scope.current_tab].data.files.length;
		jQuery(".presentation-thumbs ul").scrollTop(height*$scope.parseInt($scope.tabs[$scope.current_tab].currentpresentationindex));
	};

    $scope.reset_value = function()
    {
    	if($scope.tabs[$scope.current_tab].slide_image[$scope.tabs[$scope.current_tab].currentpresentationindex] === undefined)
        {    
        	return [];
        }
        else
        {
        	return $scope.tabs[$scope.current_tab].slide_image[$scope.tabs[$scope.current_tab].currentpresentationindex];
        }
    };

	$scope.getvideobyID = function(url)
	{
		if(url.split("/embed/").length == 2)
            return url.split("/embed/")[1];
        else if(url.split("?v=").length == 2)
            return url.split("?v=")[1];
        else
            return;
	};

	$scope.deletevideo = function(e, ind){
		e.stopPropagation();
		$scope.youtube_list.splice(ind,1);
		$http.get('<?php echo site_url();?>/wp-admin/admin-ajax.php?action=delete_video&ind='+ind).then(function(){

		});
	};

	$scope.deletepresentation = function(e, ind){
		e.stopPropagation();
		$scope.presentation_files.splice(ind,1);
		$http.get('<?php echo site_url();?>/wp-admin/admin-ajax.php?action=delete_presentation&ind='+ind).then(function(){

		});
	};

	/*Chat section starts here*/
	$scope.user_have_control = function(){
		varr = false;
		angular.forEach($scope.userlist, function(v,k){
			if(v.presentation || v.whiteboard)
				varr = true;
		});
		return varr;
	};

	$scope.user_have_admin_control = function(){
		varr = false;
		angular.forEach($scope.userlist, function(v,k){
			if(v.presentation)
				varr = true;
		});
		return varr;
	};

	$scope.chat = [];

	var statusRef = new Firebase('https://vinogautam.firebaseio.com/opentok/<?= $sessionId?>');
	statusRef.on('child_added', function(snapshot) {
		v = snapshot.val();
		if(typeof v.msg != "undefined")
		{
			hn = v.email ? v.email : v.name;
			if(!$scope.$$phase) {
				$scope.$apply(function(){
					$scope.insert_chat_byid(v);
					$scope.visible = true;
				});
			}
			else
			{
				$scope.insert_chat_byid(v);
				$scope.visible = true;
			}
		}
	});

	$scope.insert_chat_byid = function(msg){

		var a = {id: "", time: "", msg:[]};
		if($scope.chat.length == 0)
		{
			$scope.chat.push({id: msg.id, time: msg.time, msg:[msg]});
		}
		else if($scope.chat[$scope.chat.length-1].id == msg.id)
		{
			$scope.chat[$scope.chat.length-1].id = msg.id;
			$scope.chat[$scope.chat.length-1].time = msg.time;
			$scope.chat[$scope.chat.length-1].msg.push(msg);
		}
		else
		{
			$scope.chat.push({id: msg.id, time: msg.time, msg:[msg]});
		}
	};
	
	<?php if(isset($participants) && $pid) {?>
		$id = <?php echo $pid;?>;
		$scope.data2 = {id:$id, name: $scope.is_admin ? 'Agent' : '<?= $participants->name ?>', email: '<?= $participants->email ?>', msg:'', streamid:'', whiteboard:0,presentation:0,chair:0,video:0};
	<?php } else {?>
		$id = Math.round(Math.random()*100000)+''+new Date().getTime();
		$scope.data2 = {id:$id, name: $scope.is_admin ? 'Agent' : 'user'+$id, email: 'user'+$id+'@gmail.com', msg:'', streamid:'', whiteboard:0,presentation:0,chair:0,video:0};
	<?php }?>
	

	$rootScope.user = {id:$scope.data2.id, name:$scope.data2.name};

	$scope.chair_value = 0;

	$scope.$on('otStreamCreated', function(newval, val){
		console.log("publisher"+$scope.publisher.length, $scope.publisher);
		$timeout(function(){
			console.log("publisher"+$scope.publisher.length, $scope.publisher[0].streamId);
		}, 500);
		$scope.data2.streamid = $scope.publisher[0].streamId;
		if(!$scope.is_admin)
			$scope.send_noti({type:'userstream', id:$scope.data2.id, streamid:$scope.data2.streamid});
		else
			$scope.send_noti({type:'adminstream', streamid:$scope.data2.streamid});
	});

	$scope.get_chair_value = function(){
		$scope.chair_value++;

		return angular.copy($scope.chair_value);
	};
	
	$scope.getuserlist = function(){
		var arr = [];

		angular.forEach($scope.userlist, function(v,k){
			arr.push(v);
		});

		return arr;
	};

	$scope.getstreamposition = function(id)
	{
		var pos = 0;
		angular.forEach($scope.userlist, function(v,k){
			if(v.streamid == id)
				pos = v.streamid;
		});

		return pos;
	};

	$timeout(function(){
		//$(".chat-mothed").height($(window).height()-200);
	}, 3000);
	$scope.add = function(){
		if(!$scope.data2.msg)
			return;
		$scope.data2.time = new Date().getTime();
		statusRef.push($scope.data2);
		$scope.data2.msg = '';
		
	};

	$scope.show_msg = function(msg, type){
		$.notify(msg, type);
	};

	$scope.send_noti = function(data)
	{
		data.time = new Date().getTime();
		OTSession.session.signal( 
		{  type: 'user-notifications',
		   data: data
		}, 
		function(error) {
			if (error) {
			  console.log("signal error ("+ error.code + "): " + error.message);
			} else {
			  console.log("signal sent.");
			}
		});
	};

	$scope.typinguser = {};
	$scope.userlist = {};
	
	//$scope.whiteboard_control = false;
	$scope.video_control = false;
	$scope.full_control = false;
	$scope.exit_user = -1;
	$scope.adminstream = '';

	OTSession.session.on({
	    sessionConnected: function() {
	    	if($scope.is_admin)
	    	{
	    		OTSession.session.signal( 
				{  	type: 'IAMAGENT',
					data:{}
				}, 
				function(error) {
					if (error) {
					  console.log("signal error ("+ error.code + "): " + error.message);
					} else {
					  console.log("signal sent.");
					}
				});
	    	}
	    	else
	    	{
	    		OTSession.session.signal( 
				{  
					type: 'IAMUSER',
				   	data: $scope.data2
				}, 
				function(error) {
					if (error) {
					  console.log("signal error ("+ error.code + "): " + error.message);
					} else {
					  console.log("signal sent.");
					}
				});
	    	}
	    }
   	});

	OTSession.session.on('signal:user-notifications', function (event) {
		if(event.data.type == 'usertyping')
		{
			$scope.$apply(function(){
				if(event.data.data.id != $scope.data2.id)
				{
					if($scope.typinguser[event.data.data.id] === undefined)
						$scope.typinguser[event.data.data.id] = {id:event.data.data.id, time:event.data.time, name:event.data.data.name};
					else
						$scope.typinguser[event.data.data.id].time = event.data.time;
				}
			});
			jQuery(".control-sidebar").addClass("control-sidebar-open");
			$timeout(function () {
		        jQuery(".chat-mothed").scrollTop(jQuery(".chat-mothed")[0].scrollHeight);
		    }, 1000);
		}
		else if(event.data.type == 'show_video')
		{
			$scope.$apply(function(){
				$scope.show_video = event.data.data;
			});
		}
		else if(event.data.type == 'userstream')
		{
			if(!$scope.is_admin)
				return;
			$scope.$apply(function(){
				$scope.userlist[event.data.id].streamid = event.data.streamid;
			});
		}
		else if(event.data.type == 'adminstream')
		{
			if($scope.is_admin)
				return;
			$scope.$apply(function(){
				$scope.adminstream = event.data.streamid;
				$rootScope.adminstream = event.data.streamid;
			});

			console.log('adminstream'+event.data.streamid);
		}
		else if(event.data.type == 'whiteboard_control')
		{
			if(event.data.data.id != $scope.data2.id)
				return;

			$scope.$apply(function(){
				$scope.whiteboard_control = event.data.data.val;
			});

			if(event.data.data.val)
				$.notify("Agent give whiteboard control to you", "success");
			else
				$.notify("Agent get back your whiteboard control", "info");
		}
		else if(event.data.type == 'video_control')
		{
			if(event.data.data.id != $scope.data2.id)
				return;

			$scope.$apply(function(){
				$scope.video_control = event.data.data.val;
			});

			if(event.data.data.val)
				$.notify("Agent enabled your video", "success");
			else
				$.notify("Agent disabled your video", "info");
		}
		else if(event.data.type == 'full_control')
		{
			if(event.data.data.id != $scope.data2.id)
				return;

			$scope.$apply(function(){
				$scope.full_control = event.data.data.val;
			});

			if(event.data.data.val)
				$.notify("Agent give full meeting room control to you", "success");
			else
				$.notify("Agent get back your full meeting room control", "info");
		}
		else if(event.data.type == 'exit_user')
		{
			if(event.data.data.id === 'all'){
				window.location.assign(event.data.data.val);

				//exit hook here
			}
			else if (event.data.data.id == $scope.data2.id){
				window.location.assign(event.data.data.val);

				//individual user hook here
			}
		}
		else if(event.data.type == 'set_tab')
		{
			if(($scope.is_admin && !$scope.user_have_admin_control()) || $scope.full_control)
				return;
			console.log(event.data);
			$scope.$apply(function(){
				$scope.set_tab(event.data.id, 1);

				if(typeof event.data.pid != "undefined")
					$scope.tabs[event.data.id].currentpresentationindex= event.data.pid;
			});
		}
		else if(event.data.type == 'remove_tab')
		{
			if(($scope.is_admin && !$scope.user_have_admin_control()) || $scope.full_control)
				return;

			$scope.$apply(function(){
				$scope.remove_tab(event.data.id, 1);
			});
		}
		else if(event.data.type == 'add_tab')
		{
			if(($scope.is_admin && !$scope.user_have_admin_control()) || $scope.full_control || (typeof event.data.from !== 'undefined' && event.data.from !== event.target.connection.id))
				return;
			console.log(event.data);
			$scope.$apply(function(){
				$scope.add_tab(event.data.data.type, event.data.data.name, event.data.data.data, 1);
			});
		}
		else if(event.data.type == 'currentpresentationindex')
		{
			if(($scope.is_admin && !$scope.user_have_admin_control()) || $scope.full_control)
				return;

			$scope.$apply(function(){
				$scope.tabs[event.data.current_tab].currentpresentationindex= event.data.ind;
			});
		}
		else if(event.data.type == 'video_start')
		{
			if($scope.is_admin)
				return;
			player.seekTo(event.data.vtime, true);
			player.playVideo();
		}
		else if(event.data.type == 'video_pause')
		{
			if($scope.is_admin)
				return;
			player.seekTo(event.data.vtime, true);
			player.pauseVideo();
		}
		else if(event.data.type == 'exitalluser')
		{
			if($scope.userlist[event.data.id] === undefined)
				return;

			delete $scope.userlist[event.data.id];
		}
		else if(event.data.type == 'lineWidth')
		{
			$scope.$apply(function(){
				$rootScope.$broadcast('lineWidthchange', event.data.val);
			});
		}
		else if(event.data.type == 'color')
		{
			$scope.$apply(function(){
				$rootScope.$broadcast('colorchange', event.data.val);
			});
		}
		else if(event.data.type == 'close_chat')
		{
			$(".control-sidebar-dark").removeClass("control-sidebar-open");
		}
		else if(event.data.type == 'fullwidthvideo')
		{
			if($scope.is_admin)
				return;
			$scope.fullwidthvideo = event.data.data;
		}
	});

	OTSession.session.on('signal:IAMAGENT', function (event) {
		if(!$scope.is_admin)
	    {
	    	window.location.reload();
	    }
	});

	OTSession.session.on('signal:IAMUSER', function (event) {
		if($scope.is_admin)
	    {
	    	$scope.userlist[event.data.id] = event.data;

	    	angular.forEach($scope.tabs, function(v,k){
	    		$timeout(function(){
	    			console.log("send", v);
	    			$scope.send_noti({type:'add_tab', data:v, from: event.from.id});

	    			if($scope.tabs.length-1 == k)
	    			$scope.send_noti({type:'set_tab', id:$scope.current_tab, pid:$scope.tabs[$scope.current_tab].currentpresentationindex, from: event.from.id});
	    		}, 100);
	    	});
	    	
	    	console.log("received and send ack");

	    }
	});

	$interval(function(){
			angular.forEach($scope.typinguser, function(v,k){
				console.log(v);
				if(new Date().getTime() - v.time > 3000)
					delete $scope.typinguser[k];
			});
	}, 3000);
	$(".chat-close-icon").click(function(){
		jQuery(".control-sidebar").removeClass("control-sidebar-open");
	});
	$scope.size = function(obj)
	{
		return Object.size(obj);
	};

	$scope.show_video = false;
	/*Chat end here*/


	$(document).on("change", "#convert_ppt", function(e) {
		handleFileSelect(e, true);
	});
	
	var formdata = !!window.FormData;

	function handleFileSelect(evt, manual) {
		evt.stopPropagation();
		evt.preventDefault();
		var files;
		files = evt.target.files;
		for (var i = 0, f; f = files[i]; i++) {
			if (f.type !== "") {
				var filename = f.name;
				var formData = formdata ? new FormData() : null;
				formData.append('File', files[i]);
				/*formData.append('OutputFormat', 'jpg');
				formData.append('StoreFile', 'true');
				formData.append('ApiKey', '938074523');
				formData.append('JpgQuality', 100);
				formData.append('AlternativeParser', 'false');*/

				file_convert_to_jpg(formData, filename);

			} else {
				progress_status(random_id, 0, "Invalid File Format...");
			}
		}

	}

	function file_convert_to_jpg(formData, filename) {
		$(".upload-preload .upload_percentage").text("0%");
		$(".upload-preload .progress-bar").width("0%");
		$(".upload-preload .file-name").text(filename);
		$(".upload-preload").removeClass("hide");

		$.ajax({
			url: "https://v2.convertapi.com/pptx/to/jpg?Secret=udmPipTGn5qkvI6O",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			success: function(response, textStatus, request) {

				$(".upload-preload .upload_percentage").text("50%");
				$(".upload-preload .progress-bar").width("50%");
				var ranid = $scope.randomid();
				$http.post("<?php echo site_url();?>/wp-admin/admin-ajax.php?action=save_ppt2&name="+filename, {id:ranid, data:response.Files}).then(function(data){
					if(data['data'] != 'error')
					{	
						new_data = data['data'];
						new_data.name = filename;
						new_data.id = ranid;
						$scope.presentation_files.push(new_data);
						$scope.add_tab('presentation', new_data.name, new_data);

						$(".upload-preload .upload_percentage").text("100%");
						$(".upload-preload .progress-bar").width("100%");
						
						$timeout(function(){
							$(".upload-preload").addClass("hide");
						}, 500);
					}
				});
			},
			error: function(jqXHR) {
				alert("Error in file conversion");
			}
		});

	}
}])
.value({
    apiKey: '<?= API_KEY;?>',
    sessionId: '<?= $sessionId?>',
    token: '<?= $token?>'
})
.directive('ngEnter', function () {
	return function (scope, element, attrs) {
		element.bind("keydown keypress", function (event) {
			if(event.which === 13) {
				scope.$apply(function (){
					scope.$eval(attrs.ngEnter);
				});
 
				event.preventDefault();
			}
		});
	};
})
.filter('startFrom', function() {
    return function(input, start) {
        start = +start; //parse to int
        return input.slice(start);
    }
})
.filter('unique', function() {
   return function(collection, keyname) {
      var output = [], 
          keys = [];

      angular.forEach(collection, function(item) {
          var key = item[keyname];
          if(keys.indexOf(key) === -1) {
              keys.push(key);
              output.push(item);
          }
      });

      return output;
   };
});

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};
</script>