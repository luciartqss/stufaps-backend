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

            // When filtering by AY, compute EFFECTIVE status for that AY:
            // - If a student has disbursements in a LATER AY than the filtered one,
            //   they were still active during the filtered AY (even if currently terminated/graduated).
            // - If the filtered AY is their LAST AY with disbursements, use their current status.
            // Without AY filter, just use current scholarship_status.
            if ($academicYear) {
                // Get student seqs that have disbursements in the filtered AY
                $filteredStudentSeqs = Disbursement::query()
                    ->where('academic_year', $academicYear)
                    ->when($semester, fn($q) => $q->where('semester', $semester))
                    ->distinct()
                    ->pluck('student_seq');

                // For each of these students, get their max AY from all disbursements
                $maxAyPerStudent = Disbursement::whereIn('student_seq', $filteredStudentSeqs)
                    ->groupBy('student_seq')
                    ->select('student_seq', DB::raw('MAX(academic_year) as max_ay'))
                    ->pluck('max_ay', 'student_seq');

                // Students whose latest AY is AFTER the filtered AY → effectively "On-going" during filtered AY
                $effectivelyActiveSeqs = $maxAyPerStudent
                    ->filter(fn($maxAy) => $maxAy > $academicYear)
                    ->keys()
                    ->toArray();

                // Students whose latest AY is the filtered AY (or earlier) → use current status
                $atLatestAySeqs = $maxAyPerStudent
                    ->filter(fn($maxAy) => $maxAy <= $academicYear)
                    ->keys()
                    ->toArray();

                // Count effective statuses
                $effectivelyActiveCount = count($effectivelyActiveSeqs);
                $currentlyActiveAtMax = Student::whereIn('seq', $atLatestAySeqs)
                    ->where('scholarship_status', 'Active')->count();
                $activeScholars = $effectivelyActiveCount + $currentlyActiveAtMax;

                $graduated = Student::whereIn('seq', $atLatestAySeqs)
                    ->where('scholarship_status', 'Graduated')->count();
                $terminated = Student::whereIn('seq', $atLatestAySeqs)
                    ->where('scholarship_status', 'Terminated')->count();

                // Others = total minus the 3 main categories
                $othersCount = $totalStudents - $activeScholars - $graduated - $terminated;

                // Build effective status distribution for charts
                $statusDistribution = collect();
                if ($activeScholars > 0) $statusDistribution->push(['status' => 'Active', 'count' => $activeScholars]);
                if ($graduated > 0) $statusDistribution->push(['status' => 'Graduated', 'count' => $graduated]);
                if ($terminated > 0) $statusDistribution->push(['status' => 'Terminated', 'count' => $terminated]);
                if ($othersCount > 0) $statusDistribution->push(['status' => 'Others', 'count' => $othersCount]);
                $statusDistribution = $statusDistribution->toArray();
            } else {
                $activeScholars = (clone $studentQuery)->where('scholarship_status', 'Active')->count();
                $graduated = (clone $studentQuery)->where('scholarship_status', 'Graduated')->count();
                $terminated = (clone $studentQuery)->where('scholarship_status', 'Terminated')->count();
                
                // Calculate "others" (any status not in the main 3)
                $othersCount = $totalStudents - $activeScholars - $graduated - $terminated;

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
            }

            // Get total disbursed amount with filters
            // Use COALESCE to fall back: payment_amount → amount → 0
            $disbursementQuery = Disbursement::query();
            if ($semester) {
                $disbursementQuery->where('semester', $semester);
            }
            if ($academicYear) {
                $disbursementQuery->where('academic_year', $academicYear);
            }
            $totalDisbursed = $disbursementQuery->selectRaw('COALESCE(SUM(COALESCE(payment_amount, amount, 0)), 0) as total')->value('total') ?? 0;

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
            
            // 1. Students without UII - only get count, data fetched via separate endpoint
            $noUiiCount = Student::where(function($q) {
                $q->whereNull('uii')->orWhere('uii', '');
            })->count();

            // 2. Students without LRN (Learner Reference Number)
            $noLrnCount = Student::where(function($q) {
                $q->whereNull('learner_reference_number')->orWhere('learner_reference_number', '');
            })->count();

            // 3. Duplicate LRN - Students with same Learner Reference Number
            $duplicateLrns = Student::select('learner_reference_number')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('learner_reference_number')
                ->where('learner_reference_number', '!=', '')
                ->groupBy('learner_reference_number')
                ->having('count', '>', 1)
                ->get()
                ->toArray();

            $duplicateLrnDetails = [];
            if (count($duplicateLrns) > 0) {
                $dupLrns = array_column($duplicateLrns, 'learner_reference_number');
                $duplicateLrnDetails = Student::whereIn('learner_reference_number', $dupLrns)
                    ->select('seq', 'surname', 'first_name', 'learner_reference_number', 'scholarship_program', 'scholarship_status')
                    ->orderBy('learner_reference_number')
                    ->get()
                    ->toArray();
            }

            // 4. Students without Award Numbers (incomplete details)
            $noAwardNumberCount = Student::where(function($q) {
                $q->whereNull('award_number')->orWhere('award_number', '');
            })->count();

            // 5. Duplicate Award Numbers with specific rules:
            // - Only ONE active student can have an award number (multiple active = warning)
            // - Graduated students' award numbers CANNOT be reused (if graduated + another student has it = warning)
            // - Terminated students' award numbers CAN be reused (OK)
            
            // First, find award numbers with more than one ACTIVE student
            $duplicateActiveAwardNumbers = Student::select('award_number')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('award_number')
                ->where('award_number', '!=', '')
                ->where('scholarship_status', 'Active')
                ->groupBy('award_number')
                ->having('count', '>', 1)
                ->pluck('award_number')
                ->toArray();

            // Second, find award numbers where a GRADUATED student's number is being used by another student
            $graduatedAwardNumbers = Student::select('award_number')
                ->whereNotNull('award_number')
                ->where('award_number', '!=', '')
                ->where('scholarship_status', 'Graduated')
                ->pluck('award_number')
                ->toArray();

            $reusedGraduatedAwards = [];
            if (count($graduatedAwardNumbers) > 0) {
                $reusedGraduatedAwards = Student::select('award_number')
                    ->whereIn('award_number', $graduatedAwardNumbers)
                    ->where('scholarship_status', '!=', 'Graduated')
                    ->distinct()
                    ->pluck('award_number')
                    ->toArray();
            }

            // Combine both types of violations
            $allDuplicateAwardNumbers = array_unique(array_merge($duplicateActiveAwardNumbers, $reusedGraduatedAwards));

            // Get details of students with duplicate award number violations
            $duplicateAwardDetails = [];
            if (count($allDuplicateAwardNumbers) > 0) {
                $duplicateAwardDetails = Student::whereIn('award_number', $allDuplicateAwardNumbers)
                    ->select('seq', 'surname', 'first_name', 'award_number', 'scholarship_program', 'scholarship_status')
                    ->orderBy('award_number')
                    ->orderByRaw("CASE 
                        WHEN scholarship_status = 'Active' THEN 0 
                        WHEN scholarship_status = 'Graduated' THEN 1 
                        ELSE 2 END")
                    ->get()
                    ->toArray();
            }

            // 6. Students with incomplete information
            // Fields that are REQUIRED (null = incomplete):
            // Personal: surname, first_name, sex, date_of_birth
            // Contact: contact_number, email_address, street_brgy, municipality_city, province, congressional_district, zip_code
            // Institution: name_of_institution, uii, institutional_type, region, degree_program, program_degree_level
            // Scholarship: in_charge, award_year, scholarship_program, award_number, authority_type, authority_number, series, scholarship_status
            // LRN: learner_reference_number
            // 
            // Fields that CAN be null (not counted as incomplete):
            // - special_group, certification_number, program_major, program_discipline
            // - replacement_info, termination_reason (remarks)
            // - middle_name, extension, is_priority
            
            $requiredFields = [
                'surname', 'first_name', 'sex', 'date_of_birth',
                'contact_number', 'email_address', 'street_brgy', 'municipality_city', 
                'province', 'congressional_district', 'zip_code',
                'name_of_institution', 'uii', 'institutional_type', 'region', 
                'degree_program', 'program_degree_level',
                'in_charge', 'award_year', 'scholarship_program', 'award_number',
                'authority_type', 'authority_number', 'series', 'scholarship_status',
                'learner_reference_number', 'basis_cmo'
            ];

            // Build query for incomplete info - student has at least one required field null or empty
            $incompleteInfoQuery = Student::where(function($query) use ($requiredFields) {
                foreach ($requiredFields as $field) {
                    $query->orWhere(function($q) use ($field) {
                        $q->whereNull($field)->orWhere($field, '');
                    });
                }
            });
            
            $incompleteInfoCount = (clone $incompleteInfoQuery)->count();

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
                        'count' => $noUiiCount,
                    ],
                    'no_lrn' => [
                        'count' => $noLrnCount,
                    ],
                    'duplicate_lrn' => [
                        'count' => count($duplicateLrnDetails),
                        'duplicates' => $duplicateLrns,
                        'students' => $duplicateLrnDetails,
                    ],
                    'no_award_number' => [
                        'count' => $noAwardNumberCount,
                    ],
                    'duplicate_award_numbers' => [
                        'count' => count($duplicateAwardDetails),
                        'students' => $duplicateAwardDetails,
                    ],
                    'incomplete_info' => [
                        'count' => $incompleteInfoCount,
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

    /**
     * Get paginated list of students without UII
     */
    public function getNoUiiStudents(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 5);

            $query = Student::where(function($q) {
                $q->whereNull('uii')->orWhere('uii', '');
            })->select('seq', 'surname', 'first_name', 'name_of_institution', 'award_number', 'scholarship_status');

            $total = $query->count();
            $students = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->toArray();

            return response()->json([
                'students' => $students,
                'total' => $total,
                'page' => (int)$page,
                'per_page' => (int)$perPage,
                'total_pages' => ceil($total / $perPage),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get paginated list of students without LRN
     */
    public function getNoLrnStudents(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 5);

            $query = Student::where(function($q) {
                $q->whereNull('learner_reference_number')->orWhere('learner_reference_number', '');
            })->select('seq', 'surname', 'first_name', 'name_of_institution', 'award_number', 'scholarship_status');

            $total = $query->count();
            $students = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->toArray();

            return response()->json([
                'students' => $students,
                'total' => $total,
                'page' => (int)$page,
                'per_page' => (int)$perPage,
                'total_pages' => ceil($total / $perPage),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get paginated list of students without Award Number
     */
    public function getNoAwardNumberStudents(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 5);

            $query = Student::where(function($q) {
                $q->whereNull('award_number')->orWhere('award_number', '');
            })->select('seq', 'surname', 'first_name', 'name_of_institution', 'scholarship_program', 'scholarship_status');

            $total = $query->count();
            $students = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->toArray();

            return response()->json([
                'students' => $students,
                'total' => $total,
                'page' => (int)$page,
                'per_page' => (int)$perPage,
                'total_pages' => ceil($total / $perPage),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get paginated list of students with incomplete information
     */
    public function getIncompleteInfoStudents(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 5);

            // Required fields - same as in stats()
            $requiredFields = [
                'surname', 'first_name', 'sex', 'date_of_birth',
                'contact_number', 'email_address', 'street_brgy', 'municipality_city', 
                'province', 'congressional_district', 'zip_code',
                'name_of_institution', 'uii', 'institutional_type', 'region', 
                'degree_program', 'program_degree_level',
                'in_charge', 'award_year', 'scholarship_program', 'award_number',
                'authority_type', 'authority_number', 'series', 'scholarship_status',
                'learner_reference_number', 'basis_cmo'
            ];

            $query = Student::where(function($query) use ($requiredFields) {
                foreach ($requiredFields as $field) {
                    $query->orWhere(function($q) use ($field) {
                        $q->whereNull($field)->orWhere($field, '');
                    });
                }
            })->select('seq', 'surname', 'first_name', 'name_of_institution', 'scholarship_program', 'scholarship_status');

            $total = (clone $query)->count();
            $students = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->toArray();

            // For each student, find which fields are missing
            $studentSeqs = array_column($students, 'seq');
            if (count($studentSeqs) > 0) {
                $fullStudents = Student::whereIn('seq', $studentSeqs)->get()->keyBy('seq');
                
                foreach ($students as &$student) {
                    $fullStudent = $fullStudents[$student['seq']] ?? null;
                    if ($fullStudent) {
                        $missingFields = [];
                        foreach ($requiredFields as $field) {
                            $value = $fullStudent->$field;
                            if ($value === null || $value === '') {
                                $missingFields[] = $field;
                            }
                        }
                        $student['missing_fields'] = $missingFields;
                        $student['missing_count'] = count($missingFields);
                    }
                }
            }

            return response()->json([
                'students' => $students,
                'total' => $total,
                'page' => (int)$page,
                'per_page' => (int)$perPage,
                'total_pages' => ceil($total / $perPage),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}