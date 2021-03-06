<?php
	ob_start();
	// Display any php errors (for development purposes)
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	session_start();
	require_once __DIR__ . '/../config.php';

	// Check if safari
	// Check if not chrome, because chrome outputs Safari*
	// Check if no cookie/session is set
	if (strpos($_SERVER["HTTP_USER_AGENT"], "Safari")
	    && !strpos($_SERVER["HTTP_USER_AGENT"], "Chrome")) {
	    if (count($_COOKIE) === 0) {
	     echo '<script>
	     top.location = "' . $_SESSION['template_wizard_url'] . '/set_session.php";
	     </script>';
	     exit(); // need to be there in order not to load the rest of the page
	    }
	}

	//Check to see if the lti handshake passes
	$context = new BLTI($lti_secret, false, false);
	if ($context->valid) {
		$generateToken = false;
		// Setup variables from lti Post for later use
		$canvasUserID = $_POST["custom_canvas_user_id"];
		$_SESSION['userID'] = $canvasUserID;
		$_SESSION['userFullName'] = $_POST["lis_person_name_full"];
		$_SESSION['courseID'] = $_POST["custom_canvas_course_id"];
		$_SESSION['apiDomain'] = $_POST["custom_canvas_api_domain"];
		error_log('[controller.php] $_POST["custom_canvas_api_domain"]: ' . $_POST["custom_canvas_api_domain"]);
		error_log('[controller.php] $_SESSION["courseID"]: ' . $_SESSION['courseID']);
		error_log('[controller.php] $_SESSION["userID"]: ' . $_SESSION['userID']);
		error_log('[controller.php] $_SESSION["apiDomain"]: ' . $_SESSION['apiDomain']);
		error_log('[controller.php] SESSION: ' . print_r($_SESSION, true));

		$_SESSION['canvasURL'] = 'https://'.$_SESSION['apiDomain'];
		error_log('[controller.php] canvasURL: ' . $_SESSION['canvasURL']);

		$domain = $_SESSION['apiDomain'];
		error_log('[controller.php] $domain: ' . $domain);

		/* query DB to see if user has token, if yes, go to LTI*/
		$result = $dbh->prepare("SELECT canvas_user_id FROM tokens WHERE canvas_user_id = ? AND domain = ?");
		$result->execute(array($canvasUserID,$domain));

		$count_result = $dbh->prepare("SELECT count(canvas_user_id) FROM tokens WHERE canvas_user_id = ? AND domain = ?");
		$count_result->execute(array($canvasUserID,$domain));
		$number_of_rows = $count_result->fetchColumn();
		error_log('[controller.php] $number_of_rows: ' . $number_of_rows);

		if($number_of_rows >= 1)
		{
			$userCheck = true;
		}
		else {
			$userCheck = false;
		}

		if (!$userCheck){
			$generateToken = true;
		} else {
			$_SESSION['allowed'] = true;
			// Include API Calls
			require_once 'resources/wizardAPI.php';
			// test token
			$course = getCourse($_SESSION['courseID']);
			if (isset($course->errors[0]->message)){
				$result = $dbh->prepare("DELETE FROM tokens WHERE canvas_user_id = ? AND domain = ?");
				$result->execute(array($canvasUserID,$domain));
				$generateToken = true;
			}
			if (isset($course->name)){
				$generateToken = false;
			}
		}
		if ($generateToken == true) {
			echo 'Generate Token';
			// if not, redirect to canvas permission page
			header('Location: '.$_SESSION['canvasURL'].'/login/oauth2/auth?client_id='.$client_id.'&response_type=code&redirect_uri='. urlencode($_SESSION["template_wizard_url"] . '/oauth2response.php') );
		} else {
			header('Location: '.$_SESSION["template_wizard_url"].'/index.php');
		}
	} else {
		echo ($context->message."<br>");
		echo $oauth_error_message;
	}
?>
