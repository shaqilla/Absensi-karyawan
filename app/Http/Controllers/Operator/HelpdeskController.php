<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HelpdeskController extends Controller
{
    // 1. List semua tiket buat operator
    public function index()
    {
        $tickets = Ticket::with('reporter')->latest()->get();
        return view('operator.tickets.index', [ // Pastikan file ada di resources/views/operator/tickets/index.blade.php
            'tickets' => $tickets,
            'layout' => 'layouts.admin'
        ]);
    }

    // 2. Detail tiket (Tempat chat/balas)
    public function show($id)
    {
        $ticket = Ticket::with(['responses.responder', 'reporter'])->findOrFail($id);

        $suggestions = [
            'Internet' => 'Mohon coba restart router Anda selama 5 menit.',
            'Software' => 'Silakan lakukan update aplikasi ke versi terbaru.',
            'Hardware' => 'Teknisi kami akan segera datang ke lokasi Anda.'
        ];

        // FIX: Gua ganti 'operator.show' jadi 'operator.tickets.show' sesuai folder lu
        return view('operator.tickets.show', [
            'ticket' => $ticket,
            'suggestions' => $suggestions,
            'layout' => 'layouts.admin'
        ]);
    }

    // 3. Simpan balasan & Update Status (SLA Response Time)
    public function reply(Request $request, $id)
    {
        $request->validate(['message' => 'required']);
        $ticket = Ticket::findOrFail($id);

        // Simpan Balasan
        TicketResponse::create([
            'ticket_id' => $id,
            'responder_id' => Auth::id(),
            'message' => $request->message,
            'is_auto_reply' => $request->has('is_auto')
        ]);

        // LOGIC REQ LU: Update status ke 'in-progress' & set operator_id
        $updateData = [];
        
        if ($ticket->status == 'open') {
            $updateData['status'] = 'in-progress';
        }

        if (!$ticket->operator_id) {
            $updateData['operator_id'] = Auth::id();
        }

        if (!empty($updateData)) {
            $ticket->update($updateData);
        }

        return back()->with('success', 'Balasan dikirim & Status diperbarui!');
    }

    // 4. Update Status (Misal buat nutup tiket)
    public function updateStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => $request->status]);

        return back()->with('success', 'Status tiket diubah menjadi ' . $request->status);
    }
}