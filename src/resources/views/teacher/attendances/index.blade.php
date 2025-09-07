<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>出席簿（教員）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">出席簿（科目 × 日付）</h1>

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

  {{-- 科目＆日付選択（GET） --}}
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('teacher.attendances.index') }}" class="row g-2">
        <div class="col-md-6">
          <label class="form-label">科目</label>
          <select name="subject_id" class="form-select" required>
            <option value="">選択してください</option>
            @foreach($subjects as $s)
              <option value="{{ $s->id }}"
                @if(request('subject_id') == $s->id || (isset($subject) && $subject && $subject->id == $s->id)) selected @endif>
                {{ $s->name_ja }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">日付</label>
          <input type="date" name="date" class="form-control" value="{{ $date }}" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button class="btn btn-primary w-100">表示</button>
        </div>
      </form>
    </div>
  </div>

  {{-- 出席簿テーブル --}}
  @if($subject)
    @php
      $statusLabels = __('attendance.statuses'); // ['0'=>'欠席',...]
      $order = [1,0,2,3,4]; // 出席→欠席→遅刻→公欠→未記録
    @endphp

    <div class="small text-muted mb-2">
      {{ $subject->name_ja }} ／ {{ $date }} ／ 受講者 {{ number_format($attendances->count()) }} 名
    </div>

    <div class="card">
      <div class="table-responsive">
        <form method="POST" action="{{ route('teacher.attendances.bulkUpdate') }}">
          @csrf
          <input type="hidden" name="subject_id" value="{{ $subject->id }}">
          <input type="hidden" name="date" value="{{ $date }}">

          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 40%;">学生名</th>
                <th style="width: 220px;">状態</th>
                <th>備考</th>
              </tr>
            </thead>
            <tbody>
            @forelse($attendances as $i => $a)
              <tr>
                <td>{{ $a->student->name }}</td>
                <td>
                  <input type="hidden" name="rows[{{ $i }}][id]" value="{{ $a->id }}">
                  <select name="rows[{{ $i }}][status]" class="form-select">
                    @foreach($order as $val)
                      <option value="{{ $val }}" @selected($a->status === $val)>
                        {{ $statusLabels[(string)$val] ?? '不明' }}
                      </option>
                    @endforeach
                  </select>
                </td>
                <td>
                  <input type="text"
                         name="rows[{{ $i }}][note]"
                         value="{{ old("rows.$i.note", $a->note) }}"
                         class="form-control"
                         maxlength="255"
                         placeholder="（任意）メモ">
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted py-5">
                  受講学生がいません。
                </td>
              </tr>
            @endforelse
            </tbody>
          </table>
        </form>
      </div>
      <div class="card-footer text-end">
        <button form="__save_form__" class="d-none"></button>
        <button type="submit"
                class="btn btn-success"
                onclick="this.closest('.card').querySelector('form').submit()">
          保存
        </button>
      </div>
    </div>
  @else
    <div class="card">
      <div class="card-body text-muted">
        科目と日付を選んで「表示」を押してください。
      </div>
    </div>
  @endif

</div>
</body>
</html>