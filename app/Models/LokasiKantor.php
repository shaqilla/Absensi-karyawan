<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiKantor extends Model
{
    protected $fillable = ['nama_kantor', 'latitude', 'longitude', 'radius'];
}