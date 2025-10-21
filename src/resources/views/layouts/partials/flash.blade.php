@php
    $msgs = collect([
        ['type' => 'success', 'text' => session('status')],
        ['type' => 'success', 'text' => session('success')],
        ['type' => 'info',    'text' => session('info')],
        ['type' => 'warning', 'text' => session('warning')],
        ['type' => 'danger',  'text' => session('error')],
    ])->filter(fn($x) => filled($x['text']))
      ->unique('text');
@endphp

@foreach($msgs as $m)
  <div class="alert alert-{{ $m['type'] }} alert-dismissible fade show" role="alert">
    {{ $m['text'] }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endforeach