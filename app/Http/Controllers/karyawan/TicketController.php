<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\SatisfactionRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TicketController extends Controller
{
    private function getLayout()
    {
        return in_array(Auth::user()->role, ['admin', 'operator', 'pimpinan'])
            ? 'layouts.admin'
            : 'layouts.karyawan';
    }

    // Kirim layout ke view pake method ini
    private function renderView($view, $data = [])
    {
        $data['layout'] = $this->getLayout();
        return view($view, $data);
    }

    public function index()
    {
        $user = Auth::user();
        $layout = $this->getLayout();

        if (in_array($user->role, ['admin', 'operator', 'pimpinan'])) {
            // Operator/Admin: Liat semua antrian
            $tickets = Ticket::with('reporter')->latest()->get();
        } else {
            // Karyawan: Cuma liat tiket miliknya
            $tickets = Ticket::where('reporter_id', $user->id)->latest()->get();
        }

        return view('karyawan.tickets.index', compact('tickets', 'layout'));
    }

    public function create()
    {
        if (Auth::user()->role !== 'karyawan') {
            abort(403, 'Operator tidak boleh membuat tiket!');
        }
        return $this->renderView('karyawan.tickets.create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'karyawan') {
            abort(403);
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Mid,High'
        ]);

        // BUG FIX 2: Cek duplikasi lagi sebelum store (double check)
        $duplicate = $this->checkDuplicateExists($request->subject, $request->description);
        if ($duplicate) {
            return back()->with('warning', 'Aduan serupa sudah ada! Silakan cek daftar tiket Anda.')
                ->withInput();
        }

        Ticket::create([
            'reporter_id' => Auth::id(),
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'open',
            'created_at' => now()
        ]);

        return redirect()->route('karyawan.tickets.index')
            ->with('success', 'Tiket berhasil dikirim!');
    }

    public function show($id)
    {
        $ticket = Ticket::with(['responses.responder', 'reporter', 'operator', 'rating'])
            ->findOrFail($id);

        $user = Auth::user();
        $layout = $this->getLayout();

        // Cek akses
        if ($user->role == 'karyawan' && $ticket->reporter_id !== $user->id) {
            abort(403, 'Akses ditolak!');
        }

        // BUG FIX: Generate auto-reply suggestions berdasarkan subject/description
        $suggestions = $this->generateSuggestions($ticket);

        return view('karyawan.tickets.show', compact('ticket', 'layout', 'suggestions'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);

        $ticket = Ticket::findOrFail($id);
        $user = Auth::user();

        // Cek apakah tiket sudah closed
        if ($ticket->status === 'closed') {
            return back()->with('error', 'Tiket sudah ditutup, tidak bisa membalas!');
        }

        // BUG FIX 3: Cek apakah ini first reply dari OPERATOR (bukan dari karyawan)
        $hasOperatorReplied = TicketResponse::where('ticket_id', $id)
            ->whereHas('responder', function ($q) {
                $q->whereIn('role', ['operator', 'admin']);
            })->exists();

        $response = TicketResponse::create([
            'ticket_id' => $id,
            'responder_id' => $user->id,
            'message' => $request->message,
            'is_auto_reply' => false,
            'created_at' => now()
        ]);

        // Jika yang balas adalah Operator/Admin
        if (in_array($user->role, ['operator', 'admin'])) {
            $updateData = [];

            // Set operator yang menangani jika belum ada
            if (!$ticket->operator_id) {
                $updateData['operator_id'] = $user->id;
            }

            // BUG FIX 3: Set first_response_at saat operator reply pertama kali
            if (!$hasOperatorReplied) {
                $updateData['first_response_at'] = now();
            }

            // Update status ke in-progress jika masih open
            if ($ticket->status == 'open') {
                $updateData['status'] = 'in-progress';
            }

            $ticket->update($updateData);
        }

        return back()->with('success', 'Balasan dikirim!');
    }

    // BUG FIX 4 & 5: Method khusus untuk close tiket (bukan generic updateStatus)
    public function closeTicket($id)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['operator', 'admin'])) {
            abort(403);
        }

        $ticket = Ticket::with('rating')->findOrFail($id);

        // Cek status: hanya bisa close dari IN-PROGRESS
        if ($ticket->status !== 'in-progress') {
            return back()->with('error', 'Tiket harus dalam status IN-PROGRESS untuk ditutup!');
        }

        // BUG FIX 6: Cek apakah sudah ada rating dari pelapor
        $hasRating = SatisfactionRating::where('ticket_id', $id)->exists();

        if (!$hasRating) {
            return back()->with('error', 'Pelapor harus memberi rating terlebih dahulu sebelum tiket ditutup!');
        }

        // BUG FIX 4: Set resolved_at saat close
        $ticket->update([
            'status' => 'closed',
            'resolved_at' => now()
        ]);

        return back()->with('success', 'Tiket berhasil ditutup!');
    }

    // BUG FIX 2: Method checkDuplicate yang BENAR (pake subject + description)
    public function checkDuplicate(Request $request)
    {
        // Tangkap ketikan user
        $subject = $request->subject;

        // Kalo kurang dari 3 huruf, balikin kosong
        if (strlen($subject) < 3) {
            return response()->json([]);
        }

        // CARA PALING AMAN: Pake LIKE (Pasti jalan di MySQL versi berapapun)
        $similar = \App\Models\Ticket::where(function ($query) use ($subject) {
            $query->where('subject', 'LIKE', '%' . $subject . '%')
                ->orWhere('description', 'LIKE', '%' . $subject . '%');
        })
            ->where('status', '!=', 'closed') // Cuma cari yang belum selesai
            ->select('id', 'subject', 'status')
            ->limit(3)
            ->get();

        return response()->json($similar);
    }

    // Helper untuk double check sebelum store
    private function checkDuplicateExists($subject, $description)
    {
        $searchQuery = trim($subject . ' ' . $description);

        if (strlen($searchQuery) < 5) {
            return false;
        }

        $exists = Ticket::where('status', '!=', 'closed')
            ->whereRaw("MATCH(subject, description) AGAINST(? IN BOOLEAN MODE)", [$searchQuery . '*'])
            ->exists();

        return $exists;
    }

    // BUG FIX 7: Generate saran jawaban berdasarkan kata kunci
    private function generateSuggestions($ticket)
    {
        $subject = strtolower($ticket->subject);
        $description = strtolower($ticket->description);
        $combined = $subject . ' ' . $description;

        $suggestions = [];

        // Keyword matching untuk saran otomatis
        if (str_contains($combined, 'koneksi') || str_contains($combined, 'internet') || str_contains($combined, 'wifi')) {
            $suggestions['Internet / Jaringan'] = 'Mohon coba restart router Anda. Jika masih bermasalah, cek kabel LAN dan pastikan tidak longgar. Apakah perangkat lain juga mengalami kendala yang sama?';
        }

        if (str_contains($combined, 'login') || str_contains($combined, 'password') || str_contains($combined, 'akun')) {
            $suggestions['Login / Akun'] = 'Silakan coba reset password melalui fitur "Lupa Password". Jika masih gagal, kami akan reset akun Anda dari sisi server. Mohon konfirmasi email terdaftar Anda.';
        }

        if (str_contains($combined, 'lemot') || str_contains($combined, 'lambat') || str_contains($combined, 'slow')) {
            $suggestions['Performa Sistem'] = 'Kami akan cek beban server. Sementara coba clear cache browser Anda (Ctrl+Shift+Del) dan restart aplikasi. Apakah masalah terjadi di jam-jam tertentu?';
        }

        if (str_contains($combined, 'print') || str_contains($combined, 'printer') || str_contains($combined, 'cetak')) {
            $suggestions['Printer / Cetak'] = 'Cek koneksi printer dan pastikan driver sudah terinstall. Restart spooler printer: buka Services.msi → cari Print Spooler → klik Restart.';
        }

        if (str_contains($combined, 'email') || str_contains($combined, 'surat')) {
            $suggestions['Email / Surat'] = 'Cek folder Spam atau Junk. Jika tidak ada, coba kirim ulang atau hubungi admin email untuk cek antrian server.';
        }

        if (str_contains($combined, 'file') || str_contains($combined, 'hilang') || str_contains($combined, 'hapus')) {
            $suggestions['Data / File Hilang'] = 'Cek Recycle Bin terlebih dahulu. Jika tidak ada, kami akan coba restore dari backup. Kapan terakhir kali file ini masih ada?';
        }

        // Default suggestion
        if (empty($suggestions)) {
            $suggestions['Umum'] = 'Terima kasih atas laporan Anda. Tim teknis kami akan segera meninjau masalah ini. Mohon tunggu update selanjutnya.';
        }

        // Tambah suggestion berdasarkan priority
        if ($ticket->priority == 'High') {
            $suggestions['*PRIORITAS TINGGI*'] = 'Masalah ini ditangani prioritas utama. Tim teknis akan segera menghubungi Anda dalam waktu kurang dari 1 jam.';
        }

        return $suggestions;
    }

    // BUG FIX 6: Rating hanya bisa diberikan setelah ticket IN-PROGRESS atau CLOSED
    public function rate(Request $request, $id)
    {
        $request->validate([
            'score' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500'
        ]);

        $ticket = Ticket::findOrFail($id);

        // Cek akses: hanya pelapor
        if ($ticket->reporter_id !== Auth::id()) {
            abort(403, 'Hanya pelapor yang bisa memberi rating!');
        }

        // Cek status: hanya bisa rating kalau status sudah in-progress atau closed
        if (!in_array($ticket->status, ['in-progress', 'closed'])) {
            return back()->with('error', 'Rating hanya bisa diberikan setelah tiket diproses operator!');
        }

        // Cek apakah sudah pernah rating
        $existingRating = SatisfactionRating::where('ticket_id', $id)->first();

        if ($existingRating) {
            return back()->with('error', 'Anda sudah memberikan rating untuk tiket ini!');
        }

        SatisfactionRating::create([
            'ticket_id' => $id,
            'score' => $request->score,
            'feedback' => $request->feedback
        ]);

        // Optional: Auto close ticket jika sudah rating dan status masih in-progress
        if ($ticket->status === 'in-progress') {
            $ticket->update([
                'status' => 'closed',
                'resolved_at' => now()
            ]);
        }

        return back()->with('success', 'Terima kasih atas penilaian Anda!');
    }

    public function getSlaInfo($id)
    {
        $ticket = Ticket::findOrFail($id);
        $user = Auth::user();

        if ($user->role == 'karyawan' && $ticket->reporter_id !== $user->id) {
            abort(403);
        }

        $sla = [
            'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
            'response_time' => $ticket->first_response_at
                ? $ticket->created_at->diffInMinutes($ticket->first_response_at) . ' menit'
                : null,
            'resolution_time' => $ticket->resolved_at
                ? $ticket->created_at->diffInMinutes($ticket->resolved_at) . ' menit'
                : null,
            'sla_deadline' => $this->calculateSlaDeadline($ticket),
            'is_sla_met' => $this->checkSlaStatus($ticket)
        ];

        return response()->json($sla);
    }

    private function calculateSlaDeadline($ticket)
    {
        $hours = match ($ticket->priority) {
            'High' => 1,
            'Mid' => 4,
            'Low' => 24,
            default => 24
        };

        return $ticket->created_at->copy()->addHours($hours);
    }

    private function checkSlaStatus($ticket)
    {
        if (!$ticket->first_response_at) {
            return null;
        }

        $deadline = $this->calculateSlaDeadline($ticket);
        return $ticket->first_response_at <= $deadline;
    }
}
