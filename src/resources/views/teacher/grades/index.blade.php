@extends('layouts.teacher')
@section('page-title', ($subject->name_ja ?? '名称未設定').' | 成績')

@push('head')
  {{-- Font Awesome --}}
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

{{-- 右上のアクション --}}
@section('actions')
  <a class="btn btn-sm btn-outline-dark" href="{{ route('teacher.subjects.show',$subject) }}">
    <i class="fa-solid fa-book-open"></i> 科目詳細へ戻る
  </a>
@endsection

@section('teacher-content')
  {{-- JS が読むしきい値（エディタの赤線回避のため data-* で埋め込む） --}}
  <meta id="grade-threshold"
        data-high="80" data-mid="60">

  {{-- 日付切替（= 評価日） --}}
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <form class="row gy-2 gx-2 align-items-end"
            method="get"
            action="{{ route('teacher.grades.index') }}">
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <div class="col-auto">
          <label class="form-label mb-0">評価日</label>
          <input type="date" name="evaluation_date" value="{{ $date }}" class="form-control form-control-sm">
        </div>
        <div class="col-auto">
          <button class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-rotate"></i> 表示
          </button>
        </div>
        <div class="col-auto ms-auto">
          <a class="btn btn-sm btn-outline-dark" href="{{ route('teacher.subjects.show',$subject) }}">
            <i class="fa-solid fa-book-open"></i> 科目詳細へ戻る
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- フラッシュ/エラー --}}
  @includeFirst(['layouts.partials.flash','partials.flash'])
  @includeFirst(['layouts.partials.errors','partials.errors'])

  {{-- 一括更新 --}}
  <form method="POST" action="{{ route('teacher.grades.bulkUpdate') }}">
    @csrf
    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
    <input type="hidden" name="evaluation_date" value="{{ $date }}">

    <div class="card shadow-sm">
      <div class="card-header d-flex align-items-center gap-2">
        <span class="fw-semibold"><i class="fa-solid fa-clipboard-list me-1"></i> 成績入力</span>
        <div class="ms-auto d-flex flex-wrap gap-2">
          <button class="btn btn-sm btn-outline-secondary" type="button" data-fill="empty">
            <i class="fa-regular fa-circle"></i> 全員 未入力
          </button>
          <button class="btn btn-sm btn-outline-success" type="button" data-fill="100">
            <i class="fa-solid fa-arrow-up-9-1"></i> 全員 100
          </button>
          <button class="btn btn-sm btn-outline-primary" type="button" data-fill="80">
            <i class="fa-solid fa-arrow-up-wide-short"></i> 全員 80
          </button>
          <button class="btn btn-sm btn-outline-warning" type="button" data-fill="60">
            <i class="fa-solid fa-equals"></i> 全員 60
          </button>
          <button class="btn btn-sm btn-outline-danger" type="button" data-fill="0">
            <i class="fa-solid fa-arrow-down-1-9"></i> 全員 0
          </button>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:14%">学籍番号</th>
                <th>氏名</th>
                <th style="width:16%" class="text-end">スコア</th>
                <th style="width:35%">備考</th>
              </tr>
            </thead>
            <tbody>
            @forelse($grades as $i => $rec)
              {{-- $rec: student_id, student(name, student_number), score, note --}}
              <tr>
                <td class="text-nowrap">{{ $rec->student->student_number ?? '' }}</td>
                <td class="text-nowrap">{{ $rec->student->name }}</td>
                <td>
                  <input type="hidden" name="rows[{{ $i }}][id]" value="{{ $rec->id }}">
                  <input type="number"
                         name="rows[{{ $i }}][score]"
                         value="{{ is_numeric($rec->score ?? null) ? (int)$rec->score : '' }}"
                         class="form-control form-control-sm score-input text-end"
                         min="0" max="100" step="1"
                         placeholder="—">
                </td>
                <td>
                  <input type="text"
                         name="rows[{{ $i }}][note]"
                         value="{{ $rec->note ?? '' }}"
                         class="form-control form-control-sm"
                         maxlength="255"
                         placeholder="任意メモ（例：小テスト#3）">
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-muted">在籍学生がいません。</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-footer d-flex gap-2 justify-content-between align-items-center">
        <div class="small text-muted">
          <i class="fa-solid fa-circle text-success"></i> 80〜：良　
          <i class="fa-solid fa-circle text-warning"></i> 60〜79　
          <i class="fa-solid fa-circle text-danger"></i> 0〜59　
          <i class="fa-regular fa-circle text-secondary"></i> 未入力
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-primary btn-sm">
            <i class="fa-solid fa-floppy-disk"></i> 一括保存
          </button>
          <a class="btn btn-outline-secondary btn-sm" href="{{ route('teacher.subjects.show',$subject) }}">戻る</a>
        </div>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
<script>
(() => {
  // しきい値（配色用）を data-* から取得
  const meta = document.getElementById('grade-threshold');
  const THRESH = {
    HIGH: parseInt(meta.dataset.high, 10), // 80
    MID:  parseInt(meta.dataset.mid, 10),  // 60
  };

  // 一括ボタン（data-fill="0|60|80|100|empty"）
  document.querySelectorAll('[data-fill]').forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.getAttribute('data-fill');
      document.querySelectorAll('input.score-input').forEach(input => {
        if (type === 'empty') {
          input.value = '';
        } else {
          input.value = parseInt(type, 10);
        }
        tintRow(input);
      });
    });
  });

  // 行の色分け
  function tintRow(input){
    const tr = input.closest('tr');
    tr.classList.remove('table-success','table-warning','table-danger','table-light');
    const v = input.value === '' ? null : parseInt(input.value, 10);
    if (v === null || Number.isNaN(v)) {
      tr.classList.add('table-light'); // 未入力
      return;
    }
    if (v >= THRESH.HIGH)      tr.classList.add('table-success');
    else if (v >= THRESH.MID)  tr.classList.add('table-warning');
    else                       tr.classList.add('table-danger');
  }

  // 初期色付け＋変更時
  document.querySelectorAll('input.score-input').forEach(input => {
    tintRow(input);
    input.addEventListener('input', () => tintRow(input));
    input.addEventListener('change', () => tintRow(input));
  });
})();
</script>
@endpush