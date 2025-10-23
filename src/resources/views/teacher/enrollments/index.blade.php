@extends('layouts.teacher')
@section('title', ($subject->name_ja ?? $subject->name_en ?? '科目').' | 履修一覧')

@section('content')
<h1 class="h5 mb-3">{{ $subject->name_ja ?? $subject->name_en ?? '科目' }}｜履修一覧</h1>

<!-- @if(session('status'))
  <div class="alert alert-success">{{ session('status') }}</div>
@endif -->
<!-- @if($errors->any())
  <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif -->

{{-- 絞り込みフォーム（科目は固定なので年・学期のみ） --}}
<div class="card mb-3">
  <div class="card-body">
    <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-end">
      {{-- 年度 --}}
      <div class="col-6 col-md-3">
        <label class="form-label mb-0">年度</label>
        <input type="number" name="year" value="{{ old('year', request('year')) }}"
               class="form-control" placeholder="YYYY">
      </div>

      {{-- 学期 --}}
      <div class="col-6 col-md-3">
        <label class="form-label mb-0">学期</label>
        <select name="term" class="form-select">
          <option value="">指定しない</option>
          @foreach(\App\Enums\Term::cases() as $t)
            <option value="{{ $t->value }}" @selected((string)request('term') === (string)$t->value)>
              {{ $t->label() }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-auto">
        <button class="btn btn-outline-secondary">検索</button>
      </div>

      <div class="col-auto ms-auto">
        <a class="btn btn-outline-dark" href="{{ route('teacher.subjects.show',$subject) }}">科目詳細へ戻る</a>
      </div>
    </form>
  </div>
</div>

{{-- 一覧 --}}
<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead>
      <tr>
        <th style="width:16%">学籍番号</th>
        <th>氏名</th>
        <th style="width:10%">年度</th>
        <th style="width:12%">学期</th>
        <th style="width:18%">登録日時</th>
      </tr>
    </thead>
    <tbody>
      @forelse($enrollments as $en)
        @php $st = $en->student; @endphp
        
        <tr>
          <td>{{ $st?->student_number ?? '-' }}</td>
          <td>{{ $st?->name ?? '-' }}</td>
          <td>{{ $en->year }}</td>
          <td>{{ $en->term?->label() ?? '-' }}</td>
          <td>{{ $en->registered_at?->format('Y-m-d H:i') }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-muted">該当する履修がありません。</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">{{ $enrollments->links() }}</div>
@endsection
