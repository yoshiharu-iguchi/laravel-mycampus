@extends('layouts.student')
@section('title','成績一覧 | MyCampus')
@section('content')
  <x-grade.table :grades="$grades" title="成績一覧" />
@endsection