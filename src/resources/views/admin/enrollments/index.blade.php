<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      履修一覧（管理）
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

        <form method="GET" class="grid grid-cols-12 gap-3 mb-4">
          <div class="col-span-12 md:col-span-4">
            <select name="subject_id" class="border-gray-300 rounded-md w-full">
              <option value="">科目を選択</option>
              @foreach($subjects as $s)
                <option value="{{ $s->id }}" @selected(request('subject_id')==$s->id)>
                  {{ $s->name_ja ?? $s->name_en ?? '不明' }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-span-6 md:col-span-2">
            <input type="number" name="year" value="{{ request('year') }}" class="border-gray-300 rounded-md w-full" placeholder="年度(YYYY)">
          </div>
          <div class="col-span-6 md:col-span-2">
            <select name="term" class="border-gray-300 rounded-md w-full">
              <option value="">学期</option>
              @foreach(['前期','後期','通年'] as $t)
                <option @selected(request('term')===$t)>{{ $t }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-span-12 md:col-span-3">
            <input type="text" name="keyword" value="{{ request('keyword') }}" class="border-gray-300 rounded-md w-full" placeholder="学生名 or 学籍番号">
          </div>
          <div class="col-span-12 md:col-span-1">
            <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md w-full">検索</button>
          </div>
        </form>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left">
              <tr>
                <th class="px-3 py-2">学生</th>
                <th class="px-3 py-2">学籍番号</th>
                <th class="px-3 py-2">科目</th>
                <th class="px-3 py-2">年度</th>
                <th class="px-3 py-2">学期</th>
                <th class="px-3 py-2">状態</th>
              </tr>
            </thead>
            <tbody>
              @forelse($enrollments as $e)
                <tr class="border-b">
                  <td class="px-3 py-2">
                    <a class="text-blue-600 underline" href="{{ route('admin.enrollments.byStudent', $e->student_id) }}">
                      {{ $e->student->name }}
                    </a>
                  </td>
                  <td class="px-3 py-2">{{ $e->student->student_number }}</td>
                  <td class="px-3 py-2">
                    <a class="text-blue-600 underline" href="{{ route('admin.enrollments.bySubject', $e->subject_id) }}">
                      {{ $e->subject->name_ja ?? $e->subject->name_en ?? '不明' }}
                    </a>
                  </td>
                  <td class="px-3 py-2">{{ $e->year }}</td>
                  <td class="px-3 py-2">{{ $e->term }}</td>
                  <td class="px-3 py-2">{{ $e->status }}</td>
                </tr>
              @empty
                <tr><td class="px-3 py-2" colspan="6">データがありません。</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          {{ $enrollments->links() }}
        </div>

      </div>
    </div>
  </div>
</x-app-layout>