<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentDetail;
use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssessmentAdminController extends Controller
{
    // 1. Daftar Kategori Penilaian
    public function categories()
    {
        $categories = AssessmentCategory::all();
        return view('admin.assessment.categories', compact('categories'));
    }

    // 2. Simpan Kategori Baru
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'        => 'required',
            'description' => 'required'
        ]);

        AssessmentCategory::create([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => true
        ]);

        return back()->with('success', 'Kategori berhasil ditambah');
    }

    // 3. Edit Kategori
    public function editCategory(AssessmentCategory $category)
    {
        return view('admin.assessment.edit_category', compact('category'));
    }

    // 4. Update Kategori
    public function updateCategory(Request $request, AssessmentCategory $category)
    {
        $request->validate([
            'name'        => 'required',
            'description' => 'required'
        ]);

        $category->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.assessment.categories')
            ->with('success', 'Kategori berhasil diperbarui');
    }

    // 5. Toggle Aktif/Nonaktif Kategori
    public function toggleCategory(AssessmentCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Kategori \"{$category->name}\" berhasil {$status}");
    }

    // 6. Hapus Kategori
    public function destroyCategory(AssessmentCategory $category)
    {
        if ($category->assessmentDetails()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena sudah digunakan. Nonaktifkan saja.');
        }

        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus');
    }

    // 7. Daftar Karyawan untuk Dinilai
    public function listEmployees()
    {
        $employees = Karyawan::with(['user', 'departemen'])->get();
        return view('admin.assessment.employees', compact('employees'));
    }

    // 8. Form Penilaian
    public function create($id)
    {
        // Cari user yang mau dinilai
        $target     = User::with('karyawan')->findOrFail($id);
        // Ambil indikator yang aktif
        $categories = AssessmentCategory::where('is_active', true)->get();

        if ($categories->isEmpty()) {
            return redirect()->route('admin.assessment.categories')
                ->with('error', 'Buat kategori penilaian dulu!');
        }

        return view('admin.assessment.create', compact('target', 'categories'));
    }

    // 9. Simpan Nilai
    public function store(Request $request)
    {
        $request->validate([
            'evaluatee_id' => 'required|exists:users,id',
            'scores'       => 'required|array',
            'scores.*'     => 'required|integer|min:1|max:5',
        ]);

        DB::beginTransaction();
        try {
            $assessment = Assessment::create([
                'evaluator_id'    => Auth::id(),
                'evaluatee_id'    => $request->evaluatee_id,
                'assessment_date' => now()->toDateString(),
                'period'          => now()->format('F Y'),
                'period_type'     => $request->period_type ?? 'monthly',
                'general_notes'   => $request->notes ?? '-',
            ]);

            foreach ($request->scores as $catId => $score) {
                AssessmentDetail::create([
                    'assessment_id' => $assessment->id,
                    'category_id'   => $catId,
                    'score'         => $score,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.assessment.report')
                ->with('success', 'Penilaian berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    // 10. Laporan Hasil Penilaian
    public function overallReport(Request $request)
    {
        // Ambil semua periode yang tersedia untuk dropdown filter
        $periods = Assessment::select('period')
            ->distinct()
            ->orderBy('period', 'desc')
            ->pluck('period');

        // Query dengan filter periode jika dipilih
        $query = Assessment::with(['user.karyawan', 'details.category'])->latest();

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $allAssessments = $query->get();

        // Hitung rata-rata per kategori
        $avgPerCategory = $allAssessments
            ->flatMap(fn($a) => $a->details)
            ->groupBy(fn($d) => $d->category->name ?? 'Unknown')
            ->map(fn($details, $categoryName) => [
                'category' => $categoryName,
                'average'  => round($details->avg('score'), 2),
                'total'    => $details->count(),
            ])
            ->values();

        return view('admin.assessment.report', compact('allAssessments', 'periods', 'avgPerCategory'));
    }
}