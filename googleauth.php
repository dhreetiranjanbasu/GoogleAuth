<?php
require_once 'vendor/autoload.php';

// Initialize Google Client
$client = new Google_Client();
$client->setApplicationName('Google Drive API - User Login');
$client->setAuthConfig('path-to-your-service-account-credentials.json'); // Service Account JSON file
$client->setRedirectUri('http://your-redirect-url'); // Adjust your redirect URL
$client->setAccessType('offline');
$client->setScopes([Google_Service_Drive::DRIVE_METADATA_READONLY, Google_Service_Drive::DRIVE_READONLY]);
$client->setPrompt('select_account consent');

// OAuth 2.0 Login: User logs in with their Google account
session_start();
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Store token in session
    $_SESSION['access_token'] = $token;
    header('Location: ' . filter_var('http://your-redirect-url', FILTER_SANITIZE_URL));
}

// If the token is stored in the session, set it
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);

    // Check if the token is expired and refresh if needed
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        $_SESSION['access_token'] = $client->getAccessToken();
    }

    // Initialize Google Drive Service
    $driveService = new Google_Service_Drive($client);

    // Define the file ID (Google Drive file ID you want to share)
    $fileId = 'your-google-drive-file-id';

    // Get the file metadata (for display)
    try {
        $file = $driveService->files->get($fileId, ['fields' => 'id, name, mimeType']);
        echo "File name: " . $file->name . "<br>";
        echo "MIME Type: " . $file->mimeType . "<br>";
        
        // Generate a download link for the user
        echo '<a href="https://drive.google.com/uc?id=' . $fileId . '&export=download">Download File</a>';

    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    
} else {
    // Redirect to Google OAuth 2.0 login page
    $authUrl = $client->createAuthUrl();
    echo "<a href='$authUrl'>Login with Google</a>";
}
