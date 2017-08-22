<?php
include 'admin/ic-admin.php';
include 'admin/ic-mail_class.php';
include 'admin/ic-metabox.php';
include 'admin/ic-ajax.php';
include 'admin/ic-agent-api.php';
//include 'phpmailer/PHPMailerAutoload.php';

include 'frontend/ic-frontend.php';
$autoloader = 'vendor/autoload.php';
//if (!file_exists($autoloader)) {
  //die('You must run `composer install` in the sample app directory');
//}
require($autoloader);

use OpenTok\OpenTok;

function opentok_token()
{
	$apiObj = new OpenTok(API_KEY, API_SECRET);
	$session = $apiObj->createSession();
	$sessionId = $session->getSessionId(); 
	$token = $apiObj->generateToken($sessionId);
	
	return array('sessionId' => $sessionId, 'token' => $token);
}

function opentok_session_id() {
	$apiObj = new OpenTok(API_KEY, API_SECRET);	
	$session = $apiObj->createSession();
	$sessionId = $session->getSessionId();
	return $sessionId;
}

function opentok_generate_token($sessionID) {
	$apiObj = new OpenTok(API_KEY, API_SECRET);
	$token = $apiObj->generateToken($sessionID, array(
    	'role'       => Role::MODERATOR,
    	'expireTime' => time()+(7 * 24 * 60 * 60));
	return $token;
}
