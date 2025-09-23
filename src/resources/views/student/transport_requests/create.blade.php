<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>交通費申請（学生）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">交通費申請</h1>

  {{-- フラッシュ / エラー --}}
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- 申請フォーム（保存先） --}}
  <form method="POST" action="{{ route('student.tr.store') }}" class="card card-body mb-3">
    @csrf

    <div class="mb-3">
      <label class="form-label">実習先</label>
      <select name="facility_id" class="form-select" required>
        <option value="">選択してください</option>
        @foreach($facilities as $f)
          <option value="{{ $f->id }}" @selected(old('facility_id')==$f->id)>{{ $f->name }}（最寄り: {{ $f->nearest_station }}）</option>
        @endforeach
      </select>
    </div>

    <div class="row">
      <div class="col-sm-6 mb-3">
        <label class="form-label">出発駅</label>
        <input type="text" name="from_station_name" class="form-control"
               value="{{ old('from_station_name') }}" placeholder="例：大宮" required>
      </div>
      <div class="col-sm-6 mb-3">
        <label class="form-label">到着駅</label>
        <input type="text" name="to_station_name" class="form-control"
               value="{{ old('to_station_name') }}" placeholder="例：新宿" required>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-6 mb-3">
        <label class="form-label">日付</label>
        <input type="date" name="travel_date" class="form-control"
               value="{{ old('travel_date', now()->toDateString()) }}" required>
      </div>
      <div class="col-sm-6 mb-3">
        <label class="form-label">出発時刻（任意・HH:MM）</label>
        <input type="text" name="dep_time" class="form-control" placeholder="08:00"
               value="{{ old('dep_time') }}">
      </div>
    </div>

    <div class="row">
      <div class="col-sm-4 mb-3">
        <label class="form-label">到着時刻（任意・HH:MM）</label>
        <input type="text" name="arr_time" class="form-control" placeholder="09:00"
               value="{{ old('arr_time') }}">
      </div>
      <div class="col-sm-4 mb-3">
        <label class="form-label">合計（円）</label>
        <input type="number" name="total_yen" class="form-control" min="0"
               value="{{ old('total_yen') }}" required>
      </div>
      <div class="col-sm-4 mb-3">
        <label class="form-label">指定席料金（円・任意）</label>
        <input type="number" name="seat_fee_yen" class="form-control" min="0"
               value="{{ old('seat_fee_yen') }}">
      </div>
    </div>

    <div class="mb-3">
  <label class="form-label">駅すぱあと 検索URL</label>
  <input type="url" name="search_url" class="form-control"
         value="{{ old('search_url') }}" placeholder="検索で候補を選ぶと自動入力されます">
  {{-- ※ required は付けません（nullable に変更したため） --}}
</div>

    <button class="btn btn-primary">この内容で申請</button>
  </form>

  {{-- 検索（Ekispert）フォーム：必要項目だけをPOST --}}
  <form method="POST" action="{{ route('student.tr.search') }}" class="card card-body">
    @csrf
    <div class="row g-2 align-items-end">
      <div class="col-sm-4">
        <label class="form-label mb-1">出発駅</label>
        <input type="text" name="from_station_name" class="form-control"
               value="{{ old('from_station_name') }}" required>
      </div>
      <div class="col-sm-4">
        <label class="form-label mb-1">到着駅</label>
        <input type="text" name="to_station_name" class="form-control"
               value="{{ old('to_station_name') }}" required>
      </div>
      <div class="col-sm-3">
        <label class="form-label mb-1">日付</label>
        <input type="date" name="travel_date" class="form-control"
               value="{{ old('travel_date', now()->toDateString()) }}" required>
      </div>
      <div class="col-sm-3">
        <label class="form-label mb-1">時刻（任意）</label>
        <input type="text" name="time" class="form-control" placeholder="08:00"
               value="{{ old('time') }}">
      </div>
      <div class="col-sm-4">
        <label class="form-label mb-1">実習先</label>
        <select name="facility_id" class="form-select" required>
          <option value="">選択してください</option>
          @foreach($facilities as $f)
            <option value="{{ $f->id }}" @selected(old('facility_id')==$f->id)>{{ $f->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-auto">
        <button class="btn btn-outline-primary mt-3">駅すぱあとで検索</button>
      </div>
    </div>
  </form>

  {{-- 検索候補（search後に $options が渡ってきた場合だけ表示） --}}
  @isset($options)
    <div class="card mt-3">
      <div class="card-header">検索候補</div>
      <div class="list-group list-group-flush">
        @forelse($options as $op)
          <label class="list-group-item d-flex justify-content-between align-items-center">
            <span>
              <strong>{{ $op['title'] ?? '候補' }}</strong><br>
              @if(!empty($op['url']))
                <a href="{{ $op['url'] }}" target="_blank" rel="noopener" class="small">{{ $op['url'] }}</a>
              @endif
            </span>
            <span class="text-end">
              <div class="small text-muted">合計</div>
              <div class="fw-bold">{{ number_format((int)($op['total_yen'] ?? 0)) }}円</div>
              <button type="button"
  class="btn btn-sm btn-success mt-2 reflect-btn"
  data-url="{{ $op['url'] ?? '' }}"
  data-total="{{ (int)($op['total_yen'] ?? 0) }}"
  data-dep="{{ $op['dep_time'] ?? '' }}"
  data-arr="{{ $op['arr_time'] ?? '' }}">
  この候補を反映
</button>
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.reflect-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelector('input[name=search_url]').value = btn.dataset.url;
      document.querySelector('input[name=total_yen]').value  = btn.dataset.total;
      if (btn.dataset.dep) document.querySelector('input[name=dep_time]').value = btn.dataset.dep;
      if (btn.dataset.arr) document.querySelector('input[name=arr_time]').value = btn.dataset.arr;
      window.scrollTo({top:0, behavior:'smooth'});
    });
  });
});
</script>
            </span>
          </label>
        @empty
          <div class="list-group-item text-muted">候補が見つかりませんでした。</div>
        @endforelse
      </div>
    </div>
  @endisset

  {{-- ここから追記：反映ボタン用スクリプト --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.reflect-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const url   = btn.dataset.url || '';
          const total = btn.dataset.total || '';
          const dep   = btn.dataset.dep || '';
          const arr   = btn.dataset.arr || '';

          const q = (sel) => document.querySelector(sel);

          if (q('input[name=search_url]')) q('input[name=search_url]').value = url;
          if (q('input[name=total_yen]'))  q('input[name=total_yen]').value  = total;
          if (dep && q('input[name=dep_time]')) q('input[name=dep_time]').value = dep;
          if (arr && q('input[name=arr_time]')) q('input[name=arr_time]').value = arr;

          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      });
    });
  </script>
  {{-- 追記ここまで --}}

</div>
</body>
</html>