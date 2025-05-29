<?php
require_once 'vendor/autoload.php';

// Init session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Google client setup
$clientId = $_ENV['GOOGLE_CLIENT_ID'];
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
$client = new Google_Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setRedirectUri('http://localhost:9090/google-callback.php');
$client->addScope([
    Google\Service\Oauth2::USERINFO_EMAIL,
    Google\Service\Oauth2::USERINFO_PROFILE
]);
$client->setAccessType('offline');
$client->setPrompt('consent');

// Debug output (remove in production)
error_log('Session data: ' . print_r($_SESSION, true));

// Token handling
if (isset($_SESSION['access_token'])) {
    $client->setAccessToken($_SESSION['access_token']);
    
    // Check if token is expired
    if ($client->isAccessTokenExpired()) {
        try {
            if (!empty($_SESSION['refresh_token'])) {
                // Refresh the token using the stored refresh token
                $newToken = $client->fetchAccessTokenWithRefreshToken($_SESSION['refresh_token']);
                $_SESSION['access_token'] = $newToken;
                
                // Update the refresh token if a new one was provided
                if (isset($newToken['refresh_token'])) {
                    $_SESSION['refresh_token'] = $newToken['refresh_token'];
                }
                
                $client->setAccessToken($_SESSION['access_token']);
            } else {
                // No refresh token available, need to re-authenticate
                unset($_SESSION['access_token']);
                $_SESSION['redirect'] = 'dashboard.php';
                header('Location: login.php');
                exit;
            }
        } catch (Exception $e) {
            // Handle token refresh error
            error_log('Token refresh error: ' . $e->getMessage());
            unset($_SESSION['access_token'], $_SESSION['refresh_token']);
            $_SESSION['redirect'] = 'dashboard.php';
            header('Location: login.php');
            exit;
        }
    }
} else {
    // No access token available, redirect to login
    $_SESSION['redirect'] = 'dashboard.php';
    header('Location: login.php');
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_email'])) {
    $_SESSION['redirect'] = 'dashboard.php';
    header('Location: login.php');
    exit;
}

$accessToken = $_SESSION['access_token']['access_token'];
$searchResults = [];
$error = '';
$selectedVideoId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['query'])) {
        // Handle search request
        if ($client->isAccessTokenExpired()) {
            if (!empty($_SESSION['refresh_token'])) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($_SESSION['refresh_token']);
                $_SESSION['access_token'] = $newToken;
                $accessToken = $newToken['access_token'];
            } else {
                $error = "Session expired. Please login again.";
                unset($_SESSION['access_token']);
                $_SESSION['redirect'] = 'search.php';
                header('Location: login.php');
                exit;
            }
        }
        
        $query = trim($_POST['query']);
        $maxResults = 5;

        $endpoint = "https://www.googleapis.com/youtube/v3/search";
        $params = http_build_query([
            'part' => 'snippet',
            'q' => $query,
            'maxResults' => $maxResults,
            'type' => 'video',
        ]);

        $url = $endpoint . "?" . $params;

        // Prepare curl request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = "Request error: " . curl_error($ch);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                $error = "API error: " . ($errorData['error']['message'] ?? "Unknown error");
            } else {
                $data = json_decode($response, true);
                $searchResults = $data['items'] ?? [];
            }
        }

        curl_close($ch);
    } elseif (isset($_POST['video_id'])) {
        // Handle video selection
        $selectedVideoId = $_POST['video_id'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- Rest of your HTML remains the same -->
<head>
    <meta charset="UTF-8" />
    <title>YouTube Search with OAuth Token (PHP)</title>
    <style>
        .video-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .video-card {
            width: 200px;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 5px;
            padding: 10px;
            transition: all 0.3s;
        }
        .video-card:hover {
            border-color: #ccc;
            background-color: #f5f5f5;
        }
        .video-card.selected {
            border-color: #4285f4;
            background-color: #e8f0fe;
        }
        .video-thumbnail {
            width: 100%;
            height: auto;
        }
        .video-title {
            margin-top: 8px;
            font-size: 14px;
            word-break: break-word;
        }
        #selected-video {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        .hidden {
            display: none;
        }
        form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <h1>YouTube Video Search</h1>
    <form method="POST" action="">
        <input type="text" name="query" placeholder="Search for videos" required
            value="<?= isset($_POST['query']) ? htmlspecialchars($_POST['query']) : '' ?>" />
        <button type="submit">Search</button>
    </form>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($searchResults)): ?>
        <form id="video-select-form" method="POST" action="">
            <input type="hidden" name="video_id" id="selected-video-input">
            <div class="video-container">
                <?php foreach ($searchResults as $video): 
                    $videoId = $video['id']['videoId'];
                    $title = $video['snippet']['title'];
                    $image = $video['snippet']['thumbnails']['medium'];
                    $isSelected = ($selectedVideoId === $videoId);
                ?>
                    <div class="video-card <?= $isSelected ? 'selected' : '' ?>" 
                         data-video-id="<?= htmlspecialchars($videoId) ?>"
                         onclick="selectVideo(this)">
                        <img src="<?= htmlspecialchars($image['url']) ?>" class="video-thumbnail" alt="Thumbnail">
                        <div class="video-title"><?= htmlspecialchars($title) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['video_id'])): ?>
        <p>No videos found.</p>
    <?php endif; ?>

    <?php if (!empty($selectedVideoId)): ?>
        <div id="selected-video">
            <h3>Selected Video</h3>
            <p>Video ID: <?= htmlspecialchars($selectedVideoId) ?></p>
            <p>You can now use this video ID for your application.</p>
        </div>
    <?php endif; ?>

    <script>
        function selectVideo(element) {
            // Remove selected class from all cards
            document.querySelectorAll('.video-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            element.classList.add('selected');
            
            // Set the video ID in the hidden input
            const videoId = element.getAttribute('data-video-id');
            document.getElementById('selected-video-input').value = videoId;
            
            // Submit the form
            document.getElementById('video-select-form').submit();
        }
    </script>
</body>
</html>