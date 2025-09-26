<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>交通費申請</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    /* URLの省略表示に使う共通クラス */
    .url-clip { max-width: 420px; }
    @media (max-width: 576px) { .url-clip { max-width: 220px; } }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <h1 class="h5 mb-3">交通費申請</h1>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @if (session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
      @if (session('saved_url'))
        <div class="small mt-2 d-flex align-items-center gap-2 flex-wrap">
          <span class="text-muted">保存したURL：</span>
          <a class="d-inline-block text-truncate url-clip"
             href="{{ session('saved_url') }}" target="_blank" rel="noopener">
            {{ str(session('saved_url'))->limit(60) }}
          </a>
          <button class="btn btn-sm btn-outline-secondary"
                  type="button"
                  onclick='navigator.clipboard.writeText(@json(session("saved_url")))'>
            コピー
          </button>
        </div>
      @endif
    </div>
  @endif

  <div class="row g-4">
    <div class="col-12 col-lg-8">
      <div class="card mb-3">
        <div class="card-body">
          <div class="h6 mb-3">① 経路検索（駅すぱあとURLを作る）</div>

          <form method="POST" action="{{ route('student.tr.search') }}" class="row g-2">
            @csrf
            <div class="col-12 col-md-4">
              <label class="form-label small">自宅最寄り駅</label>
              <input name="from_station_name" class="form-control" value="{{ old('from_station_name') }}" placeholder="例）大宮(埼玉県)" required>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label small">実習先最寄り駅</label>
              <input name="to_station_name" class="form-control" value="{{ old('to_station_name') }}" placeholder="例）新宿" required>
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label small">日付</label>
              <input type="date" name="travel_date" class="form-control" value="{{ old('travel_date', now()->toDateString()) }}" required>
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label small">時刻</label>
              <input type="time" name="time" class="form-control" value="{{ old('time', '08:00') }}">
            </div>
            <div class="col-12">
              <button class="btn btn-primary">検索（URLを作る）</button>
            </div>
          </form>

          @php
            $viewerUrl = $viewerUrl ?? session('viewer_url');
            if (!empty($viewerUrl)) {
                if (str_starts_with($viewerUrl, '/')) {
                    $viewerUrl = 'https://roote.ekispert.net' . $viewerUrl;
                }
                $viewerUrl = str_replace('roote.ekispert.jp', 'roote.ekispert.net', $viewerUrl);
                $viewerUrl = preg_replace('#^http://#', 'https://', $viewerUrl);
            }
          @endphp

          @if(!empty($viewerUrl))
            <div class="alert alert-info mt-3">
              <div class="fw-bold small mb-1">駅すぱあと結果ページ</div>
              <a href="{{ $viewerUrl }}" target="_blank" rel="noopener">ここを開いて確認</a>
              <div class="small mt-2 d-flex align-items-center gap-2 flex-wrap">
                <code class="d-inline-block text-truncate url-clip">{{ str($viewerUrl)->limit(80) }}</code>
                <button class="btn btn-sm btn-outline-secondary"
                        type="button"
                        onclick='navigator.clipboard.writeText(@json($viewerUrl))'>
                  コピー
                </button>
              </div>
            </div>
          @endif
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <div class="h6 mb-3">② URLを見ながら、手入力で申請</div>

          <form method="POST" action="{{ route('student.tr.store') }}" class="row g-2">
            @csrf
            <div class="col-12 col-md-6">
              <label class="form-label small">自宅最寄り駅</label>
              <input name="from_station_name" class="form-control" value="{{ old('from_station_name') }}" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small">実習先最寄り駅</label>
              <input name="to_station_name" class="form-control" value="{{ old('to_station_name') }}" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small">日付</label>
              <input type="date" name="travel_date" class="form-control" value="{{ old('travel_date', now()->toDateString()) }}" required>
            </div>

            <div class="col-12">
              <label class="form-label small">駅すぱあと結果URL</label>
              <input type="url" name="search_url" class="form-control"
                     value="{{ old('search_url', $viewerUrl ?? '') }}"
                     placeholder="https://roote.ekispert.net/result?..." required>
            </div>

            <div class="col-12">
              <label class="form-label small">ルート（手入力 / 例：赤羽-池袋-新宿 JR 30分 片道240円）</label>
              <textarea name="admin_note" class="form-control" rows="3">{{ old('admin_note') }}</textarea>
              <div class="form-text">※簡単のため一時的に「管理メモ」欄に保存します。</div>
            </div>

            <div class="col-4 col-md-2">
              <label class="form-label small">出発</label>
              <input name="dep_time" class="form-control" placeholder="08:10" value="{{ old('dep_time') }}">
            </div>
            <div class="col-4 col-md-2">
              <label class="form-label small">到着</label>
              <input name="arr_time" class="form-control" placeholder="08:45" value="{{ old('arr_time') }}">
            </div>
            <div class="col-4 col-md-2">
              <label class="form-label small">運賃(円)</label>
              <input type="number" name="fare_yen" class="form-control" placeholder="240" value="{{ old('fare_yen') }}">
            </div>
            <div class="col-4 col-md-2">
              <label class="form-label small">指定席(円)</label>
              <input type="number" name="seat_fee_yen" class="form-control" placeholder="0" value="{{ old('seat_fee_yen') }}">
            </div>
            <div class="col-4 col-md-2">
              <label class="form-label small">合計(円)</label>
              <input type="number" name="total_yen" class="form-control" placeholder="240" value="{{ old('total_yen') }}">
            </div>

            <div class="col-12">
              <button class="btn btn-success">この内容で申請する</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="h6 mb-3">③ あなたの申請状況</div>

          @if(isset($myRequests) && $myRequests->count())
            <div class="list-group">
              @foreach($myRequests as $r)
                @php
                  $status = $r->status->name ?? $r->status ?? 'Pending';
                  $badge  = match (strtolower($status)) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'pending' => 'secondary',
                    default => 'secondary',
                  };
                @endphp

                <div class="list-group-item">
                  <div class="d-flex justify-content-between">
                    <div>
                      <div class="small text-muted">{{ $r->created_at?->format('Y-m-d H:i') }}</div>
                      <div class="fw-bold">{{ $r->from_station_name }} → {{ $r->to_station_name }}</div>
                      <div class="small">
                        出発 {{ $r->dep_time ?? '-' }} / 到着 {{ $r->arr_time ?? '-' }} /
                        合計 {{ $r->total_yen ? '¥'.number_format($r->total_yen) : '-' }}
                      </div>

                      @if($r->admin_note)
                        <div class="small text-muted mt-1" style="white-space:pre-wrap">{{ $r->admin_note }}</div>
                      @endif

                      @if($r->search_url)
                        <div class="small mt-1 d-flex align-items-center gap-2 flex-wrap">
                          <a href="{{ $r->search_url }}" target="_blank" rel="noopener">駅すぱあとURLを開く</a>
                          <span class="text-muted">（</span>
                          <span class="d-inline-block text-truncate url-clip">
                            {{ str($r->search_url)->limit(40) }}
                          </span>
                          <span class="text-muted">）</span>
                          <button class="btn btn-sm btn-outline-secondary"
                                  type="button"
                                  onclick='navigator.clipboard.writeText(@json($r->search_url))'>
                            コピー
                          </button>
                        </div>
                      @endif
                    </div>
                    <span class="badge bg-{{ $badge }} align-self-start">{{ ucfirst($status) }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-muted">まだ申請はありません。</div>
          @endif

        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>