<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      学生別履修一覧
    </h2>
    <p class="text-sm text-gray-600 mt-1">
      学生：{{ $student->name }}（{{ $student->student_number }}）
    </p>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left">
              <tr>
                <th class="px-3 py-2">科目</th>
                <th class="px-3 py-2">年度</th>
                <th class="px-3 py-2">学期</th>
                <th class="px-3 py-2">状態</th>
              </tr>
            </thead>
            <tbody>
            @forelse($enrollments as $e)
              <tr class="border-b">
                <td class="px-3 py-2">{{ $e->subject->name_ja ?? $e->subject->name_en ?? '不明' }}</td>
                <td class="px-3 py-2">{{ $e->year }}</td>
                <td class="px-3 py-2">{{ $e->term }}</td>
                <td class="px-3 py-2">{{ $e->status }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-3 py-6 text-center text-gray-500">
                  データがありません。
                </td>
              </tr>
            @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          {{ $enrollments->links() }}
        </div>

        <div class="mt-6">
          <a href="{{ route('admin.enrollments.index') }}"
             class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md">
            一覧に戻る
          </a>
        </div>

      </div>
    </div>
  </div>
</x-app-layout>