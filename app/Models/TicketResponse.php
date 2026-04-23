<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketResponse extends Model
{
    protected $fillable = [
        'ticket_id',
        'responder_id',
        'message',
        'is_auto_reply'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Siapa yang ngirim balesan ini (bisa Operator, bisa Karyawan)
    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        // Simpan ke tabel ticket_responses
        \App\Models\TicketResponse::create([
            'ticket_id' => $id,
            'responder_id' => auth()->id(),
            'message' => $request->message,
            'is_auto_reply' => false
        ]);

        // Balik lagi ke halaman detail tiket
        return back()->with('success', 'Balasan berhasil dikirim!');
    }
}
