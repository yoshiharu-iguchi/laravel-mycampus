@php
  $map = ['status'=>'success','success'=>'success','info'=>'info','warning'=>'warning','error'=>'danger','danger'=>'danger'];
@endphp
@foreach($map as $key => $bs)
  @if(session()->has($key))
    @php($msg = session()->pull($key))
    @if($msg)
      <div class="alert alert-{{ $bs }} small mb-2" role="alert">
        {{ is_array($msg) ? implode(' ', $msg) : $msg }}
      </div>
    @endif
  @endif
@endforeach