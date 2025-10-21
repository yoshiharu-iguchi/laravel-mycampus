{{-- resources/views/teacher/home.blade.php --}}
@extends('layouts.teacher')
@section('title','教員ホーム | MyCampus')

@section('content')
  <div class="card p-3 mb-3">
    <h1 class="h5 mb-1"><i class="bi bi-house-door me-1"></i> 教員ホーム</h1>
    <p class="text-muted mb-0">ようこそ、{{ Auth::guard('teacher')->user()->name }} さん。</p>
  </div>

  <div class="card shadow-sm">
    <div class="card-header">
      <span class="fw-semibold"><i class="bi bi-journal-text me-1"></i> 担当科目</span>
    </div>
    <div class="card-body">
      @if($subjects->isEmpty())
        <div class="text-muted">担当科目がありません。</div>
      @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
          @foreach($subjects as $subject)
            @php
              $name = $subject->name_ja ?? $subject->name_en ?? '名称未設定';
              $code = $subject->subject_code ?? '—';
              $enrolled = (int)($subject->students_count ?? 0);
            @endphp
            <div class="col">
              <div class="card h-100">
                <div class="card-body d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <div class="small text-muted">科目コード: {{ $code }}</div>
                      <div class="fw-semibold">{{ $name }}</div>
                    </div>
                    <span class="badge bg-secondary">{{ $enrolled }}名</span>
                  </div>

                  <div class="mt-3 d-flex flex-wrap gap-2">
                    {{-- 詳細 --}}
                    <a class="btn btn-sm btn-outline-secondary"
                       href="{{ route('teacher.subjects.show', $subject) }}">
                      詳細
                    </a>
                    {{-- 出席（= index.blade と同じ引数: subject / date） --}}
                    <a class="btn btn-sm btn-outline-primary"
                       href="{{ route('teacher.attendances.index', ['subject'=>$subject->id, 'date'=>now()->toDateString()]) }}">
                      出席
                    </a>
                    {{-- 成績（= index.blade と同じ引数: subject） --}}
                    <a class="btn btn-sm btn-outline-success"
                       href="{{ route('teacher.grades.index', ['subject'=>$subject->id]) }}">
                      成績
                    </a>
                    {{-- 履修（= index.blade と同じ引数: subject） --}}
                    <a class="btn btn-sm btn-outline-dark"
                       href="{{ route('teacher.enrollments.index', ['subject'=>$subject->id]) }}">
                      履修
                    </a>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
@endsection