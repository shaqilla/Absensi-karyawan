<?php
// app/Http/Controllers/Admin/AssessmentController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    /**
     * Menampilkan daftar karyawan yang bisa dinilai
     */
    public function employees()
    {
        // whereHas('user') = hanya ambil karyawan yang punya user (hindari null)
        $employees = \App\Models\Karyawan::with(['user', 'departemen'])
            ->whereHas('user')
            ->get();

        foreach ($employees as $emp) {
            $emp->assessed_this_month = Assessment::where('evaluator_id', auth()->id())
                ->where('evaluatee_id', $emp->user_id)
                ->whereMonth('assessment_date', now()->month)
                ->whereYear('assessment_date', now()->year)
                ->exists();
        }

        return view('admin.assessment.employees', compact('employees'));
    }

    /**
     * Menampilkan form penilaian
     */
    public function create($evaluatee_id)
    {
        if (!is_numeric($evaluatee_id)) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'ID karyawan tidak valid!');
        }

        $target = User::with('karyawan')->where('role', 'karyawan')->find($evaluatee_id);

        if (!$target) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'Karyawan tidak ditemukan!');
        }

        // Ambil kategori aktif beserta pertanyaan aktifnya
        $categories = AssessmentCategory::with(['activeQuestions'])
            ->where('is_active', true)
            ->get();

        if ($categories->isEmpty()) {
            return redirect()->route('admin.assessment.categories')
                ->with('error', 'Buat kategori penilaian dulu!');
        }

        // Hitung total pertanyaan untuk progress bar di view
        $totalQuestions = $categories->sum(fn($c) => $c->activeQuestions->count());

        return view('admin.assessment.create', compact('target', 'categories', 'totalQuestions'));
    }

    /**
     * Menyimpan penilaian baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'evaluatee_id' => 'required|exists:users,id',
            'scores'       => 'required|array',
            'scores.*'     => 'required|integer|min:1|max:5',
            'notes'        => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Cek apakah sudah ada penilaian bulan ini
            $existingAssessment = Assessment::where('evaluator_id', auth()->id())
                ->where('evaluatee_id', $request->evaluatee_id)
                ->whereMonth('assessment_date', now()->month)
                ->whereYear('assessment_date', now()->year)
                ->first();

            if ($existingAssessment) {
                return redirect()->back()
                    ->with('error', 'Anda sudah menilai karyawan ini bulan ini!');
            }

            // Simpan header assessment
            $assessment = Assessment::create([
                'evaluator_id'    => auth()->id(),
                'evaluatee_id'    => $request->evaluatee_id,
                'assessment_date' => now(),
                'period'          => now()->format('F Y'),
                'period_type'     => 'monthly',
                'general_notes'   => $request->notes,
            ]);

            // Simpan detail penilaian per pertanyaan
            foreach ($request->scores as $question_id => $score) {
                if (!is_numeric($question_id)) continue;

                AssessmentDetail::create([
                    'assessment_id' => $assessment->id,
                    'question_id'   => $question_id,
                    'score'         => $score,
                ]);
            }

            DB::commit();

            if ($request->has('action') && $request->action == 'save_next') {
                return redirect()->route('admin.assessment.employees')
                    ->with('success', 'Penilaian berhasil disimpan! Silakan pilih karyawan berikutnya.');
            }

            return redirect()->route('admin.assessment.employees')
                ->with('success', 'Penilaian berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan form edit penilaian
     */
    public function edit($id)
    {
        if (!is_numeric($id)) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'ID penilaian tidak valid!');
        }

        // FIX: pakai relasi 'user' dan 'details.category' sesuai model
        $assessment = Assessment::with(['user', 'details.category'])->find($id);

        if (!$assessment) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'Data penilaian tidak ditemukan!');
        }

        if ($assessment->evaluator_id != auth()->id() && auth()->user()->role != 'admin') {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'Anda tidak berhak mengedit penilaian ini!');
        }

        $categories = AssessmentCategory::where('is_active', true)->get();

        // Mapping nilai yang sudah ada: category_id => score
        $existingScores = [];
        foreach ($assessment->details as $detail) {
            $existingScores[$detail->category_id] = $detail->score;
        }

        return view('admin.assessment.edit', compact('assessment', 'categories', 'existingScores'));
    }

    /**
     * Mengupdate penilaian
     */
    public function update(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'ID penilaian tidak valid!');
        }

        $assessment = Assessment::find($id);

        if (!$assessment) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'Data penilaian tidak ditemukan!');
        }

        if ($assessment->evaluator_id != auth()->id() && auth()->user()->role != 'admin') {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'Anda tidak berhak mengupdate penilaian ini!');
        }

        $request->validate([
            'scores'   => 'required|array',
            'scores.*' => 'required|integer|min:1|max:5',
            'notes'    => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $assessment->update(['general_notes' => $request->notes]);

            foreach ($request->scores as $category_id => $score) {
                if (!is_numeric($category_id)) continue;

                AssessmentDetail::updateOrCreate(
                    [
                        'assessment_id' => $assessment->id,
                        'category_id'   => $category_id,
                    ],
                    ['score' => $score]
                );
            }

            DB::commit();

            return redirect()->route('admin.assessment.employees')
                ->with('success', 'Penilaian berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan laporan/rapor penilaian
     */
    public function report(Request $request, $user_id = null)
    {
        $targetId = $user_id ?? auth()->id();

        if (!is_numeric($targetId)) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'ID user tidak valid!');
        }

        $user = User::find($targetId);

        if (!$user) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'User tidak ditemukan!');
        }

        // Ambil semua periode untuk filter dropdown
        $periods = Assessment::where('evaluatee_id', $targetId)
            ->select('period')
            ->distinct()
            ->orderBy('period', 'desc')
            ->pluck('period');

        // Query dengan filter periode
        $query = Assessment::with(['details.category'])
            ->where('evaluatee_id', $targetId)
            ->orderBy('assessment_date', 'desc');

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $assessments = $query->get();

        // Statistik
        $stats = [
            'total_assessments' => $assessments->count(),
            'latest_score'      => $assessments->isNotEmpty()
                ? round($assessments->first()->details->avg('score'), 2) : 0,
            'average_all'       => $assessments->isNotEmpty()
                ? round($assessments->flatMap->details->avg('score'), 2) : 0,
        ];

        // Data untuk radar chart: rata-rata per kategori
        $radarData = $assessments
            ->flatMap(fn($a) => $a->details)
            ->groupBy(fn($d) => $d->category->name ?? 'Unknown')
            ->map(fn($details, $name) => [
                'name'    => $name,
                'average' => round($details->avg('score'), 2),
            ])
            ->values();

        return view('admin.assessment.report', compact(
            'user', 'assessments', 'stats', 'radarData', 'periods'
        ));
    }

    /**
     * Menampilkan riwayat penilaian (untuk admin)
     */
    public function history()
    {
        // FIX: pakai relasi 'user' sesuai model Assessment
        $assessments = Assessment::with(['evaluator', 'user'])
            ->orderBy('assessment_date', 'desc')
            ->paginate(20);

        return view('admin.assessment.history', compact('assessments'));
    }

    /**
     * Menghapus penilaian (hanya admin)
     */
    public function destroy($id)
    {
        if (auth()->user()->role != 'admin') {
            return redirect()->back()->with('error', 'Hanya admin yang bisa menghapus penilaian!');
        }

        if (!is_numeric($id)) {
            return redirect()->back()->with('error', 'ID penilaian tidak valid!');
        }

        $assessment = Assessment::find($id);

        if (!$assessment) {
            return redirect()->back()->with('error', 'Data penilaian tidak ditemukan!');
        }

        try {
            DB::beginTransaction();
            $assessment->details()->delete();
            $assessment->delete();
            DB::commit();

            return redirect()->back()->with('success', 'Penilaian berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus penilaian: ' . $e->getMessage());
        }
    }
}