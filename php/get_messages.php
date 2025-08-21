<?php
// messages.jsonからメッセージを取得
$messagesFile = '../messages.json';
if (file_exists($messagesFile)) {
    $messages = json_decode(file_get_contents($messagesFile), true) ?? [];

    // メッセージをHTML形式で返す
    foreach ($messages as $message) {
        echo "<p><strong>" . htmlspecialchars($message['username']) . ":</strong> " . htmlspecialchars($message['message']) . "</p>";
    }
}
?>
