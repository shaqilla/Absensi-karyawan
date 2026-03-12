<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    // Menampilkan daftar karyawan yang bisa dinilai
    public function employees()
    {
        $employees = Karyawan::with(['user', 'departemen'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'karyawan');
            })
            ->get();

        foreach ($employees as $emp) {
            $latest = Assessment::with([
                'evaluator.karyawan',
                'evaluatee.karyawan',
                'details.question.category',
            ])
                ->where('evaluatee_id', $emp->user_id)
                ->whereMonth('assessment_date', now()->month)
                ->whereYear('assessment_date', now()->year)
                ->latest()
                ->first();

            $emp->assessed_this_month  = !is_null($latest);
            $emp->latest_assessment_id = $latest?->id;
            $emp->latest_assessment    = $latest;
        }

        return view('admin.assessment.employees', compact('employees'));
    }

    // Menampilkan form penilaian
    public function create($evaluatee_id)
    {
        $target = User::with('karyawan')->where('role', 'karyawan')->findOrFail($evaluatee_id);

        // Ambil kategori beserta pertanyaannya
        $categories = AssessmentCategory::with(['questions' => function ($q) {
            $q->where('is_active', true);
        }])
            ->where('is_active', true)
            ->get();

        if ($categories->isEmpty()) {
            return redirect()->route('admin.assessment.categories')
                ->with('error', 'Buat kategori dan pertanyaan penilaian dulu!');
        }

        return view('admin.assessment.create', compact('target', 'categories'));
    }

    // Menyimpan penilaian baru
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

            // Cek duplikasi di bulan yang sama
            $existing = Assessment::where('evaluatee_id', $request->evaluatee_id)
                ->whereMonth('assessment_date', now()->month)
                ->whereYear('assessment_date', now()->year)
                ->first();

            if ($existing) {
                return redirect()->back()->with('error', 'Karyawan ini sudah dinilai bulan ini!');
            }

            $assessment = Assessment::create([
                'evaluator_id'    => Auth::id(),
                'evaluatee_id'    => $request->evaluatee_id,
                'assessment_date' => now(),
                'period'          => now()->format('F Y'),
                'general_notes'   => $request->notes,
            ]);

            foreach ($request->scores as $question_id => $score) {
                AssessmentDetail::create([
                    'assessment_id' => $assessment->id,
                    'question_id'   => $question_id,
                    'score'         => $score,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.assessment.report')->with('success', 'Penilaian berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    // Laporan Global untuk Admin
    public function report(Request $request)
    {
        $currentUser = Auth::user();

        // FIX: Inisialisasi variabel $query agar tidak error
        $query = Assessment::with([
            'evaluator.karyawan',
            'evaluatee.karyawan',
            'details.question.category',
        ]);

        // FILTER ROLE
        if ($currentUser->role == 'admin') {
            // Lihat semua
        } else {
            // Selain admin hanya lihat yang dia nilai (Atasan)
            $query->where('evaluator_id', $currentUser->id);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $assessments = $query->orderBy('assessment_date', 'desc')->get();
        $periods = Assessment::distinct()->pluck('period');

        // Hitung Rata-rata per Kategori untuk Radar Chart
        $categoryTotals = [];
        foreach ($assessments as $assessment) {
            foreach ($assessment->details as $detail) {
                if ($detail->question->category) {
                    $catName = $detail->question->category->name;
                    if (!isset($categoryTotals[$catName])) {
                        $categoryTotals[$catName] = ['total' => 0, 'count' => 0];
                    }
                    $categoryTotals[$catName]['total'] += $detail->score;
                    $categoryTotals[$catName]['count']++;
                }
            }
        }

        $avgPerCategory = [];
        foreach ($categoryTotals as $cat => $data) {
            $avgPerCategory[] = [
                'category' => $cat,
                'average' => round($data['total'] / $data['count'], 2)
            ];
        }

        return view('admin.assessment.report', compact('assessments', 'avgPerCategory', 'periods'));
    }

    // Rapor Saya (Khusus Karyawan)
    public function myReport(Request $request)
    {
        $userId = Auth::id();

        $query = Assessment::with(['evaluator', 'details.question.category'])
            ->where('evaluatee_id', $userId);

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $assessments = $query->orderBy('assessment_date', 'desc')->get();
        $periods = Assessment::where('evaluatee_id', $userId)->distinct()->pluck('period');

        // Data Radar Chart Pribadi
        $categoryScores = [];
        foreach ($assessments as $a) {
            foreach ($a->details as $d) {
                $catName = $d->question->category->name;
                if (!isset($categoryScores[$catName])) {
                    $categoryScores[$catName] = ['sum' => 0, 'count' => 0];
                }
                $categoryScores[$catName]['sum'] += $d->score;
                $categoryScores[$catName]['count']++;
            }
        }

        $radarLabels = [];
        $radarData = [];
        foreach ($categoryScores as $name => $val) {
            $radarLabels[] = $name;
            $radarData[] = round($val['sum'] / $val['count'], 2);
        }

        return view('karyawan.rapor', compact('assessments', 'periods', 'radarLabels', 'radarData'));
    }

    public function detail($id)
    {
        $assessment = Assessment::with(['evaluator', 'evaluatee', 'details.question.category'])->findOrFail($id);

        // Jika dipanggil lewat JavaScript (untuk Modal)
        if (request()->ajax()) {
            return response()->json($assessment);
        }

        // Jika dipanggil biasa (opsional)
        return view('admin.assessment.detail', compact('assessment'));
    }

    public function destroy($id)
    {
        $assessment = Assessment::findOrFail($id);
        $assessment->delete(); // Detail akan terhapus otomatis karena cascade di migration
        return back()->with('success', 'Data penilaian berhasil dihapus!');
    }

    // Menampilkan riwayat penilaian secara lengkap
    public function history(Request $request)
    {
        $query = Assessment::with(['evaluator.karyawan', 'evaluatee.karyawan', 'details.question.category']);

        // Fitur Cari Nama
        if ($request->filled('search')) {
            $query->whereHas('evaluatee', function ($q) use ($request) {
                $q->where('nama', 'LIKE', "%{$request->search}%");
            });
        }

        // Fitur Filter Periode
        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $assessments = $query->orderBy('assessment_date', 'desc')->paginate(10);
        $periods = Assessment::distinct()->pluck('period');

        return view('admin.assessment.history', compact('assessments', 'periods'));
    }
}
