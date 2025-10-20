@extends('layouts.student')
@section('title','出席一覧 | MyCampus')
@section('content')
  <x-attendance.subject-summary
    title="科目別の出席状況"
    :rows="$rows"
    :showLegend="true"
    :showScore="false"
  />
@endsection