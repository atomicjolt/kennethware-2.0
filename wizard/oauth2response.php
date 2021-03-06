<?php
	// Display any php errors (for development purposes)
	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	session_start();
	require_once (__DIR__.'/../config.php');

	// get code query parameter from POST data
	$opts = array('http' => array( 'method'  => 'POST', ));
	$context  = stream_context_create($opts);
	$url = $_SESSION['canvasURL'].'/login/oauth2/token?client_id='.$client_id.'&client_secret='.$clientSecret.'&code='.$_GET['code'];
	// OPTION 1
	$userTokenJSON = file_get_contents($url, false, $context, -1, 40000); //ASK CANVAS,	USING DEVELOPER TOKEN, TO RETURN STUDENT TOKEN

	// OPTION 2
    // $runfile = $url;
    // $ch = curl_init();
    // curl_setopt($ch, CURLOPT_URL, $runfile);
    // curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    // $userTokenJSON = curl_exec ($ch);
    // curl_close ($ch);

	$userToken = json_decode($userTokenJSON);
  error_log('[oauth2response.php] $userToken: ' . $userToken->access_token);

	//encrypt token
	$encrypted_user_token = $cipher->encrypt($userToken->access_token);

	//store encrypted token in the database
	$userID=$_SESSION['userID'];

	$dbCall = $dbh->prepare("INSERT INTO tokens VALUES (DEFAULT,?,?,?)");
	$result = $dbCall->execute(array($userID, utf8_encode($encrypted_user_token), $_SESSION['apiDomain']));

  // Use when debugging datbase call
  // if(!$result){
  //   echo("Database call error: <br />");
  //   echo(serialize($dbCall->errorInfo()));
  //   echo("<br />");
  // }

	$_SESSION['allowed'] = true;
	/*  redirect to main tool page */
  header('Location: '.$_SESSION["template_wizard_url"].'/index.php');

?>
