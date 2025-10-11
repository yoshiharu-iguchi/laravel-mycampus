@extends('layouts.teacher')
@section('page-title', ($subject->name_ja ?? '名称未設定').' | 出席')

@push('head')
  @once
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  @endonce
@endpush

@section('actions')
  <a class="btn btn-sm btn-outline-dark" href="{{ route('teacher.subjects.show',$subject) }}">
    科目詳細へ戻る
  </a>
@endsection

@section('teacher-content')
<meta id="attendance-status"
      data-present="{{ \App\Models\Attendance::STATUS_PRESENT }}"
      data-absent="{{ \App\Models\Attendance::STATUS_ABSENT }}"
      data-late="{{ \App\Models\Attendance::STATUS_LATE }}"
      data-excused="{{ \App\Models\Attendance::STATUS_EXCUSED }}"
      data-unrec="{{ \App\Models\Attendance::STATUS_UNRECORDED }}">

  {{-- 日付切替（カード化） --}}
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <form class="row gy-2 gx-2 align-items-end"
            method="get"
            action="{{ route('teacher.attendances.bySubject', ['subject' => $subject->id]) }}">
        <div class="col-auto">
          <label class="form-label mb-0">日付</label>
          <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm">
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

  {{-- 一括更新（カード＋枠線テーブル） --}}
  <form method="POST" action="{{ route('teacher.attendances.bulkUpdate', ['subject' => $subject->id]) }}">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">

    <div class="card shadow-sm">
      <div class="card-header d-flex align-items-center gap-2">
        <span class="fw-semibold"><i class="fa-solid fa-users-rectangle me-1"></i> 出席入力</span>
        <div class="ms-auto d-flex flex-wrap gap-2">
          <button class="btn btn-sm btn-outline-success" type="button" data-fill="present">
            <i class="fa-solid fa-check"></i> 全員 出席
          </button>
          <button class="btn btn-sm btn-outline-danger" type="button" data-fill="absent">
            <i class="fa-solid fa-xmark"></i> 全員 欠席
          </button>
          <button class="btn btn-sm btn-outline-secondary" type="button" data-fill="unrec">
            <i class="fa-regular fa-circle"></i> 全員 未記録
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
                <th style="width:28%">ステータス</th>
              </tr>
            </thead>
            <tbody>
            @forelse($rows as $i => $rec)
              @php $val = (int)($rec->status ?? \App\Models\Attendance::STATUS_UNRECORDED); @endphp
              <tr>
                <td class="text-nowrap">{{ $rec->student->student_number ?? '' }}</td>
                <td class="text-nowrap">{{ $rec->student->name }}</td>
                <td>
                  <input type="hidden" name="rows[{{ $i }}][student_id]" value="{{ $rec->student_id }}">
                  <select name="rows[{{ $i }}][status]" class="form-select form-select-sm status-select">
                    <option value="{{ \App\Models\Attendance::STATUS_PRESENT    }}" @selected($val===\App\Models\Attendance::STATUS_PRESENT)>出席</option>
                    <option value="{{ \App\Models\Attendance::STATUS_ABSENT     }}" @selected($val===\App\Models\Attendance::STATUS_ABSENT)>欠席</option>
                    <option value="{{ \App\Models\Attendance::STATUS_LATE       }}" @selected($val===\App\Models\Attendance::STATUS_LATE)>遅刻</option>
                    <option value="{{ \App\Models\Attendance::STATUS_EXCUSED    }}" @selected($val===\App\Models\Attendance::STATUS_EXCUSED)>公欠</option>
                    <option value="{{ \App\Models\Attendance::STATUS_UNRECORDED }}" @selected($val===\App\Models\Attendance::STATUS_UNRECORDED)>未記録</option>
                  </select>
                </td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-muted">在籍学生がいません。</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-footer d-flex gap-2 justify-content-between align-items-center">
        <div class="small text-muted">
          <i class="fa-solid fa-circle text-success"></i> 出席　
          <i class="fa-solid fa-circle text-danger"></i> 欠席　
          <i class="fa-solid fa-circle text-warning"></i> 遅刻　
          <i class="fa-regular fa-circle text-secondary"></i> 未記録
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
  // Blade値は data-* から取得（エディタの誤検知回避）
  const meta = document.getElementById('attendance-status');
  const STATUS = {
    PRESENT: parseInt(meta.dataset.present, 10),
    ABSENT:  parseInt(meta.dataset.absent, 10),
    LATE:    parseInt(meta.dataset.late, 10),
    EXCUSED: parseInt(meta.dataset.excused, 10),
    UNREC:   parseInt(meta.dataset.unrec, 10),
  };

  // 一括ボタン
  document.querySelectorAll('[data-fill]').forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.getAttribute('data-fill');
      const value = (type === 'present') ? STATUS.PRESENT :
                    (type === 'absent')  ? STATUS.ABSENT  : STATUS.UNREC;
      document.querySelectorAll('select.status-select').forEach(sel => {
        sel.value = value;
        tintRow(sel);
      });
    });
  });

  // 行の色分け
  function tintRow(select){
    const tr = select.closest('tr');
    tr.classList.remove('table-success','table-danger','table-warning','table-light');
    switch (parseInt(select.value,10)) {
      case STATUS.PRESENT: tr.classList.add('table-success'); break;
      case STATUS.ABSENT:  tr.classList.add('table-danger');  break;
      case STATUS.LATE:    tr.classList.add('table-warning'); break;
      case STATUS.UNREC:   tr.classList.add('table-light');   break;
    }
  }

  // 初期色付け＋変更時
  document.querySelectorAll('select.status-select').forEach(sel => {
    tintRow(sel);
    sel.addEventListener('change', () => tintRow(sel));
  });
})();
</script>
@endpush