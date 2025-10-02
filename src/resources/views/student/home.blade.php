@extends('layouts.student')

@section('title', 'ホーム')

@section('student-content')
  @php
    // 既存の $student が渡ってくるケースにも配慮
    $s = isset($student) ? $student : auth('student')->user();
  @endphp

  {{-- ステータス表示 --}}
  @if (session('status'))
    <div class="alert alert-success mb-3">{{ session('status') }}</div>
  @endif

  {{-- メール未認証の案内（verifiedミドルウェア未使用時） --}}
  @if ($s && method_exists($s, 'hasVerifiedEmail') && ! $s->hasVerifiedEmail())
    <div class="alert alert-warning mb-3">
      メールアドレスが未確認です。確認メールを再送するにはボタンを押してください。
      <form method="POST" action="{{ route('student.verification.send') }}" class="d-inline">
        @csrf
        <button class="btn btn-sm btn-dark ms-2">確認メールを再送する</button>
      </form>
      <a href="{{ route('student.verification.notice') }}" class="btn btn-sm btn-outline-dark ms-2">認証手順を表示</a>
    </div>
  @else
    <div class="alert alert-success mb-3">メール認証済みです。</div>
  @endif

  {{-- プロフィール & アカウント情報 --}}
  <div class="row g-3">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h2 class="h6 mb-3">学生情報</h2>
          @if($s)
            <div><strong>氏名：</strong>{{ $s->name }}</div>
            @isset($s->student_number)
              <div><strong>学籍番号：</strong>{{ $s->student_number }}</div>
            @endisset
            @isset($s->email)
              <div><strong>メール：</strong>{{ $s->email }}</div>
            @endisset
            <div><strong>住所：</strong>{{ $s->address ?? '未登録' }}</div>
          @else
            <div class="text-muted">学生情報を取得できませんでした。</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h2 class="h6 mb-3">アカウント</h2>
          <div>
            <strong>認証状態：</strong>
            @if ($s && $s->email_verified_at) 認証済み（{{ $s->email_verified_at }}）
            @else 未認証
            @endif
          </div>
          <p class="text-muted mt-2 mb-3">下のボタンから学習・履修関連のページへ移動できます。</p>
          <div class="d-flex flex-wrap gap-2">
            {{-- 既存ルートが無ければ後で差し替え・コメントアウトしてください --}}
            <a class="btn btn-outline-dark" href="{{ url('/student/subjects') }}">科目一覧へ</a>
            <a class="btn btn-outline-dark" href="{{ url('/student/enrollments') }}">履修登録科目一覧へ</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ダミー表示：成績 --}}
  <div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
      <h2 class="h6 mb-3">最近の成績（例）</h2>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead class="table-light">
            <tr>
              <th>科目</th>
              <th>評価</th>
              <th>更新日</th>
            </tr>
          </thead>
          <tbody>
            {{-- 実データに置き換え例：@foreach($s->grades as $g) --}}
            <tr><td>作業療法概論</td><td>A</td><td>2025-07-15</td></tr>
            <tr><td>解剖学</td><td>B+</td><td>2025-07-10</td></tr>
            <tr><td>生理学</td><td>B</td><td>2025-07-03</td></tr>
            {{-- @endforeach --}}
          </tbody>
        </table>
      </div>
      <div class="text-muted small">※ 上記はダミーです。成績モデルやリレーションが整ったら差し替えてください。</div>
    </div>
  </div>

  {{-- ダミー表示：出欠 --}}
  <div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
      <h2 class="h6 mb-3">出欠状況（例）</h2>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead class="table-light">
            <tr>
              <th>日付</th>
              <th>区分</th>
              <th>備考</th>
            </tr>
          </thead>
          <tbody>
            {{-- 実データ例：@foreach($s->attendances()->latest()->limit(10)->get() as $a) --}}
            <tr><td>2025-07-16</td><td>出席</td><td></td></tr>
            <tr><td>2025-07-15</td><td>遅刻</td><td>電車遅延</td></tr>
            <tr><td>2025-07-14</td><td>欠席</td><td>体調不良</td></tr>
            {{-- @endforeach --}}
          </tbody>
        </table>
      </div>
      <div class="text-muted small">※ こちらもダミーのテーブルです。実装後に置き換えてください。</div>
    </div>
  </div>
@endsection