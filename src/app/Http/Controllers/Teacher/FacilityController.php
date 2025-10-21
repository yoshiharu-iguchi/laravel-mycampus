<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $q = Facility::query()->orderBy('name');
        if ($kw = trim((string)$request->input('q'))) {
            $q->where(function($qq) use ($kw){
                $qq->where('name','like',"%{$kw}%")
                   ->orWhere('address','like',"%{$kw}%")
                   ->orWhere('nearest_station','like',"%{$kw}%");
            });
        }
        $facilities = $q->paginate(20)->withQueryString();

        return view('teacher.facilities.index', compact('facilities','kw'));
    }

    public function create()
    {
        $facility = new Facility();
        return view('teacher.facilities.create', compact('facility'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required','string','max:255'],
            'address'         => ['nullable','string','max:255'],
            'nearest_station' => ['nullable','string','max:255'],
        ]);

        Facility::create($data);

        return redirect()
            ->route('teacher.facilities.index')
            ->with('status','施設を登録しました。');
    }

    public function edit(Facility $facility)
    {
        return view('teacher.facilities.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        $data = $request->validate([
            'name'            => ['required','string','max:255'],
            'address'         => ['nullable','string','max:255'],
            'nearest_station' => ['nullable','string','max:255'],
        ]);

        $facility->update($data);

        return redirect()
            ->route('teacher.facilities.index')
            ->with('status','施設を更新しました。');
    }

    public function destroy(Facility $facility)
    {
        $facility->delete();

        return redirect()
            ->route('teacher.facilities.index')
            ->with('status','施設を削除しました。');
    }
}