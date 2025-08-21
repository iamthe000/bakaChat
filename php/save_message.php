<?php
// load_messages.php
session_start();

if (!isset($_SESSION['username'])) {
    echo "ログインしてください。";
    exit;
}

$username = $_SESSION['username'];
$messageFile = "messages/{$username}.json";

if (file_exists($messageFile)) {
    $messages = json_decode(file_get_contents($messageFile), true);
    echo json_encode($messages);
} else {
    echo "メッセージがありません。";
}
?>
