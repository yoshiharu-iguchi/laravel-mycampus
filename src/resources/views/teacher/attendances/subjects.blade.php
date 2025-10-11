@extends('layouts.app')
@section('title','出席（担当科目一覧）')

@section('content')
  <h1>出席（担当科目一覧）</h1>
  <ul>
    @forelse($subjects as $s)
      <li>
        <a href="{{ route('teacher.attendances.index', ['subject' => $s->id]) }}">
          {{ $s->name_ja }}
        </a>
      </li>
    @empty
      <li>担当科目はありません</li>
    @endforelse
  </ul>
@endsection