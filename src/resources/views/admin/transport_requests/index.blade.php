<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>交通費申請一覧（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap（必要ならレイアウトへ移動可） --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">交通費申請一覧</h1>

  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- 検索フォーム（状態＋キーワード） --}}
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-end">
        <div class="col-sm-4 col-md-3">
          <label class="form-label mb-1">状態</label>
          <select name="status" class="form-select">
            @php $st = request('status'); @endphp
            <option value="">すべて</option>
            <option value="pending"  @selected($st==='pending')>申請中</option>
            <option value="approved" @selected($st==='approved')>承認</option>
            <option value="rejected" @selected($st==='rejected')>却下</option>
          </select>
        </div>
        <div class="col-sm-8 col-md-6">
          <label class="form-label mb-1">キーワード</label>
          <input
            type="text"
            name="keyword"
            value="{{ old('keyword', request('keyword')) }}"
            class="form-control"
            placeholder="学生名・実習先名・区間などで検索">
        </div>
        <div class="col-sm-auto">
          <button type="submit" class="btn btn-primary">検索</button>
        </div>
        <div class="col-sm-auto">
          <a href="{{ url()->current() }}" class="btn btn-outline-secondary">リセット</a>
        </div>
      </form>

      <div class="mt-3 small text-muted">
        状態:
        <span class="badge text-bg-secondary">{{ $st ?: '指定なし' }}</span>
        ／ キーワード:
        <span class="badge text-bg-secondary">{{ request('keyword') ?: '未指定' }}</span>
      </div>
    </div>
  </div>

  {{-- 件数サマリー --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="small text-muted">
      全 {{ number_format($items->total()) }} 件
      @if($items->count())
        ／ 表示 {{ number_format($items->firstItem()) }}–{{ number_format($items->lastItem()) }} 件
      @endif
    </div>
  </div>

  @php
    use App\Enums\TransportRequestStatus as TRS;
  @endphp

  {{-- 結果テーブル --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:80px;">ID</th>
            <th>学生</th>
            <th>実習先</th>
            <th>区間</th>
            <th>日付</th>
            <th class="text-end" style="width:120px;">合計(円)</th>
            <th style="width:110px;">状態</th>
            <th style="width:240px;" class="text-end">操作</th>
          </tr>
        </thead>
        <tbody>
        @forelse($items as $it)
          <tr>
            <td>{{ $it->id }}</td>
            <td>
              {{ $it->student?->name }}<br>
              <span class="text-muted small">ID: {{ $it->student_id }}</span>
            </td>
            <td>{{ $it->facility?->name }}</td>
            <td>
              {{ $it->from_station_name }}
              <span class="text-muted">→</span>
              {{ $it->to_station_name }}
            </td>
            <td>{{ \Illuminate\Support\Carbon::parse($it->travel_date)->format('Y/m/d') }}</td>
            <td class="text-end">{{ number_format((int)($it->total_yen ?? 0)) }}</td>
            <td>
              <span class="badge
                @if($it->status === TRS::Pending) text-bg-warning
                @elseif($it->status === TRS::Approved) text-bg-success
                @else text-bg-danger
                @endif
              ">
                {{-- Enum の日本語ラベル（label() 実装済み前提） --}}
                {{ $it->status instanceof \App\Enums\TransportRequestStatus ? $it->status->label() : (string)$it->status }}
              </span>
            </td>
            <td class="text-end">
              <a href="{{ $it->search_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">結果URL</a>

              @if($it->status === TRS::Pending)
                {{-- 承認 --}}
                <form method="post" action="{{ route('admin.tr.approve',$it) }}" class="d-inline">
                  @csrf @method('PATCH')
                  <button type="submit" class="btn btn-sm btn-primary">承認</button>
                </form>

                {{-- 却下（理由を都度入力） --}}
                <form method="post" action="{{ route('admin.tr.reject',$it) }}" class="d-inline">
                  @csrf @method('PATCH')
                  <input type="hidden" name="admin_note" value="">
                  <button type="button" class="btn btn-sm btn-outline-danger"
                          onclick="const r=prompt('却下理由（任意）を入力'); if(r===null)return; this.form.admin_note.value=r; this.form.submit();">
                    却下
                  </button>
                </form>
              @else
                @if($it->approved_at)
                  <span class="text-muted small ms-2">
                    {{ \Illuminate\Support\Carbon::parse($it->approved_at)->format('Y/m/d H:i') }}
                  </span>
                @endif
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              申請はまだありません。
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ページネーション（検索クエリを引き継ぐ） --}}
  <div class="mt-3">
    {{ $items->appends([
      'status'  => request('status'),
      'keyword' => request('keyword'),
    ])->links() }}
  </div>

</div>

{{-- 2重送信ガード（簡易） --}}
<script>
document.querySelectorAll('form').forEach(f=>{
  f.addEventListener('submit',()=>{
    const btn = f.querySelector('button[type=submit],button:not([type])');
    if(btn){ btn.disabled = true; btn.textContent = btn.textContent + '…'; }
  });
});
</script>
</body>
</html>