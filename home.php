<?php 
session_start();

if(!$_SESSION && !isset($_SESSION['app_access_token']) && !isset($_SESSION['app_refresh_token'])){
 header('Location: http://localhost/oauth2-poc/index.php?response_type=code&client_id=testclient&state=xyz');
}


if(isset($_GET['logout'])){
	session_destroy();
	header('Location: http://localhost/oauth2-poc/index.php?response_type=code&client_id=testclient&state=xyz');
}


echo "You have successfully logged in APP2 with these tokens <pre>" ;

var_dump($_SESSION);

echo "<br>";

echo "<a href='http://localhost/oauth2-poc/home.php?logout=true'>Logout</a>";


?>