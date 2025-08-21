<?



namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentController extends Controller
{
    // 検索ボックスに入力されたキーワードを取得する
    public function index(Request $request) {
        
        $keyword = $request->input('keyword');

        if ($keyword) {
            $students = Student::where('name','like',"%{$keyword}%")->paginate(15);
        } else {
            $students = Student::paginate(15);
        }

        $total = $students->total();

        return view('admin.student.index',compact('students','keyword','total'));
    }

    public function show(Student $student) {
        return view('admin.students.show',compact('student'));
    }
}