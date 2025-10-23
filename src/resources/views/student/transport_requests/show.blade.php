<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>交通費申請詳細（学生）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">交通費申請 詳細</h1>


  @php use App\Enums\TransportRequestStatus as TRS; @endphp

  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <div>
          <div class="mb-1">
            <span class="text-muted small">区間</span><br>
            <strong>{{ $tr->from_station_name }}</strong>
            <span class="text-muted">→</span>
            <strong>{{ $tr->to_station_name }}</strong>
          </div>
          <div class="mb-1">
            <span class="text-muted small">日付・時刻</span><br>
            {{ \Illuminate\Support\Carbon::parse($tr->travel_date)->format('Y/m/d') }}
            {{ $tr->dep_time }} 〜 {{ $tr->arr_time }}
          </div>
          <div class="mb-1">
            <span class="text-muted small">金額</span><br>
            運賃 {{ number_format((int)$tr->fare_yen) }}円
            @if((int)$tr->seat_fee_yen > 0) ＋ 料金 {{ number_format((int)$tr->seat_fee_yen) }}円 @endif
            ＝ <b>合計 {{ number_format((int)$tr->total_yen) }}円</b>
          </div>
        </div>
        <div class="text-end">
          <span class="badge
            @if($tr->status === TRS::Pending) text-bg-warning
            @elseif($tr->status === TRS::Approved) text-bg-success
            @elseif($tr->status === TRS::Rejected) text-bg-danger
            @endif
          ">{{ $tr->status->label() }}</span>
          @if($tr->approved_at)
            <div class="small text-muted mt-2">{{ $tr->approved_at->format('Y/m/d H:i') }}</div>
          @endif
        </div>
      </div>

      @if($tr->search_url)
        <div class="mt-3">
          <a href="{{ $tr->search_url }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
            駅すぱあとで経路を開く
          </a>
        </div>
      @endif
    </div>
  </div>

  <a href="{{ route('tr.create') }}" class="btn btn-secondary">別の申請を作成</a>
</div>
</body>
</html>