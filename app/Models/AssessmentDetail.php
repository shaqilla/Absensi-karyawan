<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentDetail extends Model
{
    // FIX: pakai question_id sesuai migration (bukan category_id)
    protected $fillable = ['assessment_id', 'question_id', 'score'];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function question()
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }

    // Helper: akses kategori lewat pertanyaan
    public function getCategoryAttribute()
    {
        return $this->question?->category;
    }
}
