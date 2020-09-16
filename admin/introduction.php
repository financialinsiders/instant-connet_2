<?php
include 'wp-config.php';

global $wpdb;

$res = $wpdb->get_row('select * from wp_short_link where id='.$_GET['id']);

$link = $res->link ? ($res->link.'?') : 'https://financialinsiders.ca/?';

$data = unserialize($res->params);

//print_r($data);exit;

if(isset($data['agent_id']) && $data['agent_id']) {		
			$siteID = get_active_blog_for_user( $data['agent_id'] )->blog_id;
			switch_to_blog( $siteID );
		}
		
if(isset($data['bot_id']) && $data['bot_id']){
			$link = get_permalink($data['bot_id']).'?';
		} elseif(isset($data['page_id']) && $data['page_id']){
			$link = get_permalink($data['page_id']).'?';
		}
		$params = [];
		foreach($data as $k=>$v){
			$params[] = $k.'='.urlencode($v);
		}


if(isset($data['endorser_id'])){
$params[] = 'ref='.base64_encode(base64_encode($data['endorser_id']));
}

$params = implode('&', $params);

//$link .= $params;

if(isset($data['bot_id']) && (isset($data['videoURL']) || isset($data['messagetxt']))){
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title><?= get_the_title($data['bot_id'])?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script>
$(document).ready(function(){
$('#myModal').modal('show');
});
</script>
</head>
<body>

<div class="container">

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog" style="width:900px;">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-body redeem-body" id="modal-body">
        <div ng-if="info.introType == 'vid_txt' || 'vid_notxt' || 'novid_txt' ">
          <div class="row">            
            <div class="col-md-6">
              <div ng-if="info.introType != 'novid_txt' ">
                <div class="video-wrapper">
                  <video style="width:100%" id="introVideo" class="video-js vjs-default-skin vjs-big-play-centered" controls preload="auto" muted data-setup='{"fluid": true}'>
                    <source src="<?= $data['videoURL'];?>" type='video/mp4'>
                  </video>
                </div>
              </div>
            </div>

            <div  class="col-md-6" ng-class="{'novid_txt_div': info.introType == 'novid_txt'}">
              <div class="testimonial__info-container" ng-class="{'novid_txt_padding': info.introType == 'novid_txt'}">
                <h2 class="section-title">Thank You Endorser name</h2>
                  <p><?= $data['messagetxt'];?></p>
                  <span class="testimonial__company">Financial Insiders.</span> <br><br>
                  <a href="<?= $link.'detailInfo='.$_GET['id'];?>" class='btn btn-primary btn-rounded intro-start-btn pull-left'>Start now</a>
              </div>
            </div>
          </div>
        </div>
    </div>
      </div>
      
    </div>
  </div>
  
</div>

</body>
</html>
<?php
}elseif(isset($_GET['meta'])){
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
} else {//print_r($link);exit;
	//echo $link;
	wp_redirect($link);
}
exit;