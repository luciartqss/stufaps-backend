<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Disbursement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        try {
            $semester = $request->input('semester');
            $academicYear = $request->input('academic_year');

            // Base query for students - filter by disbursement if semester/AY provided
            $studentQuery = Student::query();
            
            if ($semester || $academicYear) {
                $studentQuery->whereHas('disbursements', function ($q) use ($semester, $academicYear) {
                    if ($semester) {
                        $q->where('semester', $semester);
                    }
                    if ($academicYear) {
                        $q->where('academic_year', $academicYear);
                    }
                });
            }

            // Get basic stats
            $totalStudents = (clone $studentQuery)->count();
            $activeScholars = (clone $studentQuery)->where('scholarship_status', 'Active')->count();
            $graduated = (clone $studentQuery)->where('scholarship_status', 'Graduated')->count();
            $terminated = (clone $studentQuery)->where('scholarship_status', 'Terminated')->count();
            
            // Calculate "others" (any status not in the main 3)
            $othersCount = (clone $studentQuery)
                ->whereNotIn('scholarship_status', ['Active', 'Graduated', 'Terminated'])
                ->whereNotNull('scholarship_status')
                ->count();

            // Get total disbursed amount with filters
            $disbursementQuery = Disbursement::query();
            if ($semester) {
                $disbursementQuery->where('semester', $semester);
            }
            if ($academicYear) {
                $disbursementQuery->where('academic_year', $academicYear);
            }
            $totalDisbursed = $disbursementQuery->sum('payment_amount') ?? 0;

            // Get scholarship status distribution (for pie chart)
            $statusDistribution = (clone $studentQuery)
                ->select('scholarship_status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('scholarship_status')
                ->get()
                ->map(function ($item) {
                    $status = $item->scholarship_status ?? 'Unknown';
                    // Group non-standard statuses as "Others"
                    if (!in_array($status, ['Active', 'Graduated', 'Terminated'])) {
                        $status = 'Others';
                    }
                    return ['status' => $status, 'count' => $item->count];
                })
                ->groupBy('status')
                ->map(function ($group) {
                    return [
                        'status' => $group->first()['status'],
                        'count' => $group->sum('count')
                    ];
                })
                ->values()
                ->toArray();

            // Get students by degree level
            $degreeLevels = (clone $studentQuery)
                ->select('program_degree_level as level')
                ->selectRaw('COUNT(*) as students')
                ->whereNotNull('program_degree_level')
                ->groupBy('program_degree_level')
                ->orderByDesc('students')
                ->get()
                ->toArray();

            // Get students by scholarship program
            $scholarshipPrograms = (clone $studentQuery)
                ->select('scholarship_program')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('scholarship_program')
                ->groupBy('scholarship_program')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->toArray();

            // Get students by institution type
            $institutionTypes = (clone $studentQuery)
                ->select('institutional_type')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('institutional_type')
                ->groupBy('institutional_type')
                ->orderByDesc('count')
                ->get()
                ->toArray();

            // Get recent registrations (last 10 students)
            $recentRegistrations = (clone $studentQuery)
                ->select([
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

            // Get available filter options
            $availableSemesters = Disbursement::select('semester')
                ->whereNotNull('semester')
                ->distinct()
                ->orderBy('semester')
                ->pluck('semester')
                ->toArray();

            $availableAcademicYears = Disbursement::select('academic_year')
                ->whereNotNull('academic_year')
                ->distinct()
                ->orderByDesc('academic_year')
                ->pluck('academic_year')
                ->toArray();

            // ===== DATA QUALITY WARNINGS =====
            
            // Students without UII
            $studentsWithoutUii = Student::whereNull('uii')
                ->orWhere('uii', '')
                ->select('seq', 'surname', 'first_name', 'name_of_institution', 'award_number')
                ->limit(50)
                ->get()
                ->toArray();

            // Duplicate Award Numbers
            $duplicateAwardNumbers = Student::select('award_number')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('award_number')
                ->where('award_number', '!=', '')
                ->groupBy('award_number')
                ->having('count', '>', 1)
                ->get()
                ->toArray();

            // Get details of students with duplicate award numbers
            $duplicateAwardDetails = [];
            if (count($duplicateAwardNumbers) > 0) {
                $dupAwardNos = array_column($duplicateAwardNumbers, 'award_number');
                $duplicateAwardDetails = Student::whereIn('award_number', $dupAwardNos)
                    ->select('seq', 'surname', 'first_name', 'award_number', 'scholarship_program')
                    ->orderBy('award_number')
                    ->get()
                    ->toArray();
            }

            // Students without authority info (UII exists but no authority_type/number/series)
            $studentsWithoutAuthority = Student::whereNotNull('uii')
                ->where('uii', '!=', '')
                ->where(function ($q) {
                    $q->whereNull('authority_type')
                      ->orWhere('authority_type', '');
                })
                ->select('seq', 'surname', 'first_name', 'uii', 'name_of_institution', 'degree_program')
                ->limit(50)
                ->get()
                ->toArray();

            // Students with incomplete personal info
            $studentsIncompleteInfo = Student::where(function ($q) {
                    $q->whereNull('surname')->orWhere('surname', '')
                      ->orWhereNull('first_name')->orWhere('first_name', '')
                      ->orWhereNull('date_of_birth')
                      ->orWhereNull('contact_number')->orWhere('contact_number', '');
                })
                ->select('seq', 'surname', 'first_name', 'award_number')
                ->limit(50)
                ->get()
                ->toArray();

            return response()->json([
                'total_students' => $totalStudents,
                'active_scholars' => $activeScholars,
                'graduated' => $graduated,
                'terminated' => $terminated,
                'others' => $othersCount,
                'total_disbursed' => $totalDisbursed,
                'status_distribution' => $statusDistribution,
                'degree_levels' => $degreeLevels,
                'scholarship_programs' => $scholarshipPrograms,
                'institution_types' => $institutionTypes,
                'recent_registrations' => $recentRegistrations,
                'filters' => [
                    'semesters' => $availableSemesters,
                    'academic_years' => $availableAcademicYears,
                ],
                'warnings' => [
                    'no_uii' => [
                        'count' => count($studentsWithoutUii),
                        'students' => $studentsWithoutUii,
                    ],
                    'duplicate_award_numbers' => [
                        'count' => count($duplicateAwardNumbers),
                        'duplicates' => $duplicateAwardNumbers,
                        'students' => $duplicateAwardDetails,
                    ],
                    'no_authority' => [
                        'count' => count($studentsWithoutAuthority),
                        'students' => $studentsWithoutAuthority,
                    ],
                    'incomplete_info' => [
                        'count' => count($studentsIncompleteInfo),
                        'students' => $studentsIncompleteInfo,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch dashboard data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}