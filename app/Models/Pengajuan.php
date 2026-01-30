<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jenis_pengajuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'lampiran',
        'status_approval',
        'approved_by',
        'catatan_admin'
    ];

    // Relasi: Mengetahui siapa karyawan yang mengajukan
    public function karyawan()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: Mengetahui siapa admin yang menyetujui
    public function admin()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
