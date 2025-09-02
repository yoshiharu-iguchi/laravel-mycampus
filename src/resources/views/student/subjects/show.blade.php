{{-- resources/views/student/subjects/show.blade.php（任意） --}}
<x-app-layout>
    <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">{{ $subject->name_ja }}</h2>
    </x-slot>
<div class="container py-4">
  <h1 class="h4 mb-3">{{ $subject->name_ja }} ({{ $subject->subject_code }})</h1>
  <p>単位: {{ $subject->credits }} / 定員: {{ $subject->capacity ?? '-' }}</p>
  <p class="text-muted">{{ $subject->description }}</p>
  <form method="POST" action="{{ route('student.enrollments.store') }}">
    @csrf
    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
    <input type="hidden" name="year" value="{{ now()->year }}">
    <select name="term" class="form-select w-auto d-inline">
      <option>前期</option><option>後期</option><option>通年</option>
    </select>
    <button class="btn btn-primary ms-2">履修登録</button>
  </form>
</div>
</x-app-layout>
