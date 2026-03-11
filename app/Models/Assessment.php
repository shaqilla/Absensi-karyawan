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

    // Relasi ke detail penilaian
    public function details()
    {
        return $this->hasMany(AssessmentDetail::class, 'assessment_id');
    }

    // Relasi ke penilai
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    // Relasi ke yang dinilai
    public function evaluatee()
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }

    // Alias untuk evaluatee
    public function user()
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }

    // Accessor: Rata-rata nilai total
    public function getAverageScoreAttribute()
    {
        // Gunakan optional() atau cek count untuk menghindari error pembagian nol
        return $this->details->count() > 0 ? round($this->details->avg('score'), 2) : 0;
    }

    // Accessor: Nilai per kategori (PENTING: Sudah diperbaiki agar aman dari null)
    public function getCategoryScoresAttribute()
    {
        $scores = [];

        foreach ($this->details as $detail) {
            // Cek apakah relasi question dan category ada untuk menghindari error "Property of non-object"
            $question = $detail->question;
            if (!$question || !$question->category) {
                continue;
            }

            $categoryId = $question->category_id;
            $categoryName = $question->category->name;

            if (!isset($scores[$categoryId])) {
                $scores[$categoryId] = [
                    'name' => $categoryName,
                    'total' => 0,
                    'count' => 0,
                    'items' => []
                ];
            }

            $scores[$categoryId]['total'] += $detail->score;
            $scores[$categoryId]['count']++;
            $scores[$categoryId]['items'][] = [
                'question' => $question->question,
                'score' => $detail->score
            ];
        }

        // Hitung rata-rata per kategori
        foreach ($scores as &$category) {
            $category['average'] = $category['count'] > 0 ? round($category['total'] / $category['count'], 2) : 0;
        }

        return $scores;
    }

    // Accessor: Ringkasan untuk laporan cepat
    public function getSummaryAttribute()
    {
        return [
            'evaluator' => $this->evaluator->nama ?? 'Sistem',
            'evaluatee' => $this->evaluatee->nama ?? 'N/A',
            'date' => $this->assessment_date ? $this->assessment_date->format('d M Y') : '-',
            'period' => $this->period,
            'average_score' => $this->average_score,
            'total_questions' => $this->details->count(),
            'notes' => $this->general_notes
        ];
    }

    // --- SCOPES ---
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
