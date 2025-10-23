{{-- resources/views/partials/flash.blade.php --}}
@php
  $detail = session('flash_detail'); // ['type'=>'success|danger|info|warning', 'title'=>'', 'body'=>'']
@endphp

{{-- 1) バリデーションエラーがあれば、それだけ出す --}}
@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="fw-bold mb-1">入力内容をご確認ください</div>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

{{-- 2) flash_detail があれば、それだけ出す --}}
@elseif ($detail && filled($detail['body'] ?? null))
  <div class="alert alert-{{ $detail['type'] ?? 'info' }} alert-dismissible fade show" role="alert">
    @if(!empty($detail['title']))
      <div class="fw-bold mb-1">{{ $detail['title'] }}</div>
    @endif
    <div>{{ $detail['body'] }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

{{-- 3) 上記がなければ、優先度順で最初の“埋まっている”1件だけ表示 --}}
@else
  @php
    $candidates = [
      ['type' => 'danger',  'text' => session('error')],
      ['type' => 'warning', 'text' => session('warning')],
      ['type' => 'success', 'text' => session('success')],
      ['type' => 'success', 'text' => session('status')],
      ['type' => 'info',    'text' => session('info')],
    ];
    $msg = collect($candidates)->first(fn($m) => filled($m['text']));
  @endphp

  @if($msg)
    <div class="alert alert-{{ $msg['type'] }} alert-dismissible fade show" role="alert">
      {{ $msg['text'] }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
@endif