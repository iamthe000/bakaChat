<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ユーザー情報を読み込む
    $usersFile = '../users.json';
    $users = json_decode(file_get_contents($usersFile), true) ?? [];

    // ユーザー情報をチェック
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;  // セッションにユーザー名を保存
            header('Location: ../index.php');    // ログイン後にリダイレクト
            exit;
        }
    }

    echo "ユーザーIDまたはパスワードが間違っています。";
}
?>
