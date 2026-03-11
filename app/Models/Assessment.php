<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $table = 'assessments';

    protected $fillable = [
        'evaluator_id',
        'evaluatee_id',
        'assessment_date',
        'period',
        'period_type',      
        'general_notes'
    ];

    protected $casts = [
        'assessment_date' => 'date',
    ];

    // Relasi ke detail penilaian (nilai per pertanyaan)
    public function details()
    {
        return $this->hasMany(AssessmentDetail::class, 'assessment_id');
    }

    // Relasi ke penilai (yang memberikan nilai)
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    // Relasi ke yang dinilai (karyawan yang dinilai)
    public function evaluatee()
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }

    // untuk evaluatee (agar konsisten dengan nama method)
    public function user()
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }

    // Mendapatkan rata-rata nilai keseluruhan
    public function getAverageScoreAttribute()
    {
        return round($this->details->avg('score'), 2);
    }

    // Mendapatkan nilai per kategori (rata-rata dari pertanyaan per kategori)
    public function getCategoryScoresAttribute()
    {
        $scores = [];

        foreach ($this->details as $detail) {
            $categoryId = $detail->question->category_id;
            $categoryName = $detail->question->category->name;

            if (!isset($scores[$categoryId])) {
                $scores[$categoryId] = [
                    'name' => $categoryName,
                    'total' => 0,
                    'count' => 0,
                    'scores' => []
                ];
            }

            $scores[$categoryId]['total'] += $detail->score;
            $scores[$categoryId]['count']++;
            $scores[$categoryId]['scores'][] = [
                'question' => $detail->question->question,
                'score' => $detail->score
            ];
        }

        // Hitung rata-rata per kategori
        foreach ($scores as &$category) {
            $category['average'] = round($category['total'] / $category['count'], 2);
        }

        return $scores;
    }

    //  Mendapatkan ringkasan penilaian dalam format sederhana
    public function getSummaryAttribute()
    {
        return [
            'evaluator' => $this->evaluator->nama ?? 'Unknown',
            'evaluatee' => $this->evaluatee->nama ?? 'Unknown',
            'date' => $this->assessment_date->format('d M Y'),
            'period' => $this->period,
            'average_score' => $this->average_score,
            'total_questions' => $this->details->count(),
            'notes' => $this->general_notes
        ];
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('assessment_date', now()->month)
            ->whereYear('assessment_date', now()->year);
    }

    public function scopeByEvaluator($query, $evaluatorId)
    {
        return $query->where('evaluator_id', $evaluatorId);
    }

    public function scopeForEvaluatee($query, $evaluateeId)
    {
        return $query->where('evaluatee_id', $evaluateeId);
    }
}
