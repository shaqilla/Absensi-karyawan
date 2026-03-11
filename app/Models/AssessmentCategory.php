<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentCategory extends Model
{
    protected $fillable = ['name', 'description', 'type', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Semua pertanyaan di kategori ini
    public function questions()
    {
        return $this->hasMany(AssessmentQuestion::class, 'category_id');
    }

    // Pertanyaan aktif saja, urut by order (dipakai di view create)
    public function activeQuestions()
    {
        return $this->hasMany(AssessmentQuestion::class, 'category_id')
            ->where('is_active', true)
            ->orderBy('order');
    }

    // Detail penilaian lewat pertanyaan
    public function assessmentDetails()
    {
        return $this->hasManyThrough(
            AssessmentDetail::class,
            AssessmentQuestion::class,
            'category_id',  // FK di assessment_questions
            'question_id',  // FK di assessment_details
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}