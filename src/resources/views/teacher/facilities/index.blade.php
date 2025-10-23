@extends('layouts.teacher')
@section('page-title','実習施設一覧')

@section('teacher-content')
  
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <form class="row g-2" method="get" action="{{ route('teacher.facilities.index') }}">
        <div class="col-md-6">
          <input type="text" name="q" value="{{ $kw ?? '' }}" class="form-control" placeholder="施設名・住所・最寄駅で検索">
        </div>
        <div class="col-auto">
          <button class="btn btn-outline-secondary"><i class="fa-solid fa-magnifying-glass"></i> 検索</button>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ route('teacher.facilities.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> 新規登録
          </a>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-sm table-hover table-bordered align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:28%">施設名</th>
            <th style="width:32%">住所</th>
            <th style="width:20%">最寄駅</th>
            <th style="width:20%" class="text-center">操作</th>
          </tr>
        </thead>
        <tbody>
        @forelse($facilities as $f)
          <tr>
            <td class="text-nowrap">{{ $f->name }}</td>
            <td>{{ $f->address ?: '—' }}</td>
            <td class="text-nowrap">{{ $f->nearest_station ?: '—' }}</td>
            <td class="text-center">
              <a href="{{ route('teacher.facilities.edit',$f) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-pen-to-square"></i> 編集
              </a>
              <form method="POST" action="{{ route('teacher.facilities.destroy',$f) }}" class="d-inline"
                    onsubmit="return confirm('削除してよろしいですか？');">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">
                  <i class="fa-solid fa-trash"></i> 削除
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-muted text-center py-4">施設がありません。</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $facilities->links() }}
    </div>
  </div>
@endsection