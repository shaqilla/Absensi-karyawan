<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $fillable = [
        'user_id', 'nip', 'jabatan', 'departemen_id', 
        'tanggal_masuk', 'alamat', 'jenis_kelamin'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function departemen() {
        return $this->belongsTo(Departemen::class);
    }
}
