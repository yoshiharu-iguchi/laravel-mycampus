@extends('layouts.student')
@section('page-title','履修登録済み科目一覧')

@section('student-content')
  {{-- フラッシュとエラーはレイアウト側で表示済み --}}
  <div class="small text-muted mb-2">
    全 {{ number_format($enrollments->total()) }} 件
    @if($enrollments->count())
      ／ 表示 {{ number_format($enrollments->firstItem()) }}–{{ number_format($enrollments->lastItem()) }} 件
    @endif
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>科目コード</th>
            <th>科目名</th>
            <th>年度</th>
            <th>学期</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
        @forelse($enrollments as $enrollment)
          <tr>
            <td>{{ $enrollment->id }}</td>
            <td>{{ $enrollment->subject->subject_code }}</td>
            <td>{{ $enrollment->subject->name_ja ?? $enrollment->subject->name_en ?? '名称未設定' }}</td>
            <td>{{ $enrollment->year ?? '—' }}</td>
            <td>{{ $enrollment->term ?? '—' }}</td>
            <td class="text-end">
              <form method="POST" action="{{ route('student.enrollments.destroy', $enrollment) }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"
                  onclick="return confirm('本当に取り消しますか？')">取消</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-5">現在、履修登録している科目はありません。</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $enrollments->links() }}
  </div>
@endsection