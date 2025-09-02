{{-- resources/views/student/subjects/index.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">科目一覧</h2>
  </x-slot>

  <div class="container py-4">
    {{-- エラー/ステータス表示（診断用） --}}
    @if (session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <table class="table table-sm align-middle">
      <thead>
        <tr><th>コード</th><th>名称</th><th>単位</th><th>定員</th><th></th></tr>
      </thead>
      <tbody>
      @foreach ($subjects as $s)
        <tr>
          <td>{{ $s->subject_code }}</td>
          <td><a href="{{ route('student.subjects.show', $s) }}">{{ $s->name_ja }}</a></td>
          <td>{{ $s->credits }}</td>
          <td>{{ $s->capacity ?? '-' }}</td>
          <td>
            {{-- ← ここにフォーム（各科目ごと） --}}
            <form method="POST" action="{{ route('student.enrollments.store') }}" class="inline">
              @csrf
              <input type="hidden" name="subject_id" value="{{ $s->id }}">
              <input type="hidden" name="year" value="{{ now()->year }}">
              <input type="hidden" name="term" value="{{ $s->term ?? '前期' }}">
              <button class="btn btn-primary btn-sm">履修登録</button>
              {{-- Jetstreamのボタンを使うなら:
              <x-primary-button class="ms-2">履修登録</x-primary-button>
              --}}
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    {{ $subjects->links() }}
  </div>
</x-app-layout>