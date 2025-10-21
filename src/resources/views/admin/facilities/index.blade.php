@extends('layouts.admin')
@section('title','実習施設')

@section('actions')
  <a class="btn btn-sm btn-primary" href="{{ route('admin.facilities.create') }}">
    新規登録
  </a>
@endsection

@section('content')
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>施設名</th>
              <th>住所</th>
              <th>最寄駅</th>
              <th style="width:160px;">操作</th>
            </tr>
          </thead>
          <tbody>
            @forelse($facilities as $f)
              <tr>
                <td class="text-nowrap">{{ $f->name }}</td>
                <td class="text-nowrap">{{ $f->address ?? '—' }}</td>
                <td class="text-nowrap">{{ $f->nearest_station ?? '—' }}</td>
                <td>
                  <a class="btn btn-sm btn-outline-secondary"
                     href="{{ route('admin.facilities.edit',$f) }}">編集</a>
                  <form class="d-inline"
                        method="POST"
                        action="{{ route('admin.facilities.destroy',$f) }}"
                        onsubmit="return confirm('削除しますか？')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">削除</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted">施設がありません</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if($facilities->hasPages())
      <div class="card-footer">{{ $facilities->links() }}</div>
    @endif
  </div>
@endsection