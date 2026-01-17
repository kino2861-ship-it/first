<?php $spot_id = $_GET['spot_id']; ?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>コメント投稿</title></head>
<body>
    <h1>スポットへのコメント</h1>
    <form action="comment_submit.php" method="post">
        名前：<input type="text" name="name" required><br>
        コメント：<textarea name="body" required></textarea><br>
        <input type="hidden" name="spot_id" value="<?php echo $spot_id; ?>">
        有効期限：
    <select name="limit_type">
        <option value="none">期限なし</option>
        <option value="1h">1時間だけ表示</option>
        <option value="24h">24時間表示</option>
    </select><br>
        <button type="submit">送信</button>
    </form>
</body>
</html>