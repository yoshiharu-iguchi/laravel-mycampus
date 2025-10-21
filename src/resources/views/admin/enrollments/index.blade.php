@extends('layouts.admin')

@section('title','Enrollments')
@section('content')
  {{-- フラッシュメッセージ --}}
  <!-- @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif -->

  {{-- 検索フォーム --}}
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ url()->current() }}" class="row g-2">

        {{-- 科目 --}}
        <div class="col-12 col-md-4">
          <select name="subject_id" class="form-select">
            <option value="">科目を選択</option>
            @foreach($subjects as $s)
              <option value="{{ $s->id }}" @selected((string)request('subject_id') === (string)$s->id)>
                {{ $s->name_ja ?? $s->name_en ?? '不明' }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- 年度 --}}
        <div class="col-6 col-md-2">
          <input type="number" name="year" value="{{ request('year') }}"
                 class="form-control" placeholder="年度(YYYY)" min="2000" max="2100">
        </div>

        {{-- 学期 --}}
        <div class="col-6 col-md-2">
          <select name="term" class="form-select">
            <option value="">開講期間</option>
            @foreach(['前期','後期','通年'] as $t)
              <option value="{{ $t }}" @selected(request('term')===$t)>{{ $t }}</option>
            @endforeach
          </select>
        </div>

        {{-- キーワード（学生名 or 学籍番号） --}}
        <div class="col-12 col-md-3">
          <input type="text" name="keyword" value="{{ old('keyword', $keyword) }}"
                 class="form-control" placeholder="学生名 or 学籍番号">
        </div>

        <div class="col-6 col-md-1 d-grid">
          <button type="submit" class="btn btn-primary">検索</button>
        </div>
        <div class="col-6 col-md-auto d-grid">
          <a href="{{ url()->current() }}" class="btn btn-outline-secondary">リセット</a>
        </div>
      </form>

      {{-- 選択中フィルタ表示 --}}
      <div class="mt-3 small text-muted">
        @php
          $selectedSubject = null;
          if (request('subject_id')) {
            $selectedSubject = $subjects->firstWhere('id', (int)request('subject_id'));
          }
        @endphp
        <span class="me-2">フィルタ:</span>
        @if(request('subject_id'))
          <span class="badge text-bg-secondary me-1">
            科目: {{ $selectedSubject->name_ja ?? $selectedSubject->name_en ?? ('ID:'.request('subject_id')) }}
          </span>
        @endif
        @if(request('year'))
          <span class="badge text-bg-secondary me-1">年度: {{ request('year') }}</span>
        @endif
        @if(request('term'))
          <span class="badge text-bg-secondary me-1">学期: {{ request('term') }}</span>
        @endif
        @if($keyword)
          <span class="badge text-bg-secondary me-1">KW: {{ $keyword }}</span>
        @endif
        @if(!request('subject_id') && !request('year') && !request('term') && !$keyword)
          <span class="text-muted">未指定</span>
        @endif
      </div>
    </div>
  </div>

  {{-- 件数サマリー --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="small text-muted">
      全 {{ number_format($total) }} 件
      @if($rows->count())
        ／ 表示 {{ number_format($rows->firstItem()) }}–{{ number_format($rows->lastItem()) }} 件
      @endif
    </div>
  </div>

  {{-- 結果テーブル --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 90px;">科目コード</th>
            <th>科目</th>
            <th>年度</th>
            <th>開講期間</th>
            <th>履修登録者数</th>
            <th style="width: 180px;"></th>
          </tr>
        </thead>
        <tbody>
        @forelse($rows as $subject)
          <tr>
            <td>{{ $subject->subject_code }}</td>
            <td>{{ $subject->name_ja ?? $subject->name_en ?? '名称未設定' }}</td>
            <td>{{ $subject->year ?? '-'}}</td>
            <td>{{ $subject->term_label ?? '-' }}</td>
            <td>{{ $subject->enrollments_count }} 名</td>
            <td class="text-end">
              <a href="{{ route('admin.enrollments.bySubject', $subject) }}" class="btn btn-sm btn-outline-primary">
                履修学生一覧
              </a>
              <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-sm btn-outline-secondary">
                科目詳細
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-5">履修登録情報はありません。</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ページネーション（検索クエリを引き継ぐ） --}}
  <div class="mt-3">
    {{ $rows->appends(request()->only(['subject_id','year','term','keyword']))->links() }}
  </div>
@endsection
