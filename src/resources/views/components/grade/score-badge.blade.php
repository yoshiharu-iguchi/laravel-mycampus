@props([
  // 数値スコア（0〜100想定）または null
  'score' => null,
  // 文字評価（必要なら）。score が無い時のフォールバック
  'evaluation' => null,
  // しきい値（教員側に合わせる）
  'high' => 80,
  'mid'  => 60,
])

@php
  $class = 'bg-secondary-subtle text-secondary';
  $text  = '—';

  if (is_numeric($score)) {
    $s = (float) $score;
    $text = (string) (int) $s;

    if ($s >= $high)      $class = 'bg-success-subtle text-success fw-semibold';
    elseif ($s >= $mid)   $class = 'bg-warning-subtle text-warning fw-semibold';
    else                  $class = 'bg-danger-subtle text-danger fw-semibold';
  } elseif (!is_null($evaluation) && $evaluation !== '') {
    // 文字評価のみの場合（必要なら調整）
    $e = mb_strtoupper(trim($evaluation));
    if (in_array($e, ['S','A'], true))      $class='bg-success-subtle text-success fw-semibold';
    elseif ($e === 'B')                     $class='bg-warning-subtle text-warning fw-semibold';
    else                                    $class='bg-danger-subtle text-danger fw-semibold';
    $text = $evaluation;
  }
@endphp

<span class="badge {{ $class }}">{{ $text }}</span>