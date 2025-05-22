<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'database.php'; // your DB handler

$clientId = $_ENV['GOOGLE_CLIENT_ID'];
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];

$client = new Google\Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setRedirectUri('http://localhost:9090/google-callback.php');
$client->addScope([
    Google\Service\Oauth2::USERINFO_EMAIL,
    Google\Service\Oauth2::USERINFO_PROFILE
]);

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        die('Error fetching access token: ' . $token['error_description']);
    }

    $client->setAccessToken($token['access_token']);

    $oauth2 = new Google\Service\Oauth2($client);
    $userinfo = $oauth2->userinfo->get();

    $firstName = $userinfo->givenName;
    $lastName = $userinfo->familyName;
    $email = $userinfo->email;

    // DB check
    $db = new Database();
    $db->query("SELECT id, username FROM users WHERE email = :email");
    $db->bind(':email', $email);
    $existingUser = $db->single();

    if ($existingUser) {
        // User exists, redirect to dashboard
        $_SESSION['user_id'] = $existingUser['id'];
        $_SESSION['username'] = $existingUser['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        // New user → store Google data and redirect to signup
        $_SESSION['google_user'] = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email
        ];
        header('Location: signup.php');
        exit;
    }
} else {
    echo "Authorization code not found.";
}
?>