<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>科目 詳細（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">科目 詳細</h1>

  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="d-flex gap-2 mb-3">
    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-outline-primary">編集</a>
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">一覧へ戻る</a>

    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('この科目を削除しますか？');" class="ms-auto">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-outline-danger">削除</button>
    </form>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <tbody>
          <tr>
            <th style="width: 200px;" class="table-light">ID</th>
            <td>{{ $subject->id }}</td>
          </tr>
          <tr>
            <th class="table-light">科目コード</th>
            <td>{{ $subject->subject_code }}</td>
          </tr>
          <tr>
            <th class="table-light">科目名（日本語）</th>
            <td>{{ $subject->name_ja }}</td>
          </tr>
          <tr>
            <th class="table-light">科目名（英語）</th>
            <td>{{ $subject->name_en }}</td>
          </tr>
          <tr>
            <th class="table-light">単位</th>
            <td>{{ $subject->credits }}</td>
          </tr>
          <tr>
            <th class="table-light">年度</th>
            <td>{{ $subject->year }}</td>
          </tr>
          <tr>
            <th class="table-light">学期</th>
            <td>{{ $subject->term }}</td>
          </tr>
          <tr>
            <th class="table-light">区分</th>
            <td>
              @if($subject->category === 'required') 必修 @elseif($subject->category === 'elective') 選択 @else {{ $subject->category }} @endif
            </td>
          </tr>
          <tr>
            <th class="table-light">定員</th>
            <td>{{ $subject->capacity }}</td>
          </tr>
          <tr>
            <th class="table-light">概要</th>
            <td class="text-pre-wrap">{{ $subject->description }}</td>
          </tr>
          <tr>
            <th class="table-light">作成日時</th>
            <td>{{ optional($subject->created_at)->format('Y-m-d H:i') }}</td>
          </tr>
          <tr>
            <th class="table-light">更新日時</th>
            <td>{{ optional($subject->updated_at)->format('Y-m-d H:i') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>
