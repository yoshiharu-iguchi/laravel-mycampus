@extends('layouts.admin')

@section('title','Subjects')

@section('content')



  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  {{-- 検索フォーム（学生一覧と同じUI） --}}
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ url()->current() }}" class="row g-2">
        <div class="col-sm-8 col-md-6">
          <input
            type="text"
            name="keyword"
            value="{{ old('keyword', $keyword) }}"
            class="form-control"
            placeholder="科目名で検索（例：作業療法概論）">
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

  {{-- 件数サマリー（学生一覧と統一） --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="small text-muted">
      全 {{ number_format($total) }} 件
      @if($subjects->count())
        ／ 表示 {{ number_format($subjects->firstItem()) }}–{{ number_format($subjects->lastItem()) }} 件
      @endif
    </div>
    <div>
      <a href="{{ route('admin.subjects.create') }}" class="btn btn-sm btn-success">新規登録</a>
    </div>
  </div>

  {{-- 結果テーブル --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            {{-- 必要なら @sortablelink に差し替え可能（例：@sortablelink('subject_code','科目コード')） --}}
            <th>科目コード</th>
            <th>科目名</th>
            <th>単位</th>
            <th>年度</th>
            <th>開講期間</th>
            <th>必修/選択</th>
            <th>定員</th>
            <th style="width: 160px;"></th>
          </tr>
        </thead>
        <tbody>
        @forelse($subjects as $subject)
          <tr>
            <td>{{ $subject->subject_code }}</td>
            <td>{{ $subject->name_ja }}</td>
            <td>{{ $subject->credits }}</td>
            <td>{{ $subject->year }}</td>
            <td>{{ $subject->term }}</td>
            <td>{{ $subject->category }}</td>
            <td>{{ $subject->capacity }}</td>
            <td class="text-end">
              <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-sm btn-outline-primary">詳細</a>
              <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-secondary">編集</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              該当する科目は見つかりませんでした。
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ページネーション（検索クエリを引き継ぐ） --}}
  <div class="mt-3">
    {{ $subjects->appends(['keyword' => $keyword])->links() }}
  </div>

@endsection