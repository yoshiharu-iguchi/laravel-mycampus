@extends('layouts.student')
@section('title','実習先一覧')

@section('student-content')
  <h1 class="h5 mb-3">実習先一覧</h1>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if($facilities->isEmpty())
        <p class="text-muted mb-0">登録された実習先はまだありません。</p>
      @else
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead class="table-light">
              <tr><th>名称</th><th>最寄駅</th></tr>
            </thead>
            <tbody>
              @foreach($facilities as $f)
                <tr>
                  <td>{{ $f->name }}</td>
                  <td>{{ $f->nearest_station ?? '-' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-2">{{ $facilities->links() }}</div>
      @endif
    </div>
  </div>
@endsection