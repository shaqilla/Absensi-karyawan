<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\SatisfactionRating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Total tiket
        $totalTickets = Ticket::count();
        $closedTickets = Ticket::where('status', 'closed')->count();
        
        // Rata-rata response time (dari tiket yang sudah direspon)
        $avgResponseTime = Ticket::whereNotNull('first_response_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, first_response_at)) as avg_response'))
            ->value('avg_response') ?? 0;
        
        // Rata-rata rating
        $avgRating = SatisfactionRating::avg('score') ?? 0;
        
        // Performa per operator
        $operatorPerformance = User::whereIn('role', ['operator', 'admin'])
            ->withCount(['ticketsHandled as total_handled' => function($q) {
                $q->where('status', 'closed');
            }])
            ->with(['ticketsHandled' => function($q) {
                $q->whereNotNull('first_response_at')
                  ->whereNotNull('resolved_at');
            }])
            ->get()
            ->map(function($operator) {
                $tickets = $operator->ticketsHandled;
                
                // Rata-rata response time (jam)
                $avgResponse = $tickets->avg(function($ticket) {
                    return $ticket->first_response_at 
                        ? $ticket->created_at->diffInHours($ticket->first_response_at) 
                        : null;
                });
                
                // Rata-rata resolution time (jam)
                $avgResolution = $tickets->avg(function($ticket) {
                    return $ticket->resolved_at 
                        ? $ticket->created_at->diffInHours($ticket->resolved_at) 
                        : null;
                });
                
                // Rata-rata rating
                $avgRating = $tickets->avg(function($ticket) {
                    return $ticket->rating?->score;
                });
                
                // SLA status
                $slaMet = $tickets->filter(function($ticket) {
                    $deadlineHours = match($ticket->priority) {
                        'High' => 1,
                        'Mid' => 4,
                        'Low' => 24,
                        default => 4
                    };
                    $responseTime = $ticket->first_response_at 
                        ? $ticket->created_at->diffInHours($ticket->first_response_at) 
                        : null;
                    return $responseTime && $responseTime <= $deadlineHours;
                })->count();
                
                return (object) [
                    'id' => $operator->id,
                    'name' => $operator->nama,
                    'role' => $operator->role,
                    'total_handled' => $operator->total_handled,
                    'avg_response_hours' => round($avgResponse ?? 0, 1),
                    'avg_resolution_hours' => round($avgResolution ?? 0, 1),
                    'avg_rating' => round($avgRating ?? 0, 1),
                    'sla_met_count' => $slaMet,
                    'sla_total_count' => $tickets->count()
                ];
            });
        
        // Distribusi rating
        $ratingDistribution = [
            1 => SatisfactionRating::where('score', 1)->count(),
            2 => SatisfactionRating::where('score', 2)->count(),
            3 => SatisfactionRating::where('score', 3)->count(),
            4 => SatisfactionRating::where('score', 4)->count(),
            5 => SatisfactionRating::where('score', 5)->count(),
        ];
        $totalRatings = SatisfactionRating::count();
        
        // SLA Summary (hitung dari tiket yang sudah direspon)
        $ticketsWithResponse = Ticket::whereNotNull('first_response_at')->get();
        $slaMetCount = 0;
        $slaBreachedCount = 0;
        
        foreach ($ticketsWithResponse as $ticket) {
            $deadlineHours = match($ticket->priority) {
                'High' => 1,
                'Mid' => 4,
                'Low' => 24,
                default => 4
            };
            $responseHours = $ticket->created_at->diffInHours($ticket->first_response_at);
            
            if ($responseHours <= $deadlineHours) {
                $slaMetCount++;
            } else {
                $slaBreachedCount++;
            }
        }
        
        return view('admin.analytics.index', compact(
            'totalTickets',
            'closedTickets', 
            'avgResponseTime',
            'avgRating',
            'operatorPerformance',
            'ratingDistribution',
            'totalRatings',
            'slaMetCount',
            'slaBreachedCount'
        ));
    }
}