@extends('layouts.admin')

@section('title','Student list')

@section('content')

<!-- @if (session('flash_detail'))
  @php($fd = session('flash_detail'))
  <div class="alert alert-info">
    <div class="fw-bold mb-1">{{ $fd['title'] }}</div>
    <div class="small">{!! nl2br(e($fd['body'])) !!}</div>
  </div>
@endif -->

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
            placeholder="名前で検索（例：山田）">
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
      @if($students->count())
        ／ 表示 {{ number_format($students->firstItem()) }}–{{ number_format($students->lastItem()) }} 件
      @endif
    </div>
  </div>

  {{-- 結果テーブル --}}
  <div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>@sortablelink('student_number','学籍番号')</th>
          <th>@sortablelink('name','学生氏名')</th>
          <th>メールアドレス</th>
          <th style="width: 120px;">編集</th>
        </tr>
      </thead>
      <tbody>
      @forelse($students as $student)
        <tr>
          <td>{{ $student->student_number }}</td>
          <td>{{ $student->name }}</td>
          <td>{{ $student->email }}</td>
          <td>
            <a href="{{ route('admin.students.edit', $student) }}"
               class="btn btn-sm btn-outline-primary">編集</a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" class="text-center text-muted py-5">
            該当する学生は見つかりませんでした。
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

  {{-- ページネーション（検索クエリを引き継ぐ） --}}
  <div class="mt-3">
    {{ $students->appends(['keyword' => $keyword])->links() }}
  </div>

@endsection