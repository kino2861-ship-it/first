<!DOCTYPE html>
<html>

<head>
    <title>スポット投稿 - お散歩ガイドマップ</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 350px;
            width: 100%;
            margin-bottom: 20px;
            border: 2px solid #ccc;
        }

        .form-group {
            margin-bottom: 10px;
        }

        label {
            display: inline-block;
            width: 100px;
        }
    </style>
</head>

<body>
    <h1>新しいスポットを投稿</h1>
    <p>地図をクリックして、発見した場所を選んでください。</p>

    <div id="map"></div>

    <form action="post_submit.php" method="post">
        <div class="form-group">
            <label>タイトル</label>
            <input type="text" name="title" required placeholder="例：謎のオブジェ">
        </div>
        <div class="form-group">
            <label>ニックネーム</label>
            <input type="text" name="nickname" required>
        </div>
        <div class="form-group">
            <label>説明</label><br>
            <textarea name="description" rows="4" cols="40"></textarea>
        </div>
        <div class="form-group">
            <label>期間限定設定</label><br>
            <select name="limit_type" id="limit_type" onchange="toggleDateInput()">
                <option value="0">ずっと表示（通常）</option>
                <option value="1">期間限定（時間を指定）</option>
            </select>
        </div>

        <div id="date_input_area" style="display:none;" class="form-group">
            <label>表示期間</label>
            <input type="datetime-local" name="expires_at">
        </div>

        <script>
            function toggleDateInput() {
                const type = document.getElementById('limit_type').value;
                const area = document.getElementById('date_input_area');
                area.style.display = (type == "1") ? "block" : "none";
            }
        </script>
        <input type="hidden" name="latitude" id="lat">
        <input type="hidden" name="longitude" id="lng">

        <div class="form-group">
            <p>選択中の座標: <span id="coords">地図をクリックしてください</span></p>
            <button type="button" id="currentLocation" style="margin-top: 10px; padding: 8px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">📍 現在地取得</button>
            <span id="loadingIndicator" style="display: none; margin-left: 10px; color: #666;">取得中...</span>
        </div>

        <button type="submit">投稿する</button>
    </form>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([35.6812, 139.7671], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        let marker;

        // 地図クリック時のイベント
        map.on('click', function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            // すでにピンがあれば移動、なければ作成
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }

            // フォームのhidden項目と表示用テキストに値をセット
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            document.getElementById('coords').innerText = lat.toFixed(6) + ", " + lng.toFixed(6);
        });

        // 現在地取得ボタン
        document.getElementById('currentLocation').addEventListener('click', function() {
            const btn = document.getElementById('currentLocation');
            const loading = document.getElementById('loadingIndicator');

            // ボタン無効化とローディング表示
            btn.disabled = true;
            btn.style.opacity = '0.6';
            loading.style.display = 'inline';

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const latlng = L.latLng(lat, lng);

                    // マップをズーム
                    map.setView(latlng, 17);

                    // すでにピンがあれば移動、なければ作成
                    if (marker) {
                        marker.setLatLng(latlng);
                    } else {
                        marker = L.marker(latlng).addTo(map);
                    }

                    // フォームのhidden項目と表示用テキストに値をセット
                    document.getElementById('lat').value = lat;
                    document.getElementById('lng').value = lng;
                    document.getElementById('coords').innerText = lat.toFixed(6) + ", " + lng.toFixed(6);

                    // ローディング終了、ボタン有効化
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    loading.style.display = 'none';
                }, function(error) {
                    alert('現在地の取得に失敗しました: ' + error.message);
                    // ローディング終了、ボタン有効化
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    loading.style.display = 'none';
                });
            } else {
                alert('ブラウザが現在地取得に対応していません');
                // ローディング終了、ボタン有効化
                btn.disabled = false;
                btn.style.opacity = '1';
                loading.style.display = 'none';
            }
        });
    </script>
</body>

</html>