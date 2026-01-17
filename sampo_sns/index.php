<?php
$pdo = new PDO("sqlite:osampo.sqlite");

//通常スポット、または「期限が現在時刻より未来」のスポットだけを取得
$sql = "SELECT * FROM spots 
        WHERE is_limited = 0 
        OR (is_limited = 1 AND expires_at > datetime('now', 'localtime'))";
$st = $pdo->query("SELECT * FROM spots");
$spots = $st->fetchAll(PDO::FETCH_ASSOC);

// 各スポットに紐づくコメントも取得して配列に整理

// ... スポット取得の後のループ部分 ...

foreach ($spots as $key => $spot) {
    // 現在のループのスポットIDをしっかり指定
    $current_id = $spot['id'];

    // 1. コメント取得 (既存のコード)
    $c_st = $pdo->prepare("SELECT * FROM comments WHERE spot_id = ? AND (expires_at IS NULL OR expires_at > datetime('now', 'localtime'))");
    $c_st->execute([$current_id]);
    $spots[$key]['comments'] = $c_st->fetchAll(PDO::FETCH_ASSOC);

    // 2. リアクションをゲト
    $r_st = $pdo->prepare("SELECT reaction_type, count FROM reactions WHERE spot_id = ?");
    $r_st->execute([$current_id]);
    // reaction_typeをキー、countを値にした連想配列にする
    $reactions = $r_st->fetchAll(PDO::FETCH_KEY_PAIR); 

    // 配列の中に 'emo' や 'nazo' がなければ 0 を入れる
    $spots[$key]['emo_count'] = $reactions['emo'] ?? 0;
    $spots[$key]['nazo_count'] = $reactions['nazo'] ?? 0;
}

$json_spots = json_encode($spots);
?>

<!DOCTYPE html>
<html>

<head>
    <title>お散歩マップ</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 500px;
            width: 100%;
        }
    </style>
</head>

<body>
    <h1>お散歩マップ</h1>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // 赤いピンの定義
        const redIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
        // 地図の初期化（東京中心）　←中野中心にしてもいいかも
        const map = L.map('map').setView([35.6812, 139.7671], 15);

        // 背景地図（OpenStreetMap）の読み込み
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // PHPから渡されたスポットデータを表示
        const spots = <?php echo $json_spots; ?>;

        spots.forEach(spot => {
            // --- 1. 期間限定ラベルとピンの色の設定 ---
            let titlePrefix = "";
            let markerOptions = {}; // 通常は空（デフォルトの青ピン）

            if (spot.is_limited == 1) {
                titlePrefix = "<span style='color:red; font-weight:bold;'>【期間限定】</span> ";
                markerOptions = { icon: redIcon }; // 期間限定なら赤アイコンを指定
            }

            // --- 2. コメントHTMLの組み立て (既存のコード) ---
            let commentHtml = "<div style='max-height: 80px; overflow-y: auto;'><ul>";
            if (spot.comments && spot.comments.length > 0) {
                spot.comments.forEach(c => {
                    commentHtml += `<li><b>${c.name}:</b> ${c.body}</li>`;
                });
            } else {
                commentHtml += "<li>コメントなし</li>";
            }
            commentHtml += "</ul></div>";

            // --- 3. マーカーの作成と表示 ---
            L.marker([spot.latitude, spot.longitude], markerOptions) // 第2引数にオプションを追加
                .addTo(map)
                
                // --- ポップアップの中身 ---
                .bindPopup(`
    <div style="min-width: 160px;">
        ${titlePrefix} <strong>${spot.title}</strong><br>
        <span style="color: #666;">${spot.description}</span><br>
        ${spot.is_limited == 1 ?
                        `<small style="background:#fff0f0; border:1px solid red; padding:2px; display:inline-block; margin-top:5px;">
                                  表示期限: ${spot.expires_at}
                         </small>` : ""
                    }
        <div style="margin-top: 10px; display: flex; gap: 10px;">
            <a href="reaction.php?spot_id=${spot.id}&type=emo" style="text-decoration:none;">
                <button style="cursor:pointer;">🌸 エモい (${spot.emo_count})</button>
            </a>
            <a href="reaction.php?spot_id=${spot.id}&type=nazo" style="text-decoration:none;">
                <button style="cursor:pointer;">❓ 謎 (${spot.nazo_count})</button>
            </a>
        </div>

        <hr>
        <p style="font-size: 0.85em; margin-bottom: 5px;">💬 最新のコメント</p>
        ${commentHtml}
        <hr>
        <a href="comment_form.php?spot_id=${spot.id}" style="color: blue;">
            コメントを書く
        </a>
    </div>
`);
        });

    </script>
    <p><a href="post.php">新しいスポットを投稿する</a></p>
</body>

</html>