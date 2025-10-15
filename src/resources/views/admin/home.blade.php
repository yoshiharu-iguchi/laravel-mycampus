{{-- resources/views/admin/home.blade.php --}}
@extends('layouts.admin')

@section('title','ダッシュボード')

@section('actions')
  {{-- 右上アクション（必要なら） --}}
  <a href="{{ route('admin.enrollments.index') }}" class="btn btn-sm btn-primary">
    履修一覧へ
  </a>
@endsection

@section('content')
  {{-- 概要カード --}}
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">ログイン中の管理者</div>
          <div class="fw-semibold">{{ optional(auth('admin')->user())->email }}</div>
        </div>
      </div>
    </div>
    @isset($pendingCount)
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="text-muted small mb-1">承認待ちの経路申請</div>
            <div class="h5 mb-0">{{ $pendingCount ?? '—' }}</div>
          </div>
        </div>
      </div>
    @endisset
  </div>

  {{-- クイックリンク --}}
  <div class="card">
    <div class="card-header">クイックリンク</div>
    <div class="list-group list-group-flush">
      <a class="list-group-item list-group-item-action"
         href="{{ route('admin.students.index') }}">
        <i class="bi bi-people me-2"></i>学生一覧
      </a>
      <a class="list-group-item list-group-item-action"
         href="{{ route('admin.subjects.index') }}">
        <i class="bi bi-journal-text me-2"></i>科目一覧
      </a>
      <a class="list-group-item list-group-item-action"
        href="{{ route('admin.enrollments.index') }}">
        <i class="bi bi-card-checklist me-2"></i>履修登録一覧
      </a>

      <a class="list-group-item list-group-item-action"
         href="{{ route('admin.teachers.index') }}">
        <i class="bi bi-person-badge me-2"></i>教員一覧
      </a>
      <a class="list-group-item list-group-item-action"
         href="{{ route('admin.tr.index', ['status' => 'pending']) }}">
        <i class="bi bi-ticket-detailed me-2"></i>経路申請（申請中）
        @if(isset($pendingCount) && $pendingCount !== null)
          <span class="badge bg-warning text-dark ms-2">{{ $pendingCount }}</span>
        @endif
      </a>
    </div>
  </div>
@endsection