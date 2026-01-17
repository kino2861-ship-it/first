<?php
date_default_timezone_set('Asia/Tokyo'); // 日本時間に設定

if (isset($_POST["spot_id"])) {
    $expires_at = null;
    
    // 期限の計算
    if ($_POST["limit_type"] == "1h") {
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));
    } elseif ($_POST["limit_type"] == "24h") {
        $expires_at = date("Y-m-d H:i:s", strtotime("+24 hours"));
    }

    $pdo = new PDO("sqlite:osampo.sqlite");
    $st = $pdo->prepare("INSERT INTO comments (spot_id, name, body, expires_at) VALUES (?, ?, ?, ?)");
    $st->execute([$_POST["spot_id"], $_POST["name"], $_POST["body"], $expires_at]);
    
    header("Location: index.php");
    exit;
}