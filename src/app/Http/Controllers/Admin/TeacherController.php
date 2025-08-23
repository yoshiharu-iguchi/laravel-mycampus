<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        
        $keyword = $request->input('keyword');

        if ($keyword) {
            $teachers = Teacher::where('name','like',"%{$keyword}%")->paginate(15);
        } else {
            $teachers = Teacher::paginate(15);
        }

        $total = $teachers->total();

        return view('admin.teachers.index',compact('teachers','keyword','total'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function show(Teacher $teacher)
    {
        return view('admin.teachers.show',compact('teacher'));
    }
    public function create()
    {
        return view('admin.teachers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:teachers,email'],
            'password' => ['required','string','min:8'],
        ]);

        Teacher::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.teachers.index')->with('status','教員を登録しました。');
    }



    /**
     * Display the specified resource.
     */
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher)
    {
        return view('admin.teachers.edit',compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255',
            Rule::unique('teachers','email')->ignore($teacher->id),],
            'password' => ['nullable','string','min:8'],

        ]);
        $teacher->name = $request->name;
        $teacher->email = $request->email;

        if ($request->filled('password')){
            $teacher->password = Hash::make($request->password);
        }

        $teacher->save();

        return redirect()->route('admin.teachers.show',$teacher->id)->with('status','教員情報を更新しました。');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return redirect()->route('admin.teachers.index')->with('status','教員を削除しました。');
    }
}
