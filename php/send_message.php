<?php
session_start();

// メッセージが送信されているか確認
if (isset($_POST['message']) && isset($_SESSION['username'])) {
    $message = $_POST['message'];
    $username = $_SESSION['username'];

    // メッセージのデータを保存
    $messagesFile = '../messages.json';
    $messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];

    // 新しいメッセージを配列に追加
    $messages[] = [
        'username' => $username,
        'message' => $message,
    ];

    // メッセージをファイルに保存
    file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));

    // チャット画面にリダイレクト
    header('Location: ../index.php');
    exit;
}
?>
