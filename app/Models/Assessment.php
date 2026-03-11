<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'evaluator_id',
        'evaluatee_id',
        'assessment_date',
        'period',
        'general_notes'
    ];

    public function details()
    {
        return $this->hasMany(AssessmentDetail::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }
}
