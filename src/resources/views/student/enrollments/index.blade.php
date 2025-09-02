{{-- resources/views/student/enrollments/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">履修科目</h2>
    </x-slot>
<div class="container py-4">
  <h1 class="h4 mb-3">履修科目</h1>
  @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
  <table class="table table-sm align-middle">
    <thead><tr><th>コード</th><th>名称</th><th>年度</th><th>学期</th><th></th></tr></thead>
    <tbody>
    @forelse($enrollments as $e)
      <tr>
        <td>{{ $e->subject->subject_code }}</td>
        <td>{{ $e->subject->name_ja }}</td>
        <td>{{ $e->year }}</td>
        <td>{{ $e->term }}</td>
        <td>
          <form method="POST" action="{{ route('student.enrollments.destroy',$e) }}">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm">取り消し</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="5" class="text-muted">まだ履修はありません。</td></tr>
    @endforelse
    </tbody>
  </table>
  {{ $enrollments->links() }}
</div>
</x-app-layout>