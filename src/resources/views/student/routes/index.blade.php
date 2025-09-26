<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>経路検索（駅すぱあと）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-3">経路検索（駅すぱあと）</h1>

  <!-- 検索フォーム -->
  <form class="row g-2 mb-4" method="GET" action="">
    <div class="col-12 col-md-3">
      <label class="form-label small mb-1">出発駅（自宅最寄り）</label>
      <input name="from" class="form-control" placeholder="例）大宮(埼玉県)">
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label small mb-1">到着駅（実習先最寄り）</label>
      <input name="to" class="form-control" placeholder="例）新宿">
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label small mb-1">出発日時</label>
      <input type="datetime-local" name="when" class="form-control">
    </div>
    <div class="col-12 col-md-3 align-self-end">
      <button class="btn btn-primary w-100">検索</button>
    </div>
  </form>

  <!-- 結果一覧（サンプル） -->
  <div class="list-group">
    <div class="list-group-item">
      <div class="d-flex justify-content-between">
        <div>
          <div class="fw-bold">08:00 → 08:32（約32分）</div>
          <div>概算：¥780（運賃¥780 / 指定¥0）</div>
          <div class="text-muted small">JR京浜東北線 / JR埼京線</div>
          <a href="https://roote.ekispert.net/result?sample" target="_blank" rel="noopener" class="small">駅すぱあと結果を開く</a>
        </div>
        <form method="POST" action="">
          <button class="btn btn-success">このルートで申請</button>
        </form>
      </div>
    </div>

    <div class="list-group-item">
      <div class="d-flex justify-content-between">
        <div>
          <div class="fw-bold">08:15 → 08:49（約34分）</div>
          <div>概算：¥920（運賃¥780 / 指定¥140）</div>
          <div class="text-muted small">JR湘南新宿ライン</div>
          <a href="https://roote.ekispert.net/result?sample2" target="_blank" rel="noopener" class="small">駅すぱあと結果を開く</a>
        </div>
        <form method="POST" action="">
          <button class="btn btn-success">このルートで申請</button>
        </form>
      </div>
    </div>
  </div>

</div>
</body>
</html>