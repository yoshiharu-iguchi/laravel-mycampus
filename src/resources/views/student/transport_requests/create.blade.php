<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>交通費申請（学生）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap（必要ならレイアウトへ移動可） --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">交通費申請</h1>

  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- 入力フォーム --}}
  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('student.tr.store') }}" class="row g-3">
        @csrf

        {{-- 実習先 --}}
        <div class="col-12">
          <label class="form-label">実習先 <span class="text-danger">*</span></label>
          <select name="facility_id" class="form-select" required>
            <option value="">選択してください</option>
            @foreach($facilities as $f)
              <option value="{{ $f->id }}" @selected(old('facility_id')==$f->id)>
                {{ $f->name }}（最寄：{{ $f->nearest_station ?? '未設定' }}）
              </option>
            @endforeach
          </select>
        </div>

        {{-- 区間（自宅⇄実習先） --}}
        <div class="col-md-6">
          <label class="form-label">自宅最寄駅 <span class="text-danger">*</span></label>
          <input
            type="text"
            name="from_station_name"
            value="{{ old('from_station_name') }}"
            class="form-control"
            placeholder="例）大宮(埼玉県)"
            required>
        </div>
        <div class="col-md-6">
          <label class="form-label">実習先最寄駅 <span class="text-danger">*</span></label>
          <input
            type="text"
            name="to_station_name"
            value="{{ old('to_station_name') }}"
            class="form-control"
            placeholder="例）新宿"
            required>
        </div>

        {{-- 日付・時刻 --}}
        <div class="col-md-4">
          <label class="form-label">移動日 <span class="text-danger">*</span></label>
          <input
            type="date"
            name="travel_date"
            value="{{ old('travel_date', now()->toDateString()) }}"
            class="form-control"
            required>
        </div>
        <div class="col-md-4">
          <label class="form-label">出発時刻（任意）</label>
          <input
            type="text"
            name="dep_time"
            value="{{ old('dep_time') }}"
            class="form-control"
            placeholder="例 08:30">
        </div>
        <div class="col-md-4">
          <label class="form-label">到着時刻（任意）</label>
          <input
            type="text"
            name="arr_time"
            value="{{ old('arr_time') }}"
            class="form-control"
            placeholder="例 09:10">
        </div>

        {{-- 金額 --}}
        <div class="col-md-4">
          <label class="form-label">運賃（円） <span class="text-danger">*</span></label>
          <input
            type="number"
            name="fare_yen"
            value="{{ old('fare_yen') }}"
            class="form-control"
            min="0"
            required>
        </div>
        <div class="col-md-4">
          <label class="form-label">指定席（円）</label>
          <input
            type="number"
            name="seat_fee_yen"
            value="{{ old('seat_fee_yen', 0) }}"
            class="form-control"
            min="0">
        </div>
        <div class="col-md-4">
          <label class="form-label">合計（円） <span class="text-danger">*</span></label>
          <input
            type="number"
            name="total_yen"
            value="{{ old('total_yen') }}"
            class="form-control"
            min="0"
            required>
        </div>

        {{-- 検索結果URL --}}
        <div class="col-12">
          <label class="form-label">駅すぱあと検索結果URL <span class="text-danger">*</span></label>
          <input
            type="url"
            name="search_url"
            value="{{ old('search_url') }}"
            class="form-control"
            placeholder="結果ページのURLを貼り付け"
            required>
          <div class="form-text">
            ※ フリープラン中は学生がURLを貼ります（キー発行後はサーバーで自動取得に切替予定）
          </div>
        </div>

        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-primary">申請する</button>
          <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">戻る</a>
        </div>
      </form>
    </div>
  </div>

</div>
</body>
</html>