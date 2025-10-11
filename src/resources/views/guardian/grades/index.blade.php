@extends('layouts.guardian')
@section('title','成績一覧（お子さま） | MyCampus')
@section('content')
  <div class="card mc-card"><div class="card-body">
    <h2 class="h5 mb-3"><i class="bi bi-mortarboard"></i> 成績一覧（お子さま）</h2>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr><th>科目</th><th>担当</th><th>評価</th><th>更新日</th></tr>
        </thead>
        <tbody>
          @forelse($grades as $g)
            <tr>
              <td>—</td>
              <td>—</td>
              <td>—</td>
              <td>—</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-muted">データがありません</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div></div>
@endsection