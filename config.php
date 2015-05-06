<?php
  // Display any php errors (for development purposes)
  error_reporting(E_ALL);
  ini_set('display_errors', '1');

  /***************************/
  /* TEMPLATE WIZARD CONFIG  */
  /***************************/
  // The URL for where the "wizard" folder is located
  $code = explode(".", $_SERVER['HTTP_HOST'], 2)[0];

  $_SESSION['template_wizard_url'] = "https://" . $_SERVER['HTTP_HOST'] . '/wizard';
  
  require_once __DIR__.'/wizard/resources/blti.php';
  require_once __DIR__.'/wizard/resources/cryptastic.php';
  require_once($_SERVER['DOCUMENT_ROOT'].'/vendor/phpseclib/phpseclib/phpseclib/Crypt/AES.php');

  $dbType = getenv("DB_TYPE");
  $dbName = getenv("DB_NAME");
  $dbHost = getenv("DB_HOST");
  
  $dsn = "$dbType:dbname=$dbName;host=$dbHost";

  if($dbPort = getenv("DB_PORT")){
    $dsn = $dsn . ";port=$dbPort";
  }

  if($dbUser = getenv("DB_USER")){
    $dsn = $dsn . ";user=$dbUser";
  }

  if($dbPass = getenv("DB_PASS")){
    $dsn = $dsn . ";password=$dbPass";
  }

  $dbh = new PDO($dsn);

  $assets_server = getenv("ASSETS_SERVER");
  $main_css = $assets_server . "/" . $code . "/main.css";

  $result = $dbh->prepare('SELECT * FROM accounts WHERE code = ?');
  $result->execute(array($code));

  $account = $result->fetch(PDO::FETCH_OBJ);

  // Strings to help encrypt/decrypt user OAuth tokens
  $salt = $account->salt;
  $pass = $account->pass;

  // Your Canvas OAuth2 Developer information. Used for getting OAuth tokens from users
  $client_id = getenv("CLIENT_ID");
  $clientSecret = getenv("CLIENT_SECRET");

  // The Shared Secret you use when setting up the Template Wizard LTI tool
  $lti_secret = $account->lti_secret;
  
  // Message to display if the OAuth token request fails
  $oauth_error_message = 'There is a problem, contact someone to fix it';

  // TEMPLATE ARRAY (templateName, minWidth,minHeight, ratioX,ratioY)
  // This array is for customizing banner images for template themes
  $result = $dbh->prepare('SELECT * FROM front_page_themes WHERE account_id = ?');
  $result->execute(array($account->id));

  $templates = array();
  while($row = $result->fetch())
  {
    $templates[] = $row;
  }

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

  //Decrypt the canvas token in the account manager
  $encrypted_token = base64_decode($account->encrypted_canvas_token);
  $encryption_key = getenv("ENCRYPTION_KEY");
  $salt = $account->encrypted_canvas_token_salt;
  $iv = base64_decode($account->encrypted_canvas_token_iv);

  $cipher = new Crypt_AES();
  $cipher->setKeyLength(256);

  $cipher->setPassword($encryption_key, "pbkdf2", "sha1", $salt, 2000, 256 / 8);
  $cipher->setIV($iv);

  // This OAuth token needs to make GET API calls for any course in your institution
  $apiToken = $cipher->decrypt($encrypted_token);
?>