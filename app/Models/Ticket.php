<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'operator_id',
        'subject',
        'description',
        'priority',
        'status',
        'first_response_at',
        'resolved_at'
    ];

    // Biar otomatis jadi objek Carbon (biar bisa diitung waktunya)
    protected $casts = [
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // LOGIKA SLA (SERVICE LEVEL AGREEMENT) - FITUR NO 3 & 4

    /**
     * Menghitung Kecepatan Respon (First Response Time)
     * Selisih antara tiket dibuat dengan balasan pertama operator
     */
    public function getResponseTimeMinutesAttribute()
    {
        if (!$this->first_response_at) return null;
        return $this->created_at->diffInMinutes($this->first_response_at);
    }

    /**
     * Menghitung Durasi Penyelesaian (Resolution Time)
     * Selisih antara tiket dibuat dengan tiket ditutup (Closed)
     */
    public function getResolutionTimeDurationAttribute()
    {
        if (!$this->resolved_at) return null;
        // Contoh return: "2 jam 30 menit"
        return $this->created_at->diffForHumans($this->resolved_at, true);
    }

    // RELASI DATABASE

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function responses()
    {
        return $this->hasMany(TicketResponse::class);
    }

    public function rating()
    {
        return $this->hasOne(SatisfactionRating::class);
    }
}
