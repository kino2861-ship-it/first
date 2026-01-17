<?php
function h($str) { return htmlspecialchars($str, ENT_QUOTES, "UTF-8"); }

if (isset($_POST["title"])) {
    $title = $_POST["title"];
    $nickname = $_POST["nickname"];
    $description = $_POST["description"];
    $lat = $_POST["latitude"];
    $lng = $_POST["longitude"];
    
    // 期間限定の処理
    $is_limited = $_POST["limit_type"];
    $expires_at = null;
    if ($is_limited == "1" && !empty($_POST["expires_at"])) {
        // datetime-local の形式を SQLite に合わせる
        $expires_at = str_replace("T", " ", $_POST["expires_at"]);
    }

    $pdo = new PDO("sqlite:osampo.sqlite");
    $sql = "INSERT INTO spots (title, nickname, description, latitude, longitude, is_limited, expires_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $st = $pdo->prepare($sql);
    $st->execute(array($title, $nickname, $description, $lat, $lng, $is_limited, $expires_at));
} else {
    $result = "データが正しく送信されませんでした。";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>登録結果</title>
</head>
<body>
    <h1>登録結果</h1>
    <p><?php echo h($result); ?></p>
    <a href="index.php">マップに戻る</a>
</body>
</html>