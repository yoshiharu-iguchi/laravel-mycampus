@extends('layouts.teacher')
@section('title', $subject->name_ja.' | 出席')

@section('content')
<h1 class="h5 mb-3">{{ $subject->name_ja ?? $subject->name_en ?? '名称未設定' }}｜出席管理</h1>

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

<form class="row gy-2 gx-2 align-items-end mb-3" method="get"
      action="{{ route('teacher.attendances.index', ['subject'=>$subject->id]) }}">
  <div class="col-auto">
    <label class="form-label mb-0">日付</label>
    <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm">
  </div>
  <div class="col-auto">
    <button class="btn btn-sm btn-outline-secondary">表示</button>
  </div>
  <div class="col-auto ms-auto">
    <a class="btn btn-sm btn-outline-dark"
       href="{{ route('teacher.subjects.show',$subject) }}">科目詳細へ戻る</a>
  </div>
</form>

<form method="post" action="{{ route('teacher.attendances.bulkUpdate', ['subject'=>$subject->id]) }}">
  @csrf
  <input type="hidden" name="date" value="{{ $date }}">

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead>
        <tr>
          <th style="width:14%">学籍番号</th>
          <th>氏名</th>
          <th style="width:22%">ステータス</th>
        </tr>
      </thead>
      <tbody>
      @forelse($students as $i => $st)
        @php $rec = $records[$st->id] ?? null; $val = $rec->status ?? 4; @endphp
        <tr>
          <td class="text-nowrap">{{ $st->student_number }}</td>
          <td>{{ $st->name }}</td>
          <td>
            <input type="hidden" name="rows[{{ $i }}][student_id]" value="{{ $st->id }}">
            <select name="rows[{{ $i }}][status]" class="form-select form-select-sm">
              <option value="1" @selected($val==1)>出席</option>
              <option value="2" @selected($val==2)>欠席</option>
              <option value="3" @selected($val==3)>遅刻</option>
              <option value="4" @selected($val==4)>未記録</option>
            </select>
          </td>
        </tr>
      @empty
        <tr><td colspan="3" class="text-muted">在籍学生がいません。</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-primary btn-sm">一括保存</button>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('teacher.subjects.show',$subject) }}">戻る</a>
  </div>
</form>
@endsection