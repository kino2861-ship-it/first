<?php
// エラーを表示する設定を追加
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_GET["spot_id"]) && isset($_GET["type"])) {
    $spot_id = $_GET["spot_id"];
    $type = $_GET["type"];

    try {
        $pdo = new PDO("sqlite:osampo.sqlite"); // ファイル名に注意！
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 以下、前回のロジック ...
        $st = $pdo->prepare("SELECT count FROM reactions WHERE spot_id = ? AND reaction_type = ?");
        $st->execute([$spot_id, $type]);
        $row = $st->fetch();

        if ($row) {
            $st = $pdo->prepare("UPDATE reactions SET count = count + 1 WHERE spot_id = ? AND reaction_type = ?");
            $st->execute([$spot_id, $type]);
        } else {
            $st = $pdo->prepare("INSERT INTO reactions (spot_id, reaction_type, count) VALUES (?, ?, 1)");
            $st->execute([$spot_id, $type]);
        }
        
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        // エラーがあれば画面に出す
        die("エラーが発生しました: " . $e->getMessage());
    }
}