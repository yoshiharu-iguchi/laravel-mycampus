@extends('layouts.admin')

@section('title','Teachers')

@section('content')


  {{-- 検索フォーム --}}
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ url()->current() }}" class="row g-2">
        <div class="col-sm-8 col-md-6">
          <input
            type="text"
            name="keyword"
            value="{{ old('keyword', $keyword) }}"
            class="form-control"
            placeholder="名前で検索（例：佐藤）">
        </div>
        <div class="col-sm-auto">
          <button type="submit" class="btn btn-primary">検索</button>
        </div>
        <div class="col-sm-auto">
          <a href="{{ url()->current() }}" class="btn btn-outline-secondary">リセット</a>
        </div>
      </form>

      <div class="mt-3 small text-muted">
        @if($keyword)
          キーワード: <span class="badge text-bg-secondary">{{ $keyword }}</span>
        @else
          キーワード未指定
        @endif
      </div>
    </div>
  </div>

  {{-- 件数サマリー --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="small text-muted">
      全 {{ number_format($total) }} 件
      @if($teachers->count())
        ／ 表示 {{ number_format($teachers->firstItem()) }}–{{ number_format($teachers->lastItem()) }} 件
      @endif
    </div>
    {{-- 追加ボタンを出す場合は以下（不要なら削除）
    <div>
      <a href="{{ route('admin.teachers.create') }}" class="btn btn-sm btn-success">新規登録</a>
    </div>
    --}}
  </div>

  {{-- 結果テーブル --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 90px;">教員氏名</th>
            {{-- 教員番号があるプロジェクトの場合は表示（無ければ列ごと削除OK） --}}
            @php $hasTeacherNumber = isset($teachers[0]) && isset($teachers[0]->teacher_number); @endphp
            @if($hasTeacherNumber)
              <th>教員番号</th>
            @endif
            <th>メールアドレス</th>
            <th style="width: 120px;"></th>
          </tr>
        </thead>
        <tbody>
        @forelse($teachers as $teacher)
          <tr>
            <td>{{ $teacher->name }}</td>
            @if($hasTeacherNumber)
              <td>{{ $teacher->teacher_number }}</td>
            @endif
            <td>{{ $teacher->email }}</td>
            <td class="text-end">
              {{-- ルート名は resource 想定（admin.teachers.show）にしています --}}
              <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-sm btn-outline-primary">
                詳細
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="{{ 3 + ($hasTeacherNumber ? 2 : 1) }}" class="text-center text-muted py-5">
              該当する教員は見つかりませんでした。
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ページネーション（検索クエリを引き継ぐ） --}}
  <div class="mt-3">
    {{ $teachers->appends(['keyword' => $keyword])->links() }}
  </div>

@endsection
