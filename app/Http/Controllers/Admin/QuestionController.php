<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AssessmentQuestion, AssessmentCategory};
use Illuminate\Http\Request;

class QuestionController extends Controller {
    public function index() {
        $questions = AssessmentQuestion::with('category')->orderBy('category_id')->get();
        $categories = AssessmentCategory::all();
        return view('admin.assessment.questions', compact('questions', 'categories'));
    }

    public function store(Request $request) {
        $request->validate(['category_id' => 'required', 'question' => 'required']);
        AssessmentQuestion::create($request->all());
        return back()->with('success', 'Pertanyaan berhasil ditambah!');
    }

    public function update(Request $request, $id) {
        AssessmentQuestion::findOrFail($id)->update($request->all());
        return back()->with('success', 'Pertanyaan berhasil diupdate!');
    }

    public function destroy($id) {
        AssessmentQuestion::findOrFail($id)->delete();
        return back()->with('success', 'Pertanyaan dihapus!');
    }
}
