<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatisfactionRating extends Model
{
    protected $fillable = [
        'ticket_id',
        'score',
        'feedback'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
