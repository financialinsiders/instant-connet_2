<?php
require('autoload.php');

use OpenTok\OpenTok;
use OpenTok\Role;

define("API_KEY", "45426652");
define("API_SECRET", "ff71053e07be2f36ec4c1a6f1351fdc340285b81");

function opentok_token($sessionId = '')
{
	$apiObj = new OpenTok(API_KEY, API_SECRET);
	
	if(!$sessionId) {
		$session = $apiObj->createSession();
		$sessionId = $session->getSessionId();
	}
	 
	$token = $apiObj->generateToken($sessionId, array(
    'role'       => Role::MODERATOR,
    'expireTime' => time()+(7 * 24 * 60 * 60)));

	return array('sessionId' => $sessionId, 'token' => $token);
}

echo json_encode(opentok_token($_GET['sessionId']));