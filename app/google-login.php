<?php
require_once 'vendor/autoload.php';

$clientId = $_ENV['GOOGLE_CLIENT_ID'];
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];

session_start();

$client = new Google_Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setRedirectUri('http://localhost:9090/google-callback.php');
$client->addScope('email');
$client->addScope('profile');
$client->addScope('https://www.googleapis.com/auth/youtube.readonly');
$client->addScope('https://www.googleapis.com/auth/userinfo.profile');
$client->addScope('https://www.googleapis.com/auth/userinfo.email');

$authUrl = $client->createAuthUrl();

header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();
