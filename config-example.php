<?php
	// Display any php errors (for development purposes)
	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	/***************************/
	/* TEMPLATE WIZARD CONFIG  */
	/***************************/
	// The URL for where the "wizard" folder is located
	$user_id = explode(".", $_SERVER['HTTP_HOST'], 2)[0];
	$_SESSION['template_wizard_url'] = 'https://<path to wizard>/wizard';
	require_once __DIR__.'/wizard/resources/blti.php';
	require_once __DIR__.'/wizard/resources/cryptastic.php';
	require_once($_SERVER['DOCUMENT_ROOT'].'/kennethware-2.0/vendor/phpseclib/phpseclib/phpseclib/Crypt/AES.php');

	$database = pg_connect('host=127.0.0.1 dbname=oauth_lti_template_development connect_timeout=5');

	//Still need to get user_id from url

	// Strings to help encrypt/decrypt user OAuth tokens
	$result = pg_query_params($database, 'SELECT * FROM accounts WHERE id = $1', array($user_id)) or die('Error in query: '.pg_last_error());

	$account = pg_fetch_object($result, 0);

	pg_free_result($result);

	$salt = $account->salt;
	$pass = $account->pass;

	//Decrypt the canvas token in the account manager
	$encrypted_token = base64_decode($account->encrypted_canvas_token);
	$password = 'e506763de011f77cca0e46b387f52b3d89ae10bcaa69ba1fd9f8815ffbe05d8ecb343ffd6844c402ef644b2960765108b04201db199968d0c824d3d8c8a955d1';
	$salt = $account->encrypted_canvas_token_salt;
	$iv = base64_decode($account->encrypted_canvas_token_iv);

	$cipher = new Crypt_AES();
	$cipher->setKeyLength(256);

	$cipher->setPassword($password, "pbkdf2", "sha1", $salt, 2000, 256 / 8);
	$cipher->setIV($iv);

	// This OAuth token needs to make GET API calls for any course in your institution
	$apiToken = $cipher->decrypt($encrypted_token);

	// Your Canvas OAuth2 Developer information. Used for getting OAuth tokens from users
	$client_id = '#####';
	$clientSecret = '######';

	// The Shared Secret you use when setting up the Template Wizard LTI tool
	$lti_secret = $account->lti_secret;

	// Message to display if the OAuth token request fails
	$oauth_error_message = 'There is a problem, contact someone to fix it';

	// TEMPLATE ARRAY (templateName, minWidth,minHeight, ratioX,ratioY)
	// This array is for customizing banner images for template themes
	$templates = array (
		array('kl_fp_horizontal_nav_2', 1050,312, 215,64),
		array('kl_fp_panel_nav_2', 	1050,312,  215,64),
		array('kl_fp_squares_1x1', 320,320,  1,1),
		array('kl_fp_circles_1x1', 320,320,  1,1)
	);
	// RATIO ARRAY (ratioX, ratioY)
	$ratios = array (
		array (1,1),
		array(4,3),
		array(5,4),
		array(7,5),
		array(3,2),
		array(16,9)
	);

	/***************************/
	/* TOOLS API CONFIG  */
	/***************************/

	// These variables for the Content Tools to make API calls
	$canvasDomain = $account->canvas_uri; // 'https://<your domain>.instructure.com';
?>
