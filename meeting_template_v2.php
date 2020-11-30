<?php
$ip = $_SERVER['REMOTE_ADDR'];
$ipInfo = file_get_contents('http://ip-api.com/json/' . $ip);
$ipInfo = json_decode($ipInfo);
$timezone = $ipInfo->timezone;
date_default_timezone_set($timezone);

if(isset($_GET['only_video']))
{
  include 'only_video.php';
  exit;
}

if(isset($_GET['dev']))
{
  $ot = opentok_token();
  $sessionId = $ot['sessionId']; 
  $token = $ot['token'];
  $pid = 0;
}
elseif(isset($_GET['sessionId']))
{
  $sessionId = $_GET['sessionId']; 
  $token = $_GET['token'];
  $pid = 1;
}
else
{
  $decode=explode('#', base64_decode(base64_decode($_GET['id'])));
  $meeting_id = $decode[0];
  $pid = $decode[1];

  global $wpdb; $results = $meeting = $wpdb->get_row("select * from ".$wpdb->prefix . "meeting where id=".$meeting_id);

  if(($meeting->status == 1) || (strtotime('now') < strtotime($meeting->meeting_date) && count($decode)==2)){

    wp_redirect(get_permalink($meeting->bot_id).'?meetingId='.$meeting_id);
    exit;
  }

  $sessionId = $meeting->session_id; 
  $token = $meeting->token;

  if($pid != 0){
    $participants = $wpdb->get_row("select * from ".$wpdb->prefix . "meeting_participants where id=".$pid);
  }

  if (!is_numeric($meeting_id) || !is_numeric($pid)) {
    die("Invalid meeting url");
  }

  /*if (!isset($_GET['admin']) && (!isset($_GET['finonce']) || !wp_verify_nonce($_GET['finonce'], 'finonce'))) {
    die("Invalid meeting url");
  }*/
}
$blog_id = get_current_blog_id();
$agent_id = get_blog_option($blog_id, 'agent_id');

$profile_image = get_user_meta($agent_id, 'ic_agent_profile_image', true);
?>
<!DOCTYPE html>
<!--
Instant Connect UI
-->
<html ng-app="instantconnect">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Financial Insiders Meeting Room</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="<?= plugin_dir_url(__FILE__); ?>bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Toolbar styles -->
  <link rel="stylesheet" href="<?= plugin_dir_url(__FILE__); ?>dist/v2/css/jquery.toolbar.css">

  <!-- Theme style -->
  <link rel="stylesheet" href="<?= plugin_dir_url(__FILE__); ?>dist/v2/css/instantconnect.min.css">
  
  <link rel="stylesheet" href="<?= plugin_dir_url(__FILE__); ?>dist/v2/css/skins/skin-blue.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <link rel="stylesheet" href="<?= plugin_dir_url(__FILE__); ?>css/opentok-whiteboard.css" type="text/css" media="screen" charset="utf-8">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <style type="text/css">
    .meet-icon li{background: url(<?= plugin_dir_url(__FILE__); ?>dist/v2/img/meet-icons.jpg);}
    
    .admin_view .whiteboardtab, .admin_view .presentation-room {
        pointer-events: none;
    }
    .admin_view.whiteboard_control .whiteboardtab, .admin_view.whiteboard_control .presentation-room {
        pointer-events: auto;
    }
    .client_view.whiteboard_control ot-whiteboard, .client_view.whiteboard_control ot-whiteboard {
        pointer-events: auto;
    }
    ot-whiteboard{height: calc(100% - 80px);top: 0;bottom:0;margin:auto;cursor: pointer;}
    ot-whiteboard.whiteboard_thumb_active{width:auto !important;}
    /*ot-whiteboard.presentation_thumb_active{width: calc(100% - 17%);right: auto;}*/
    .client_view ot-whiteboard.presentation_thumb_active{width: 100%;}
    .tab-pane{position: relative;}
    .instant-connect .meeting-panel-container .meeting-panel .panel-header{z-index: 12;}
    .client_view .tab-inner-div {
        height: calc(100vh - 180px) !important;
    }
    .client_view.full_control .tab-inner-div, .client_view.whiteboard_control .tab-inner-div {
        height: calc(100vh - 180px) !important;
    }
    .client_view ot-whiteboard{height: 100%;}
    .client_view.full_control ot-whiteboard{height: calc(100% - 80px);bottom:0;}
    .client_view.full_control .tab-inner-div{margin-top:0;}
    .client_view.whiteboard_control ot-whiteboard{height: calc(100% - 40px);bottom:auto}
    .client_view.whiteboard_control .tab-inner-div{margin-top:0;}
    .presentation-thumbs{position: absolute;right: 0;z-index: 12;}
    .img_wh100{width: auto;height: auto;max-width: 100%;max-height: 100%;}
    .admin_view.user_have_admin_control ot-whiteboard{height: calc(100% - 40px);bottom: auto;}
    .pos-relative{position: relative;}
    .instant-connect .ICModalWindow .add-video-container{display: block;}
    /*.fullwidthvideo ot-layout{position: fixed;z-index:1000;margin-left: 0;}
    .fullwidthvideo .OT_publisher, .fullwidthvideo  .OT_subscriber {
        height: 100% !important;
        position: relative !important;
        width: 100% !important;
        left:0 !important;
      }
      .fullwidthvideo .video-container ot-layout .video2 .OT_publisher, .fullwidthvideo .video-container ot-layout .video2 .OT_subscriber {
        height: 50% !important;
        left: 0 !important;
          margin: 0 1% !important;
          position: relative !important;
          top: 0 !important;
          width: 45% !important;
      }*/
      .screenshare-container .OT_publisher, .screenshare-container .OT_subscriber{
        width: 100% !important;
        height: 100% !important;
        left: 0 !important;
      }
       .screenshare-container .screenshare-div{
            width: 300px;
            margin: 0 auto;
            margin-top: 100px;
            text-align: center;
        }
      .screenshare-container .screenshare-div .OT_publisher{
        width: 263px !important;
        height: 148px !important;
        overflow: hidden;
        border-radius: 10px;
        margin: 0 auto;
    }
    .screenshare-container h6{
      font-size: 24px;
    }
    .switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
.video-container-agent.video-container ot-publisher {
    width: 100% !important;
    height: 100% !important;
    border-radius: 10px;
}
.instant-connect .video-chat .video-agent{
  width: 100%;
    background: #000;
    height: 200px;
}
@media screen and (max-width: 600px) {
  .test_link{
    display: none !important;
  }
  body{
    overflow-y: auto !important;
    overflow-x: auto !important;
  }
}
  </style>


</head>

<body class="hold-transition skin-blue sidebar-collapse sidebar-mini instant-connect <?= $pid == 0 ? 'admin_view' : 'client_view'; ?>" ng-class="{whiteboard_control:whiteboard_control, video_control:video_control, full_control:full_control, user_have_control: user_have_control(), user_have_admin_control: user_have_admin_control()}" ng-controller="MyCtrl">
<div class="preloader" ng-if="preloader">
  <span><i class="fa fa-spinner fa-pulse fa-3x fa-fw margin-bottom"></i></span>
</div>
<div class="wrapper" ng-class="{opacity_0: preloader}">

  <!-- Main Header -->
  <header class="main-header">

    <!-- Logo -->
    <a href="index2.html" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><img src="<?= plugin_dir_url(__FILE__); ?>dist/v2/img/fi-logo-mini.png"/></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg">Meeting Room</span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
      <!-- Navbar Right Menu -->
      <div class="navbar-custom-menu" ng-show="is_admin">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
          <li ng-show="show_video && fullwidthvideo" ng-click="fullwidthvideo=false;send_noti({type:'fullwidthvideo', data:fullwidthvideo})"><a>Video Minimize</a></li>
          <li ng-show="show_video && !fullwidthvideo" ng-click="fullwidthvideo=true;send_noti({type:'fullwidthvideo', data:fullwidthvideo})"><a>Video Maximize</a></li>

          <li ng-show="show_video" ng-click="show_video=false;send_noti({type:'show_video', data:show_video})"><a>Disable video</a></li>
          <li ng-hide="show_video" ng-click="show_video=true;send_noti({type:'show_video', data:show_video})"><a>Enable video</a></li>
          <?php if(isset($_GET['dev'])){?>
            <li class="test_link"><a target="_blank" href="?user&sessionId=<?= $sessionId?>&token=<?= $token?>">Test user link</a></li>
          <?php }?>
          <li class="dropdown messages-menu">
            <!-- Menu toggle button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-users"></i>
              <span class="label label-success">{{size(userlist)}}</span>
            </a>
            <ul class="dropdown-menu user-list-dropdown-menu">
             <li class="header">User List</li>
              <li>
                <!-- inner menu: contains the messages -->
                <ul class="menu user-container">
                  <!-- start user item -->
                  <li ng-repeat="user in getuserlist() | orderBy:'-chair'" class="user-element">
                    <div class="user-icon"><i class="fa fa-user" aria-hidden="true"></i></div>
                      <div class="user-name">{{user.name}}</div>
                      <div class="user-controls">
                      <a class="btn user-control" ng-class="{active:user.whiteboard}" ng-click="userlist[user.id].whiteboard = !user.whiteboard;send_noti({type:'whiteboard_control', data:{id:user.id, val:userlist[user.id].whiteboard}});">
                        <i class="fa fa-edit"></i>
                      </a>
                      <a class="btn user-control" ng-class="{active:user.video}" ng-click="userlist[user.id].video = !user.video;send_noti({type:'video_control', data:{id:user.id, val:userlist[user.id].video}})">
                        <i class="fa fa-video-camera" aria-hidden="true"></i>
                      </a>

                      <a ng-show="!user_have_admin_control() || user.presentation" class="btn user-control" ng-class="{active:user.presentation}" ng-click="userlist[user.id].presentation = !user.presentation;send_noti({type:'full_control', data:{id:user.id, val:userlist[user.id].presentation}})">
                        <i class="fa fa-line-chart" aria-hidden="true"></i>
                      </a>

                      <a ng-hide="!user_have_admin_control() || (user_have_admin_control() && user.presentation)" ng-click="show_msg('Deselect already having user control and then try.', 'info');" class="btn user-control" >
                        <i class="fa fa-line-chart" aria-hidden="true"></i>
                      </a>

                      <a class="btn user-control" ng-class="{active:user.chair}" ng-click="userlist[user.id].chair = user.chair ? 0 : get_chair_value();">
                        <i class="fa fa-arrow-up" aria-hidden="true"></i>
                      </a>

                      <a class="btn user-control" ng-click="$parent.exit_user = user.id;" data-toggle="modal" data-target="#Exitmodal">
                        <i class="fa fa-times" aria-hidden="true"></i>
                      </a>
                    </div>
                  </li>
                  <!-- end user item -->
                </ul>
                <!-- /.menu -->
              </li>
              <li class="footer users-footer">Close <i class="fa fa-times" aria-hidden="true"></i></li>
            </ul>
          </li>
          <!-- /.messages-menu -->

                   
          <!-- Control Sidebar Toggle Button -->
          <li ng-click="check_chat_opened()">
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-comments"></i></a>
          </li>

          <li class="dropdown">
            <a><i class="fa fa-sign-out" ng-click="exit_user = 'all'" data-toggle="modal" data-target="#Exitmodal"></i></a>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar instant-connect-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

      <!-- Sidebar user panel (optional) -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="<?= $profile_image ? $profile_image : plugin_dir_url(__FILE__).'dist/v2/img/neil-avatar.jpg'; ?>" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?= $agent_name?></p>
          <!-- Status -->
          <a href="#"><i class="fa fa-circle text-success"></i>In meeting</a>

        </div>
      </div>

     

      <!-- Sidebar Menu -->
      <ul class="sidebar-menu" ng-show="is_admin || full_control">
        <li class="header">Meeting Room Controls</li>
        <!-- Optionally, you can add icons to the links -->
        <li><a href="#" data-toggle="modal" data-target="#presentationsModal"><i class="fa fa-line-chart" aria-hidden="true"></i> <span>Presentations</span></a></li>
        <li><a href="#" data-toggle="modal" data-target="#youtubeModal"><i class="fa fa-youtube-play"></i> <span>Videos</span></a></li>
        <li ng-click="add_tab('screenshare', 'Screen Share');"><a href="#"><i class="fa fa-television"></i> <span>Screen Share</span></a></li>
       
        <li><a href="#"><i class="fa fa-question-circle"></i> <span>Help</span></a></li>
      </ul>
      <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    

    <?php $agent_name = get_user_meta($agent_id, 'first_name', true).' '.get_user_meta($agent_id, 'last_name', true)?>
    <!-- Main content -->
    <section class="content">

      <!-- Start Video and group chat -->
     <div class="row">
     <div class="col-xs-12 col-md-3 col-sm-4 video-chat" ng-class="{fullwidthvideo: fullwidthvideo}">
        <div style="margin-top: 0;margin-bottom: 20px;" ng-if="!is_admin" class="video-container agent user-video-single-container">
            <div class="video-agent" >
              <img src="<?= plugin_dir_url(__FILE__); ?>dist/v2/img/agent-video-mock-up.jpg" class="img-responsive"/>
              <div ng-if="adminstreamm.length">
              <ot-layout props="{animate:true}">
                <ot-subscriber  stream="adminstreamm[0]" 
                  props="{style: {nameDisplayMode: 'on'}}">
                </ot-subscriber>
              </ot-layout>
              </div>
              <div class="agent-name"><?= $agent_name?></div>
            </div>
         
        </div>

        <div ng-if="is_admin" class="video-container agent video-container-agent">
            <div class="video-agent">
              <img src="<?= $profile_image ? $profile_image : plugin_dir_url(__FILE__); ?>dist/v2/img/agent-video-mock-up.jpg" class="img-responsive"/>
              <ot-layout ng-if="(is_admin && show_video) || video_control" props="{animate:true}">
                <ot-publisher id="publisher" 
                  props="{style: {nameDisplayMode: 'on'}, resolution: '640x480', frameRate: 30, name: data2.name}">
                </ot-publisher>
              </ot-layout>
              <div class="agent-name"><?= $agent_name?> <span class="designations">C.F.C</span></div>
            </div>
        </div> 

        <div ng-if="streams.length == 1 && is_admin" class="video-container agent user-video-single-container">
            <div class="video-agent">
              <ot-layout props="{animate:true}">
                <ot-subscriber ng-repeat="stream in streams" data-val="{{stream.streamId}}"
                  stream="stream" 
                  props="{style: {nameDisplayMode: 'on'}}">
                </ot-subscriber>
              </ot-layout>
              <div class="agent-name hide">User Name <span class="designations">C.F.C</span></div>
            </div>
         
        </div>
        <div ng-if="streams.length > 1  && is_admin" class="user-video-multiple-container" ng-class="{two_streams:streams.length == 2, more_than_two_streams:streams.length > 2}">
            <div ng-repeat="stream in streams" class="col-xs-6 video-container" data-pos="{{stream.vposition}}">
              <ot-layout props="{animate:true}">
                <ot-subscriber  
                  stream="stream" 
                  props="{style: {nameDisplayMode: 'on'}}">
                </ot-subscriber>
              </ot-layout>
            </div>
        </div>

        <div ng-if="(userstreams.length || video_control) && !is_admin" ng-class="{'video-container agent user-video-single-container': ((userstreams.length==0 && video_control) || (!video_control && userstreams.length==1)), 'user-video-multiple-container': !((userstreams.length==0 && video_control) || (!video_control && userstreams.length==1)), two_streams:userstreams.length == 2, more_than_two_streams:userstreams.length > 2}">
            <div ng-if="video_control" ng-class="{'video-agent': ((userstreams.length==0 && video_control) || (!video_control && userstreams.length==1)), 'col-xs-6 video-container': !((userstreams.length==0 && video_control) || (!video_control && userstreams.length==1))}" data-pos="{{stream.vposition}}">
              <ot-layout  props="{animate:true}">
                <ot-publisher id="publisher" 
                  props="{style: {nameDisplayMode: 'on'}, resolution: '640x480', frameRate: 30, name: data2.name}">
                </ot-publisher>
              </ot-layout>
            </div>
            <div ng-repeat="stream in userstreams" ng-class="{'video-agent': ((userstreams.length==0 && video_control) || (!video_control && userstreams.length==1)), 'col-xs-6 video-container': !((userstreams.length==0 && video_control) || (!video_control && userstreams.length==1))}" data-pos="{{stream.vposition}}">
              <img ng-if="((userstreams.length==0 && video_control) || (!video_control && userstreams.length==1))" src="<?= plugin_dir_url(__FILE__); ?>dist/v2/img/agent-video-mock-up.jpg" class="img-responsive"/>
              <ot-layout props="{animate:true}">
                <ot-subscriber  
                  stream="stream" 
                  props="{style: {nameDisplayMode: 'on'}}">
                </ot-subscriber>
              </ot-layout>
            </div>
        </div>

        <div class="client-videos-container hide">
           
              
              <!--CLIENT VIDEO -->
              <div class="col-xs-4 video-container">
                  <div class="video">
                  </div>
              </div>
              <!-- END CLIENT VIDEO -->

              <!-- CLIENT VIDEO -->
              <div class="col-xs-4 video-container">
                  <div class="video">
                  </div>
              </div>
              <!-- END CLIENT VIDEO -->


              <!-- CLIENT VIDEO -->
              <div class="col-xs-4 video-container">
                  <div class="video">
                  </div>
              </div>
              <!-- END CLIENT VIDEO -->

              <!-- CLIENT VIDEO -->
              <div class="col-xs-4 video-container">
                  <div class="video">
                  </div>
              </div>
              <!-- END CLIENT VIDEO -->



              
          
        </div>
      <!-- Group Chat -- >
       <!-- Construct the box with style you want. Here we are using box-danger -->
<!-- Then add the class direct-chat and choose the direct-chat-* contexual class -->
<!-- The contextual class should match the box, so we are using direct-chat-danger -->



      </div>
<!-- End Video and Group Chat -->

<!--  MEETING ROOM WINDOWS -->
<div class="col-xs-12 col-sm-9 meeting-panel-container" ng-init="tabindex=0">
    <div class="meeting-panel row">
        
        <div class="col-xs-12 panel-header hide no-pad" ng-show="(is_admin && !user_have_admin_control()) || full_control">
        <div ng-click="set_tab(-1);" class="home-label"><i class="fa fa-home" aria-hidden="true"></i>
 Start</div>
        <ul>
            <li class="tab-{{tab.type}}" ng-repeat="tab in tabs track by $index" ng-class="{active:current_tab == $index}" ng-show="$index >= tabindex && $index <= tabindex+4"><a ng-click="set_tab($index);">{{short_text(tab.name, 10)}} <span ng-click="$event.stopPropagation();remove_tab($index);" class="close-window">&times;</span></a></li>
        </ul>
        <span ng-if="tabs.length > 4" class="tabnavigation">
          <i ng-show="tabindex" ng-click="$parent.tabindex = $parent.tabindex-1" class="fa fa-arrow-left"></i>
          <i ng-show="tabindex < tabs.length-5" ng-click="$parent.tabindex = $parent.tabindex+1" class="fa fa-arrow-right"></i>
        </span>
        </div>
 

    <div class="tab-content clearfix pos-relative">
      <ot-whiteboard ng-hide="current_tab == -1 || tabs[current_tab].type == 'youtube' || tabs[current_tab].type == 'screenshare'" ng-class="{presentation_thumb_active: current_tab != -1 && tabs[current_tab].type == 'presentation', whiteboard_thumb_active: current_tab != -1 && tabs[current_tab].type == 'whiteboard'}" width="700" height="420" ></ot-whiteboard>

      <div class="tab-pane active clearfix" ng-if="current_tab == -1" ng-show="(is_admin && !user_have_admin_control()) || full_control">
        <div class="col-xs-12 no-pad meeting-pane">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <ul class="meet-icon">
                <li class="presen-img"><a href="#" data-toggle="modal" data-target="#presentationsModal"></a></li>
                <li class="screen-share">
                  <a ng-hide="tab_type_length('screenshare');" ng-click="add_tab('screenshare', 'Screen Share');" href="#"></a>
                  <a ng-click="show_msg('Screenshare already opened', 'info');" ng-show="tab_type_length('screenshare');" href="#"></a>
                </li>
                <li class="whith-board">
                  <a ng-hide="tab_type_length('whiteboard');" ng-click="add_tab('whiteboard', 'WhiteBoard');" href="#"></a>
                  <a ng-click="show_msg('WhiteBoard already opened', 'info');"  ng-show="tab_type_length('whiteboard');" href="#"></a>
                </li>
                <li class="youtube"><a href="#" data-toggle="modal" data-target="#youtubeModal"></a></li>
              </ul>
            </div>
        </div>
      </div>
      <div class="tab-pane clearfix" ng-repeat="tab in tabs track by $index" ng-if="current_tab != -1 && current_tab == $index" ng-class="{active:current_tab == $index}" ng-init="tab.index = $index">
         <?php include 'elements/tab_content.php';?>
      </div>

    </div>

    </div>
   </div>



<!-- END MEETING ROOM WINDOWS -->




    </section>
    <!-- /.content -->
  </div>
  <!-- END MEETING ROOM -->
    <!-- /.content-wrapper -->
 <footer class="main-footer">
    
    <strong>Powered by Agent Online</strong>
  </footer>
  </div>





  <!-- CHAT STARTS HERE PULL SNIPPET HERE-->
  
<?php include 'elements/chat_section.php';?>

  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->

  <div class="control-sidebar-bg"></div>
</div>
<!-- CHAT ENDS HERE PULL SNIPPET HERE- -->


<!-- ./wrapper -->
<!--MODAL WINDOWS -->

<div id="Exitmodal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Select a page to redirect user</h4>
      </div>
      <div class="modal-body">
        <select ng-model="selected_page"  ng-init="selected_page=''">
      <option value="">Select Page</option>
      <?php foreach (get_pages() as $key => $value) {?>
      <option value="<?= get_permalink($value->ID);?>"><?= $value->post_title;?></option>
      <?php }?>
    </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" ng-click="send_noti({type:'exit_user', data:{id:exit_user, val:selected_page}});" data-dismiss="modal">Ok</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<?php include 'elements/video_modal.php';?>
<?php include 'elements/presentation_modal.php';?>
<!-- REQUIRED JS SCRIPTS -->

<script type="text/javascript">
  var extensionId = 'pfobnffdhkcjnpmcgjmfdgbgcpaijbpd';
  var ffWhitelistVersion;
</script>
<!-- jQuery 2.2.3 -->
<script src="<?= plugin_dir_url(__FILE__); ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="<?= plugin_dir_url(__FILE__); ?>js/notify.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular.min.js" type="text/javascript" charset="utf-8"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<!-- TOOL TIP -->
<script src="<?= plugin_dir_url(__FILE__); ?>dist/v2/js/jquery.toolbar.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="<?= plugin_dir_url(__FILE__); ?>/bootstrap/js/bootstrap.min.js"></script>
<!-- AdminLTE App -->
<script src="<?= plugin_dir_url(__FILE__); ?>dist/v2/js/app.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/paper.js/0.9.25/paper-core.min.js" type="text/javascript" charset="utf-8"></script>
<script src="//static.opentok.com/v2/js/opentok.min.js" type="text/javascript" charset="utf-8"></script>
<script src="<?= plugin_dir_url(__FILE__); ?>js/opentok-layout.js" type="text/javascript" charset="utf-8"></script>
<script src="<?= plugin_dir_url(__FILE__); ?>js/opentok-angular.js" type="text/javascript" charset="utf-8"></script>
<script src="<?= plugin_dir_url(__FILE__); ?>js/opentok-whiteboard.js" type="text/javascript" charset="utf-8"></script>

<script src='https://cdn.firebase.com/js/client/2.2.1/firebase.js'></script>

<?php include 'action_v2.php';?>
<!-- Optionally, you can add Slimscroll and FastClick plugins.
     Both of these plugins are recommended to enhance the
     user experience. Slimscroll is required when using the
     fixed layout. -->
</body>
</html>
