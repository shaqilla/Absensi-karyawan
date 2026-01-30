<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $fillable = [
        'user_id', 'qr_session_id', 'tanggal', 'jam_masuk', 
        'jam_keluar', 'latitude', 'longitude', 'status', 
        'kategori_id', 'keterangan'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function qrSession() {
        return $this->belongsTo(QrSession::class);
    }

    public function kategori() {
        return $this->belongsTo(KategoriAbsen::class, 'kategori_id');
    }
}