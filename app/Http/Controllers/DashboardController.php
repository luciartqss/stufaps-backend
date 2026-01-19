<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Disbursement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        try {
            // Get basic stats
            $totalStudents = Student::count();
            $activeScholars = Student::where('scholarship_status', 'On-going')->count();
            $graduated = Student::where('scholarship_status', 'Graduated')->count();
            $terminated = Student::where('scholarship_status', 'Terminated')->count();
            
            // Get total disbursed amount
            $totalDisbursed = Disbursement::sum('payment_amount') ?? 0;
            
            // Get students by degree level
            $degreeLevels = Student::select('program_degree_level as level')
                ->selectRaw('COUNT(*) as students')
                ->groupBy('program_degree_level')
                ->get()
                ->toArray();
            
            // Get recent registrations (last 10 students)
            $recentRegistrations = Student::select([
                'seq as student_id',
                'first_name',
                'middle_name', 
                'surname',
                'extension',
                'degree_program',
                'scholarship_status',
                'created_at'
            ])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->toArray();

            return response()->json([
                'total_students' => $totalStudents,
                'active_scholars' => $activeScholars,
                'graduated' => $graduated,
                'terminated' => $terminated,
                'total_disbursed' => $totalDisbursed,
                'degree_levels' => $degreeLevels,
                'recent_registrations' => $recentRegistrations,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch dashboard data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}