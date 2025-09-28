<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>交通費申請（学生）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap（必要ならレイアウトへ移動可） -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">交通費申請（学生）</h1>

  {{-- フラッシュメッセージ --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row g-4">
    {{-- 左：検索＋申請フォーム --}}
    <div class="col-lg-8">

      {{-- 上段：駅すぱあと検索フォーム --}}
      <div class="card">
        <div class="card-header">① 駅すぱあとで検索URL作成</div>
        <div class="card-body">
          <form class="row g-3" method="POST" action="{{ route('student.tr.search') }}">
            @csrf

            {{-- 実習施設プルダウン（選ぶと到着駅に最寄駅が入る） --}}
            <div class="col-12">
              <label for="facility_id" class="form-label small mb-1">実習施設（選ぶと到着駅に最寄駅が入ります）</label>
              <select name="facility_id" id="facility_id" class="form-select">
                <option value="">（未選択）</option>
                @foreach($facilities as $f)
                  <option
                    value="{{ $f->id }}"
                    data-station="{{ $f->nearest_station }}"
                    @selected(old('facility_id') == $f->id)
                  >
                    {{ $f->name }}（最寄：{{ $f->nearest_station }}）
                  </option>
                @endforeach
              </select>
              @error('facility_id') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            {{-- 出発駅 --}}
            <div class="col-md-6">
              <label class="form-label small mb-1">出発駅</label>
              <input type="text" name="from_station_name" class="form-control"
                     value="{{ old('from_station_name') }}" placeholder="例）大宮(埼玉県)">
              @error('from_station_name') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            {{-- 到着駅（施設選択で自動入力／ボタンで強制コピー） --}}
            <div class="col-md-6">
              <label class="form-label small mb-1">到着駅</label>
              <div class="input-group">
                <input type="text" id="to_station_name" name="to_station_name" class="form-control"
                       value="{{ old('to_station_name') }}" placeholder="例）新宿">
                <button type="button" id="copyNearestBtn" class="btn btn-outline-secondary">最寄駅を入れる</button>
              </div>
              @error('to_station_name') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            {{-- 日付:default=今日(Asia/Tokyo) --}}
            <div class="mb-3">
              <label class="form-label">日付</label>
              <input type="date" name="travel_date" class="form-control" value="{{ old('travel_date',now()->timezone('Asia/Tokyo')->toDateString()) }}">
            </div>

            {{-- 到着時刻:default=08:00(編集可能) --}}
            <div class="mb-3">
              <label class="form-label">到着時刻</label>
              <input type="time" name="arr_time" class="form-control" value="{{ old('arr_time','08:00') }}">
              <div class="form-text">到着時刻は8:00に設定しています。必要に応じて変更して下さい。</div>
            </div>

            <div class="col-12 d-flex gap-2">
              <button class="btn btn-primary">検索URL作成</button>
              <a href="{{ route('student.tr.create', ['clear' => 1]) }}" class="btn btn-outline-secondary">プレビューを消す</a>
            </div>
          </form>

          {{-- 検索結果URLのプレビュー --}}
          @if(!empty($viewerUrl))
            <div class="alert alert-info mt-3">
              検索結果URL：
              <a href="{{ $viewerUrl }}" target="_blank" rel="noopener">新しいタブで開く</a>
            </div>
          @endif
        </div>
      </div>

      {{-- 下段：申請フォーム --}}
      <div class="card mt-4">
        <div class="card-header">② この内容で申請する</div>
        <div class="card-body">
          <form class="row g-3" method="POST" action="{{ route('student.tr.store') }}">
  @csrf

  {{-- 実習施設（任意） --}}
  <div class="col-12">
    <label for="facility_id_store" class="form-label small mb-1">実習施設（任意）</label>
    <select name="facility_id" id="facility_id_store" class="form-select">
      <option value="">（未選択）</option>
      @foreach($facilities as $f)
        <option value="{{ $f->id }}" @selected(old('facility_id') == $f->id)>{{ $f->name }}</option>
      @endforeach
    </select>
    @error('facility_id') <div class="text-danger small">{{ $message }}</div> @enderror
  </div>

  {{-- 出発駅・到着駅 --}}
  <div class="col-md-6">
    <label class="form-label small mb-1">出発駅</label>
    <input type="text" name="from_station_name" class="form-control" value="{{ old('from_station_name') }}">
    @error('from_station_name') <div class="text-danger small">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-6">
    <label class="form-label small mb-1">到着駅</label>
    <input type="text" name="to_station_name" class="form-control" value="{{ old('to_station_name') }}">
    @error('to_station_name') <div class="text-danger small">{{ $message }}</div> @enderror
  </div>

  {{-- 日付・運賃 --}}
  <div class="col-md-6">
    <label class="form-label small mb-1">日付</label>
    <input type="date" name="travel_date" class="form-control" value="{{ old('travel_date') }}">
    @error('travel_date') <div class="text-danger small">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-6">
    <label class="form-label small mb-1">運賃（円）</label>
    <input type="number" name="fare_yen" class="form-control" value="{{ old('fare_yen') }}">
    @error('fare_yen') <div class="text-danger small">{{ $message }}</div> @enderror
  </div>
  {{-- 経路メモ（任意・管理者にも表示されます） --}}
<div class="col-12">
  <label class="form-label small mb-1">経路メモ ※到着時刻 / 線路名 /乗り換え駅 /所要時間を記載</label>
<textarea name="route_note"
          class="form-control"
          rows="3"
          placeholder="{{ session('route_memo_default', '到着 08:00 / 埼京線 / 大宮(埼玉県) → 赤羽 → 新宿 / 30分') }}">{{ old('route_note') }}</textarea>
<div class="form-text">
  ※却下時は、その理由を管理者がメールに記載します。
</div>
  @error('route_note') <div class="text-danger small">{{ $message }}</div> @enderror
</div>

  {{-- 検索結果URL（必須） --}}
  <div class="col-12">
    <label class="form-label small mb-1">検索結果URL（必須）</label>
    <input type="url" name="search_url" class="form-control"
           value="{{ old('search_url', $viewerUrl) }}"
           placeholder="駅すぱあと検索結果ページのURLを貼り付け">
    @error('search_url') <div class="text-danger small">{{ $message }}</div> @enderror
    @if(session('saved_url'))
      <div class="form-text">保存したURL：<a href="{{ session('saved_url') }}" target="_blank" rel="noopener">開く</a></div>
    @endif
  </div>

  <div class="col-12">
    <button class="btn btn-success">この内容で申請する</button>
  </div>
</form>
        </div>
      </div>
    </div>

    {{-- 右：最近の申請 --}}
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">直近の申請（10件）</div>
        <div class="card-body">

          @php use App\Enums\TransportRequestStatus as TRS; @endphp

          @forelse($myRequests as $tr)
            <div class="mb-3">
              <div class="small text-muted">{{ $tr->created_at?->format('Y-m-d H:i') }}</div>

              <div>{{ $tr->from_station_name }} <span class="text-muted">→</span> {{ $tr->to_station_name }}</div>

              <div class="small">
                日付：
                {{ optional($tr->travel_date ? \Illuminate\Support\Carbon::parse($tr->travel_date) : null)->format('Y/m/d') }}
              </div>

              @php
                // クラス決定（Enum キャスト無くても動くようにフォールバック）
                $cls = 'text-bg-secondary';
                if ($tr->status instanceof \App\Enums\TransportRequestStatus) {
                  if ($tr->status === TRS::Pending)      $cls = 'text-bg-warning';
                  elseif ($tr->status === TRS::Approved) $cls = 'text-bg-success';
                  elseif ($tr->status === TRS::Rejected) $cls = 'text-bg-danger';
                } else {
                  switch ((string)$tr->status) {
                    case 'pending':  $cls = 'text-bg-warning'; break;
                    case 'approved': $cls = 'text-bg-success'; break;
                    case 'rejected': $cls = 'text-bg-danger';  break;
                  }
                }

                // ラベル決定
                if ($tr->status instanceof \App\Enums\TransportRequestStatus) {
                  $label = $tr->status->label();
                } else {
                  $label = match ((string)$tr->status) {
                    'pending'  => '申請中',
                    'approved' => '承認',
                    'rejected' => '却下',
                    default    => '未設定',
                  };
                }
              @endphp

              <div class="small d-flex align-items-center gap-2">
                <span>合計：{{ number_format((int)($tr->total_yen ?? 0)) }}円</span>
                <span class="badge {{ $cls }}">{{ $label }}</span>
              </div>

              @if($tr->search_url)
                <div class="small">
                  <a href="{{ $tr->search_url }}" target="_blank" rel="noopener">検索結果URL</a>
                </div>
              @endif

              <hr>
            </div>
          @empty
            <div class="text-muted">まだ申請はありません。</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>

{{-- 施設選択 → 到着駅に最寄駅を入れる（シンプルJS） --}}
<script>
  (function(){
    const sel = document.getElementById('facility_id');
    const to  = document.getElementById('to_station_name');
    const btn = document.getElementById('copyNearestBtn');

    function fillToStation(force=false){
      const opt = sel?.selectedOptions?.[0];
      const station = opt?.dataset?.station || '';
      if (!station) return;
      if (force || !to.value) to.value = station; // 手入力があるときは強制上書きしない
    }

    sel?.addEventListener('change', () => fillToStation(false));
    btn?.addEventListener('click',  () => fillToStation(true));

    // 初期表示時、到着駅が空で施設が選ばれていたら入れておく
    document.addEventListener('DOMContentLoaded', () => {
      if (sel && to && !to.value && sel.value) fillToStation(false);
    });
  })();
</script>
</body>
</html>
