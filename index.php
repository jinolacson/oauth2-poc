<?php 
//http://localhost/oauth2-poc/authorize.php?response_type=code&client_id=testclient&state=xyz
// include our OAuth2 Server object
require_once __DIR__.'/server.php';

$request = OAuth2\Request::createFromGlobals();
$response = new OAuth2\Response();

session_start();

/**
 * Credentials
 * @var string
 */
$clientID = 'testclient';
$redirect_uri = 'http://fake/';
$clientSecret = 'testpass';
$tokenUrl = "http://localhost/oauth2-poc/token.php";

// validate the authorize request
if (!$server->validateAuthorizeRequest($request, $response)) {
    $response->send();
    die;
}
// display an authorization form
if (empty($_POST)) {
  
  unset($_SESSION['app_access_token']);
  unset($_SESSION['app_refresh_token']);
  //session_destroy();

  exit('
		<form method="post">
		  <label>Do you want to logged in APP2 ?</label><br />
		  <input type="submit" name="authorized" value="yes">
		  <input type="submit" name="authorized" value="no">
		</form>'
	);
}

// print the authorization code if the user has authorized your client
$is_authorized = ($_POST['authorized'] === 'yes');
$server->handleAuthorizeRequest($request, $response, $is_authorized);

if ($is_authorized) {
  // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
  $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=')+5, 40);

  $response = json_decode(generateRefreshToken($code));
  
  if($response && $response->access_token){
	 $_SESSION['app_access_token'] = $response->access_token;
	 $_SESSION['app_refresh_token'] = rand().$response->refresh_token;
	 header('Location: http://localhost/oauth2-poc/home.php');
  }
  exit();
}
$response->send();


function generateRefreshToken($code)
{
	global $clientID;
	global $redirect_uri;
	global $clientSecret;
	global $tokenUrl;

	$params = array(
		'code' => $code,
		'client_id' => $clientID,
		'client_secret' => $clientSecret,
		'redirect_uri' => $redirect_uri,
		'grant_type' => 'authorization_code'
	);

	$curlCommand = curl_init();

	curl_setopt($curlCommand, CURLOPT_URL, $tokenUrl);
	curl_setopt($curlCommand, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curlCommand, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curlCommand, CURLOPT_POSTFIELDS,$params);

	$response = curl_exec($curlCommand);

	return ($response);
}
?>