<?php
session_start();

// サイトパスワード（固定値）
define('SITE_PASSWORD', 'sitepassword');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークン検証
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        echo htmlspecialchars("不正なリクエストです。", ENT_QUOTES, 'UTF-8');
        exit;
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $sitePassword = $_POST['site_password'];

    // サイトパスワードの検証
    if ($sitePassword !== SITE_PASSWORD) {
        echo htmlspecialchars("サイトパスワードが正しくありません。", ENT_QUOTES, 'UTF-8');
        exit;
    }

    // 入力値バリデーション
    if (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $username)) {
        echo htmlspecialchars("ユーザーIDは半角英数字とアンダースコア3～32文字で入力してください。", ENT_QUOTES, 'UTF-8');
        exit;
    }
    if (strlen($password) < 6 || strlen($password) > 64) {
        echo htmlspecialchars("パスワードは6～64文字で入力してください。", ENT_QUOTES, 'UTF-8');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ユーザー情報を保存するファイル
    $usersFile = '../users.json';
    $users = json_decode(file_get_contents($usersFile), true) ?? [];

    // ユーザーがすでに存在するか確認
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            echo htmlspecialchars("そのユーザーIDはすでに使われています。", ENT_QUOTES, 'UTF-8');
            exit;
        }
    }

    // 新しいユーザーを追加
    $users[] = [
        'username' => $username,
        'password' => $hashedPassword,
        'email' => '',  // 空のメールアドレス
        'birthday' => '' // 空の誕生日
    ];

    // ユーザー情報を保存
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // 登録完了後、index.phpにリダイレクト
    header('Location: ../index.php');
    exit;
}
?>
