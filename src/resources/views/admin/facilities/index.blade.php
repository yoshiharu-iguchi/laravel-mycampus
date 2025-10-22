@extends('layouts.admin')
@section('title','Facilities list')

@section('content')
  <div class="d-flex align-items-center mb-3">
    <a href="{{ route('admin.facilities.create') }}" class="btn btn-primary ms-auto">
      <i class="bi bi-plus-lg me-1"></i>新規登録
    </a>
  </div>

  @if($facilities->isEmpty())
    <div class="alert alert-info mb-0">登録済みの施設はありません。</div>
  @else
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:28%">施設名称</th>
            <th>住所</th>
            <th style="width:20%">最寄駅</th>
            <th style="width:18%" class="text-end">編集</th>
          </tr>
        </thead>
        <tbody>
          @foreach($facilities as $f)
            <tr>
              <td class="text-nowrap">{{ $f->name }}</td>
              <td class="text-nowrap">{{ $f->address ?? '—' }}</td>
              <td class="text-nowrap">{{ $f->nearest_station ?? '—' }}</td>
              <td class="text-end">
                <a href="{{ route('admin.facilities.edit',$f) }}" class="btn btn-sm btn-outline-secondary">編集</a>
                <form action="{{ route('admin.facilities.destroy',$f) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('削除してよろしいですか？');">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">削除</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="mt-3">{{ $facilities->links() }}</div>
  @endif
@endsection