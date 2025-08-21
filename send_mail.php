<?php
$to = "umekichi3hoshi@gmail.com";
$subject = "定期メール";
$message = "これは定期的に送信されるメールです。";
$headers = "From: your-email@example.com\r\n" .
           "Reply-To: your-email@example.com\r\n" .
           "Content-Type: text/plain; charset=UTF-8\r\n";

// メール送信
if (mail($to, $subject, $message, $headers)) {
    echo "メール送信成功";
} else {
    echo "メール送信失敗";
}
?>