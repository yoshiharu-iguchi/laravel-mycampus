@extends('layouts.teacher')
@section('title','担当科目一覧')

@section('content')
<h1 class="h4 mb-3">担当科目</h1>

@if($subjects->isEmpty())
  <div class="alert alert-info">担当科目がありません。</div>
@else
  <div class="list-group">
    @foreach($subjects as $subject)
      <div class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-semibold">{{ $subject->name_ja ?? $subject->name_en ?? '名称未設定' }}</div>
          <small class="text-muted">科目コード: {{ $subject->subject_code ?? '—' }} / 在籍: {{ $subject->students_count }}名</small>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-outline-secondary btn-sm"
             href="{{ route('teacher.subjects.show',$subject) }}">詳細</a>
          <a class="btn btn-outline-primary btn-sm"
             href="{{ route('teacher.attendances.index',['subject'=>$subject->id,'date'=>now()->toDateString()]) }}">出席</a>
          <a class="btn btn-outline-success btn-sm"
             href="{{ route('teacher.grades.index',['subject'=>$subject->id]) }}">成績</a>
          <a class="btn btn-outline-dark btn-sm"
             href="{{ route('teacher.enrollments.index',['subject'=>$subject->id]) }}">履修</a>
        </div>
      </div>
    @endforeach
  </div>
  <div class="mt-3">{{ $subjects->links() }}</div>
@endif
@endsection