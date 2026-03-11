<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentCategory;
use Illuminate\Http\Request;

class AssessmentCategoryController extends Controller
{
    public function index()
    {
        $categories = AssessmentCategory::latest()->get();
        return view('admin.assessment.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:assessment_categories,name',
            'type'        => 'required|in:Employee,Student',
            'description' => 'nullable|string|max:500',
        ]);

        AssessmentCategory::create([
            'name'        => $request->name,
            'type'        => $request->type,
            'description' => $request->description,
            'is_active'   => true,
        ]);

        return back()->with('success', 'Kategori Penilaian Berhasil Ditambah!');
    }

    public function update(Request $request, $id)
    {
        $category = AssessmentCategory::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:100|unique:assessment_categories,name,' . $id,
            'type'        => 'required|in:Employee,Student',
            'description' => 'nullable|string|max:500',
        ]);

        $category->update([
            'name'        => $request->name,
            'type'        => $request->type,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Kategori Berhasil Diupdate!');
    }

    public function toggleActive($id)
    {
        $category = AssessmentCategory::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Kategori \"{$category->name}\" berhasil {$status}.");
    }

    public function destroy($id)
    {
        $category = AssessmentCategory::findOrFail($id);

        // Cek apakah kategori sudah dipakai di penilaian
        if ($category->assessmentDetails()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena sudah digunakan. Nonaktifkan saja.');
        }

        $category->delete();
        return back()->with('success', 'Kategori Berhasil Dihapus!');
    }
}