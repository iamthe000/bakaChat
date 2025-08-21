<?php
session_start();

// CSRFトークン生成
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="manifest" href="manifest.json">
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js');
    }
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.png" id="favicon">
    <link rel="apple-touch-icon" sizes="192x192" href="favicon.png">
    <title>ログイン & チャット</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var messages = $('#messages');
            var firstLoad = true; // 初回ロード判定用

            // メッセージの更新処理
            function fetchMessages() {
                $.ajax({
                    url: 'php/get_messages.php',
                    type: 'GET',
                    success: function(data) {
                        var isScrolledToBottom = (messages[0].scrollHeight - messages.scrollTop()) === messages.outerHeight();

                        // メッセージを更新
                        messages.html(data);

                        // 初回ロード時のみ最下部にスクロール
                        if (firstLoad) {
                            messages.scrollTop(messages[0].scrollHeight);
                            firstLoad = false; // 初回ロード完了を記録
                        }
                    }
                });
            }

            // 初期メッセージの表示
            fetchMessages();

            // 2秒ごとにメッセージを更新
            setInterval(fetchMessages, 2000);
        });
    </script>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['username'])): ?>
            <div class="header">
                <h1>BAKAchat</h1>
                <p>ver.1.5</p>
                <h1>チャットルーム</h1>
            </div>
            <p><strong><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong>のアカウントでログイン中</p>
            
            <div id="chatbox">
                <!-- チャットメッセージ表示エリア -->
                <div id="messages"></div>

                <!-- メッセージ送信用フォーム -->
                <form action="php/send_message.php" method="POST" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="text" id="message" name="message" placeholder="メッセージを入力..." required maxlength="200">
                    <button type="submit">送信</button>
                </form>
            </div>

            <!-- ログアウトリンク -->
            <a href="logout.php">ログアウト</a>
        <?php else: ?>
            <!-- 未ログインの場合、登録フォームとログインフォームを表示 -->
            <h1>アカウント作成</h1>
            <form action="php/register.php" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <label for="username">ユーザーID</label>
                <input type="text" id="username" name="username" required maxlength="32" pattern="[a-zA-Z0-9_]+">
                
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required minlength="6" maxlength="64">

                <label for="site-password">サイトパスワード</label>
                <input type="password" id="site-password" name="site_password" required>
                
                <button type="submit">アカウント作成</button>
            </form>

            <hr>

            <h2>ログイン</h2>
            <form action="php/login.php" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <label for="login-username">ユーザーID</label>
                <input type="text" id="login-username" name="username" required maxlength="32" pattern="[a-zA-Z0-9_]+">
                
                <label for="login-password">パスワード</label>
                <input type="password" id="login-password" name="password" required minlength="6" maxlength="64">
                
                <button type="submit">ログイン</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
