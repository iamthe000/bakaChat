<?php
session_start();

// サイトパスワード（固定値）
define('SITE_PASSWORD', 'sitepassword');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sitePassword = $_POST['site_password'];

    // サイトパスワードの検証
    if ($sitePassword !== SITE_PASSWORD) {
        echo "サイトパスワードが正しくありません。";
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ユーザー情報を保存するファイル
    $usersFile = '../users.json';
    $users = json_decode(file_get_contents($usersFile), true) ?? [];

    // ユーザーがすでに存在するか確認
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            echo "そのユーザーIDはすでに使われています。";
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
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

    // 登録完了後、index.phpにリダイレクト
    header('Location: ../index.php');
    exit;
}
?>
