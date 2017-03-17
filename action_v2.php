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
	$scope.is_admin = <?= isset($_GET['admin']) ? 1 : 0;?>;

	window.addEventListener("beforeunload", function (e) {
	 	var confirmationMessage = "\o/";

		if(!$scope.is_admin)
			$scope.send_noti({type:'exitalluser', id:$scope.data2.id});

		(e || window.event).returnValue = confirmationMessage; 
		return confirmationMessage; 
	                             
	});

	$scope.add_tab = function(type, name, data)
	{
		$scope.tabs.push({type:type, name:name, data:data});
		$scope.current_tab = $scope.tabs.length-1;

		$scope.initiatescripts();

		$scope.send_noti({type:'tabs_data', tabs:$scope.tabs, current_tab:$scope.current_tab});
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
		              }
		        );
	        }
	        
	        if($(".range-slider").length)
	        {
	        	$(".range-slider img").click(function(){
		            $(".range").toggle();
		        });
	        }
	        
	        if($(".tab-inner-div").length)
	        {
	        	$(".tab-inner-div").height($(".meeting-pane").height()-40);
	        }

		}, 100);
	};

	$scope.set_tab = function(id)
	{
		if($scope.current_tab != -1 && ($scope.tabs[$scope.current_tab].type == 'presentation' || $scope.tabs[$scope.current_tab].type == 'whiteboard'))
		{
			$scope.broadcast();
			$timeout(function(){
				$scope.current_tab = id;
				$scope.initiatescripts();

				$scope.send_noti({type:'tabs_data', tabs:$scope.tabs, current_tab:$scope.current_tab});
			},500);
		}
		else
		{
			$scope.current_tab = id;
			$scope.initiatescripts();

			$scope.send_noti({type:'tabs_data', tabs:$scope.tabs, current_tab:$scope.current_tab});
		}
	};

	$scope.remove_tab = function(id)
	{
		if($scope.current_tab != -1 && ($scope.tabs[$scope.current_tab].type == 'presentation' || $scope.tabs[$scope.current_tab].type == 'whiteboard'))
		{
			$scope.broadcast();

			$timeout(function(){
				$scope.tabs.splice(id,1);
				$scope.current_tab = -1;
				$scope.initiatescripts();

				$scope.send_noti({type:'tabs_data', tabs:$scope.tabs, current_tab:$scope.current_tab});
			}, 500);
		}
		else
		{
			$scope.tabs.splice(id,1);
			$scope.current_tab = -1;
			$scope.initiatescripts();

			$scope.send_noti({type:'tabs_data', tabs:$scope.tabs, current_tab:$scope.current_tab});
		}

		
	};

	$scope.parseInt = function(id)
	{
		return parseInt(id);
	};

	$scope.broadcast = function()
	{
		$rootScope.$broadcast('get_image_data', {ind:$scope.current_tab, tab:$scope.tabs[$scope.current_tab]});
	};

	$rootScope.$on('Presentation_changed', function(event, data){
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
	$scope.streams = OTSession.streams;
	$scope.screenshare = OTSession.screenshare;
	$scope.publisher = OTSession.publishers;

	$scope.initiate_screen_sharing = function(){
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
	$id = Math.round(Math.random()*100000)+''+new Date().getTime();
	$scope.data2 = {id:$id, name: 'user'+$id, email: 'user'+$id+'@gmail.com', msg:'', streamid:'', whiteboard:0,presentation:0,chair:0,video:0};
	$scope.chair_value = 0;

	$scope.$on('otStreamCreated', function(newval, val){
		$scope.data2.streamid = $scope.publisher[0].streamId;
		$scope.send_noti({type:'userstream', id:$scope.data2.id, streamid:$scope.data2.streamid});
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
		$(".chat-mothed").height($(window).height()-200);
	}, 3000);
	$scope.add = function(){
		if(!$scope.data2.msg)
			return;
		$scope.data2.time = new Date().getTime();
		statusRef.push($scope.data2);
		$scope.data2.msg = '';
		
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
	
	$scope.whiteboard_control = false;
	$scope.video_control = false;
	$scope.full_control = false;
	$scope.exit_user = -1;

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
			$scope.$apply(function(){
				$scope.userlist[event.data.id].streamid = event.data.streamid;
			});
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
			if(event.data.data.id != $scope.data2.id)
				return;

			window.location.assign(event.data.data.val);
		}
		else if(event.data.type == 'tabs_data')
		{
			console.log(event.data);

			if(($scope.is_admin && !$scope.user_have_control()) || $scope.full_control)
				return;

			$scope.$apply(function(){
				$scope.tabs = event.data.tabs;
				$scope.current_tab = event.data.current_tab;

				$scope.initiatescripts();
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
	    	$scope.send_noti({type:'tabs_data', tabs:$scope.tabs, current_tab:$scope.current_tab});
	    }
	});

	$interval(function(){
			angular.forEach($scope.typinguser, function(v,k){
				console.log(v);
				if(new Date().getTime() - v.time > 3000)
					delete $scope.typinguser[k];
			});
	}, 3000);
	$(".close-icon").click(function(){
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
				formData.append('OutputFormat', 'jpg');
				formData.append('StoreFile', 'true');
				formData.append('ApiKey', '938074523');
				formData.append('JpgQuality', 100);
				formData.append('AlternativeParser', 'false');

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
			url: "https://do.convertapi.com/PowerPoint2Image",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			success: function(response, textStatus, request) {

				$(".upload-preload .upload_percentage").text("50%");
				$(".upload-preload .progress-bar").width("50%");

				$http.post("<?php echo site_url();?>/wp-admin/admin-ajax.php?action=save_ppt&name="+filename, {data:request.getResponseHeader('FileUrl')}).then(function(data){
					if(data['data'] != 'error')
					{	
						new_data = data['data'];
						new_data.name = filename;
						new_data.id = $scope.randomid();
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