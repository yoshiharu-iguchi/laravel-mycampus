<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $facilities = Facility::orderBy('name')->paginate(20);
        return view('admin.facilities.index', compact('facilities'));
    }

    public function create()
    {
        $facility = new Facility();
        return view('admin.facilities.create', compact('facility'));
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
            ->route('admin.facilities.index')
            ->with('status','施設を登録しました');
    }

    public function edit(Facility $facility)
    {
        return view('admin.facilities.edit', compact('facility'));
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
            ->route('admin.facilities.index')
            ->with('status','施設を更新しました');
    }

    public function destroy(Facility $facility)
    {
        $facility->delete();

        return back()->with('status','施設を削除しました');
    }
}
