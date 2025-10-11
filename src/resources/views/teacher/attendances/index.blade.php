@extends('layouts.teacher')
@section('title', ($subject->name_ja ?? '名称未設定').' | 出席')

@section('content')
<h1 class="h5 mb-3">{{ $subject->name_ja ?? $subject->name ?? '名称未設定' }}｜出席管理</h1>

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

{{-- 日付切替 --}}
<form class="row gy-2 gx-2 align-items-end mb-3" method="get"
      action="{{ route('teacher.attendances.index', ['subject' => $subject->id]) }}">
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

{{-- 一括更新 --}}
<form method="post" action="{{ route('teacher.attendances.bulkUpdate') }}">
  @csrf
  <input type="hidden" name="subject_id" value="{{ $subject->id }}">
  <input type="hidden" name="date" value="{{ $date }}">

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead>
        <tr>
          <th style="width:14%">学籍番号</th>
          <th>氏名</th>
          <th style="width:28%">ステータス</th>
        </tr>
      </thead>
      <tbody>
      @forelse($rows as $i => $rec)
        @php $val = (int)($rec->status ?? \App\Models\Attendance::STATUS_UNRECORDED); @endphp
        <tr>
          <td class="text-nowrap">{{ $rec->student->student_number ?? '' }}</td>
          <td>{{ $rec->student->name }}</td>
          <td>
            <input type="hidden" name="rows[{{ $i }}][student_id]" value="{{ $rec->student_id }}">
            <select name="rows[{{ $i }}][status]" class="form-select form-select-sm">
              <option value="{{ \App\Models\Attendance::STATUS_PRESENT   }}" @selected($val===\App\Models\Attendance::STATUS_PRESENT)>出席</option>
              <option value="{{ \App\Models\Attendance::STATUS_ABSENT    }}" @selected($val===\App\Models\Attendance::STATUS_ABSENT)>欠席</option>
              <option value="{{ \App\Models\Attendance::STATUS_LATE      }}" @selected($val===\App\Models\Attendance::STATUS_LATE)>遅刻</option>
              <option value="{{ \App\Models\Attendance::STATUS_EXCUSED   }}" @selected($val===\App\Models\Attendance::STATUS_EXCUSED)>公欠</option>
              <option value="{{ \App\Models\Attendance::STATUS_UNRECORDED}}" @selected($val===\App\Models\Attendance::STATUS_UNRECORDED)>未記録</option>
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