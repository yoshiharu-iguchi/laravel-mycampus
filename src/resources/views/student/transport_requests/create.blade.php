
@extends('layouts.app')
@section('title','交通費申請ページ')
@section('content')
  <h1 class="h4 mb-4">経路検索・交通経路申請</h1>
  @php
   $vu = ($viewerUrl ?? null)
        ?? session('viewerUrl')
        ?? session('viewer_url')
        ?? old('search_url')
  @endphp

  <div class="row g-4">
    {{-- 左：検索+申請フォーム --}}
    <div class="col-lg-8">
      {{-- 上段:駅すぱあと検索フォーム --}}
      <div class="card">
        <div class="card-header">① 経路検索フォーム</div>
          <div class="card-body">
          <form class="row g-3" method="POST" action="{{ route('student.tr.search') }}">
            @csrf

            {{-- 実習施設プルダウン（選ぶと到着駅に最寄駅が入る） --}}
            <div class="col-12">
              <label for="facility_id" class="form-label small mb-1">実習施設(最寄駅)</label>
              <select name="facility_id" id="facility_id" class="form-select">
                <option value="">（未選択）</option>
                @foreach($facilities as $f)
                  <option
                    value="{{ $f->id }}"
                    data-station="{{ $f->nearest_station }}"
                    @selected(old('facility_id') == $f->id)
                  >
                    {{ $f->name }}（最寄駅：{{ $f->nearest_station }}）
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
          @if(!empty($vu))
            <div class="alert alert-info mt-3">
              検索結果URL：
              <a href="{{ $vu }}" target="_blank" rel="noopener">駅すぱあとを開く（別タブ）</a>
            </div>
          @endif
        </div>
      </div>

      {{-- 下段：申請フォーム --}}
      <div class="card mt-4">
        <div class="card-header">② 申請フォーム</div>
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
   <label class="form-label small mb-1">片道 金額（円）</label>
   <input type="number" name="fare_yen" class="form-control" value="{{ old('fare_yen') }}" min="0" step="1" inputmode="numeric" placeholder="例）450">
   @error('fare_yen') <div class="text-danger small">{{ $message }}</div> @enderror
 <div class="form-text">通学定期は対象外。片道の実費を整数で入力してください。</div>
 </div>

  {{-- 経路メモ（任意・管理者にも表示されます） --}}
  <div class="col-12">
    <label class="form-label small mb-1">経路メモ（必須）</label>
    <textarea
        name="route_memo"
        class="form-control"
        rows="3"
        placeholder="{{ session('route_memo_default', '例）埼京線／大宮(埼玉県)→ 赤羽 → 新宿（乗換1回）所要時間:30分') }}">{{ old('route_memo') }}</textarea>
      <div class="form-text">線路名・乗換え駅・所要時間など詳細に書いてください。</div>
        @error('route_memo') <div class="text-danger small">{{ $message }}</div> @enderror
  </div>


  {{-- 検索結果URL（必須） --}}
  <div class="col-12">
    <label class="form-label small mb-1">検索結果URL（必須）</label>
    <input type="url" name="search_url" class="form-control"
           value="{{ old('search_url') !== null ? old('search_url') : $vu}}"
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
                <span class="badge {{ $cls }}">{{ $label }}</span>
              </div>
              @if(!is_null($tr->fare_yen))
                <div class="small">片道: {{ number_format((int)$tr->fare_yen) }}円</div>
              @endif
              @if(!empty($tr->route_memo))
                <div class="small text-muted mt-1">
                メモ：{{ \Illuminate\Support\Str::limit($tr->route_memo, 60) }}               
                </div>
              @endif


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


@endsection

@push('scripts')
{{-- 施設選択 → 到着駅に最寄駅を入れる（シンプルJS） --}}
<script>
  (function(){
    const sel = document.getElementById('facility_id');
    const to  = document.getElementById('to_station_name');
    const btn = document.getElementById('copyNearestBtn');

    function extractNearest(opt){
      //1)data-stationを最優先
      let s = opt?.dataset?.station || '';
      if (s) return s.trim();
      //2)保険:表示テキストから「最寄駅:◯◯を抜き出す
      const m = (opt?.textContent || '').match(/最寄駅[：:]\s*([^）\)]+)/);
      return m ? m[1].trim():'';
    }
    
    function fillToStation(force=false){
      const opt = sel?.selectedOptions?.[0];
      const station = extractNearest(opt);
    
      if (!station) return;
      if (force || !to.value) to.value = station; // 手入力があるときは強制上書きしない
    }

    sel?.addEventListener('change', () => fillToStation(true));
    btn?.addEventListener('click',  () => fillToStation(true));

    // 初期表示時、到着駅が空で施設が選ばれていたら入れておく
    document.addEventListener('DOMContentLoaded', () => {
      if (sel && to && !to.value && sel.value) fillToStation(false);
    });
  })();
</script>
@endpush
