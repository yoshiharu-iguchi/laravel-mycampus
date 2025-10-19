@extends('layouts.student')
@section('page-title','科目一覧')

@section('student-content')
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ url()->current() }}" class="row g-2">
        <div class="col-sm-8 col-md-6">
          <input type="text" name="keyword"
            value="{{ old('keyword', $keyword ?? request('keyword')) }}"
            class="form-control" placeholder="科目名で検索（例：作業療法概論）">
        </div>
        <div class="col-sm-auto">
          <button type="submit" class="btn btn-primary">検索</button>
        </div>
        <div class="col-sm-auto">
          <a href="{{ url()->current() }}" class="btn btn-outline-secondary">リセット</a>
        </div>
      </form>

      <div class="mt-3 small text-muted">
        @if(!empty($keyword ?? request('keyword')))
          キーワード: <span class="badge text-bg-secondary">{{ $keyword ?? request('keyword') }}</span>
        @else
          キーワード未指定
        @endif
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="small text-muted">
      全 {{ number_format($total ?? $subjects->total()) }} 件
      @if($subjects->count())
        ／ 表示 {{ number_format($subjects->firstItem()) }}–{{ number_format($subjects->lastItem()) }} 件
      @endif
    </div>
    <div>
      <a href="{{ route('student.enrollments.index') }}" class="btn btn-sm btn-outline-primary">履修登録一覧</a>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:90px;">ID</th>
            <th>科目コード</th>
            <th>科目名</th>
            <th>単位</th>
            <th>年度</th>
            <th>開講期間</th>
            <th>必修/選択</th>
            <th>定員</th>
            <th style="width:200px;"></th>
          </tr>
        </thead>
        <tbody>
        @forelse($subjects as $subject)
          <tr>
            <td>{{ $subject->id }}</td>
            <td>{{ $subject->subject_code }}</td>
            <td>{{ $subject->name_ja ?? $subject->name_en ?? '名称未設定' }}</td>
            <td>{{ rtrim(rtrim(number_format($subject->credits,1),'0'),'.') }}</td>
            <td>{{ $subject->year ?? '—' }}</td>
            <td>{{ $subject->term ?? '—' }}</td>
            <td>
              @php
                $cat = $subject->category ?? null;
                $label = $cat==='required' ? '必修' : ($cat==='elective' ? '選択' : ($cat ?? '—'));
              @endphp
              {{ $label }}
            </td>
            <td>{{ $subject->capacity ?? '—' }}</td>
            <td class="text-end">
              @php $isEnrolled = in_array($subject->id, $enrolledIds ?? []); @endphp
              <a href="{{ route('student.subjects.show', $subject) }}" class="btn btn-sm btn-outline-primary">詳細</a>
              @if($isEnrolled)
                <span class="badge text-bg-success ms-1 align-middle">履修中</span>
              @else
                <span class="text-muted small ms-1 align-middle">未履修</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center text-muted py-5">該当する科目は見つかりませんでした。</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $subjects->appends(['keyword' => $keyword ?? request('keyword')])->links() }}
  </div>
@endsection
