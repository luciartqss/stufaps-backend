<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\LogService;
use App\Services\StudentLookupService;
use App\Models\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(): JsonResponse
    {
        // Eager load all disbursements so the frontend export has complete data
        $students = \App\Models\Student::with('disbursements')->get();

        // Add academic_year and semester from the latest disbursement (keep full list attached)
        $students = $students->map(function ($student) {
            $latest = $student->disbursements
                ->sortByDesc('disbursement_date')
                ->sortByDesc('created_at')
                ->first();

            $student->academic_year = $latest->academic_year ?? null;
            $student->semester = $latest->semester ?? null;

            return $student;
        });

        return response()->json($students);
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'in_charge' => 'nullable|string|max:191',
            'award_year' => 'nullable|string|max:191',
            'scholarship_program' => 'nullable|string|max:191',
            'award_number' => 'nullable|string|max:191',
            'learner_reference_number' => 'nullable|string|max:191',
            'surname' => 'nullable|string|max:191',
            'first_name' => 'nullable|string|max:191',
            'middle_name' => 'nullable|string|max:191',
            'extension' => 'nullable|string|max:191',
            'sex' => 'nullable|string|max:191',
            'date_of_birth' => 'nullable|string|max:191',
            'contact_number' => 'nullable|string|max:191',
            'email_address' => 'nullable|email|max:191',
            'street_brgy' => 'nullable|string|max:191',
            'municipality_city' => 'nullable|string|max:191',
            'province' => 'nullable|string|max:191',
            'congressional_district' => 'nullable|string|max:191',
            'zip_code' => 'nullable|string|max:191',
            'special_group' => 'nullable|string|max:191',
            'certification_number' => 'nullable|string|max:191',
            'name_of_institution' => 'nullable|string|max:191',
            'uii' => 'nullable|string|max:191',
            'institutional_type' => 'nullable|string|max:191',
            'region' => 'nullable|string|max:191',
            'degree_program' => 'nullable|string|max:191',
            'program_major' => 'nullable|string|max:191',
            'program_discipline' => 'nullable|string|max:191',
            'program_degree_level' => 'nullable|string|max:191',
            'authority_type' => 'nullable|string|max:191',
            'authority_number' => 'nullable|string|max:191',
            'series' => 'nullable|string|max:191',
            'is_priority' => 'nullable|string|max:191',
            'basis_cmo' => 'nullable|string|max:191',
            'scholarship_status' => 'nullable|string|max:191',
            'replacement_info' => 'nullable|string',
            'termination_reason' => 'nullable|string',
        ]);

        $student = Student::create($validated);

        // Log the creation
        LogService::log($student, $student->seq, 'create', null, $student->toArray());

        return response()->json([
            'message' => 'Student created successfully',
            'data' => $student
        ], 201);
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student): JsonResponse
    {
        // Load the student with their disbursements
        $student->load('disbursements');

        return response()->json($student);
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'in_charge' => 'nullable|string|max:191',
            'award_year' => 'nullable|string|max:191',
            'scholarship_program' => 'nullable|string|max:191',
            'award_number' => 'nullable|string|max:191',
            'learner_reference_number' => 'nullable|string|max:191',
            'surname' => 'nullable|string|max:191',
            'first_name' => 'nullable|string|max:191',
            'middle_name' => 'nullable|string|max:191',
            'extension' => 'nullable|string|max:191',
            'sex' => 'nullable|string|max:191',
            'date_of_birth' => 'nullable|string|max:191',
            'contact_number' => 'nullable|string|max:191',
            'email_address' => 'nullable|email|max:191',
            'street_brgy' => 'nullable|string|max:191',
            'municipality_city' => 'nullable|string|max:191',
            'province' => 'nullable|string|max:191',
            'congressional_district' => 'nullable|string|max:191',
            'zip_code' => 'nullable|string|max:191',
            'special_group' => 'nullable|string|max:191',
            'certification_number' => 'nullable|string|max:191',
            'name_of_institution' => 'nullable|string|max:191',
            'uii' => 'nullable|string|max:191',
            'institutional_type' => 'nullable|string|max:191',
            'region' => 'nullable|string|max:191',
            'degree_program' => 'nullable|string|max:191',
            'program_major' => 'nullable|string|max:191',
            'program_discipline' => 'nullable|string|max:191',
            'program_degree_level' => 'nullable|string|max:191',
            'authority_type' => 'nullable|string|max:191',
            'authority_number' => 'nullable|string|max:191',
            'series' => 'nullable|string|max:191',
            'is_priority' => 'nullable|string|max:191',
            'basis_cmo' => 'nullable|string|max:191',
            'scholarship_status' => 'nullable|string|max:191',
            'replacement_info' => 'nullable|string',
            'termination_reason' => 'nullable|string',
        ]);

        $student->update($validated);

        return response()->json([
            'message' => 'Student updated successfully',
            'data' => $student
        ]);
    }

    /**
     * Remove the specified student.
     */
    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully'
        ]);
    }
  
    
//jed code
    public function import(Request $request): JsonResponse
    {
        $students = $request->input('students', []);
        $created = [];

        foreach ($students as $studentData) {
            // Remove empty values
            $studentData = array_filter($studentData, fn($v) => $v !== '' && $v !== null);

            // Only keep keys that actually exist in the DB table
            $allowed = (new Student())->getFillable();
            $filtered = array_intersect_key($studentData, array_flip($allowed));

            // Skip if no valid data
            if (empty($filtered)) {
                continue;
            }

            $created[] = Student::create($filtered);
        }

        return response()->json([
            'message' => 'Students imported successfully',
            'data' => $created
        ], 201);
    }

    protected static function booted()
    {
        static::created(function () {
            app(\App\Http\Controllers\ScholarshipProgramController::class)->updateSlots();
        });
    }
//ends here


    /**
     * Bulk update a specific field for students.
     */
    public function bulkUpdateField(Request $request): JsonResponse
    {
        $field = $request->input('field');
        $oldValue = $request->input('old_value');
        $newValue = $request->input('new_value');

        // Allow all safe bulk-editable fields matching the frontend fieldOptions
        $allowedFields = [
            'degree_program',
            'program_major',
            'program_discipline',
            'program_degree_level',
            'name_of_institution',
            'institutional_type',
            'region',
            'province',
            'municipality_city',
            'street_brgy',
            'congressional_district',
            'scholarship_program',
            'scholarship_status',
            'special_group',
            'authority_type',
            'authority_number',
            'series',
            'basis_cmo',
            'termination_reason',
            'replacement_info',
        ];

        if (!in_array($field, $allowedFields)) {
            return response()->json(['error' => 'Invalid field'], 400);
        }

        $count = Student::where($field, $oldValue)->update([$field => $newValue]);

        // Log only ONE entry for the entire bulk edit action
        Log::create([
            'model' => 'Student',
            'model_id' => 0, // Use 0 to indicate bulk action
            'action' => 'update',
            'old_data' => json_encode([$field => $oldValue]),
            'new_data' => json_encode([$field => $newValue]),
            'changed_fields' => $field,
            'user_id' => auth()->id() ?? null,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'message' => "Updated $count records.",
            'updated_count' => $count,
        ]);
    }

    /**
     * Generate the masterlist PDF for a given program, semester, and academic year.
     */
    public function masterlist(Request $request)
    {
        $validated = $request->validate([
            'program' => 'required|string',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'prepared_name' => 'nullable|array',
            'prepared_name.*' => 'nullable|string',
            'prepared_position' => 'nullable|array',
            'prepared_position.*' => 'nullable|string',
            'reviewed_name' => 'nullable|array',
            'reviewed_name.*' => 'nullable|string',
            'reviewed_position' => 'nullable|array',
            'reviewed_position.*' => 'nullable|string',
            'approved_name' => 'nullable|string',
            'approved_position' => 'nullable|string',
        ]);

        $program = $validated['program'];
        $semester = $validated['semester'];
        $academicYear = $validated['academic_year'];

        // Signature fields - prepared by supports 1 or 2 entries
        $preparedNames = $validated['prepared_name'] ?? [];
        $preparedPositions = $validated['prepared_position'] ?? [];
        $preparedBy = [];
        for ($i = 0; $i < max(count($preparedNames), count($preparedPositions)); $i++) {
            $preparedBy[] = [
                'name' => $preparedNames[$i] ?? '',
                'position' => $preparedPositions[$i] ?? '',
            ];
        }
        
        $reviewedNames = $validated['reviewed_name'] ?? [];
        $reviewedPositions = $validated['reviewed_position'] ?? [];
        $reviewedBy = [];
        for ($i = 0; $i < max(count($reviewedNames), count($reviewedPositions)); $i++) {
            $reviewedBy[] = [
                'name' => $reviewedNames[$i] ?? '',
                'position' => $reviewedPositions[$i] ?? '',
            ];
        }
        $approvedName = $validated['approved_name'] ?? '';
        $approvedPosition = $validated['approved_position'] ?? 'Director IV';

        $students = Student::with(['disbursements' => function ($query) use ($academicYear, $semester) {
            $query->where('academic_year', $academicYear)
                  ->where('semester', $semester);
        }])
            ->where('scholarship_program', $program)
            ->whereHas('disbursements', function ($query) use ($academicYear, $semester) {
                $query->where('academic_year', $academicYear)
                      ->where('semester', $semester);
            })
            ->orderBy('surname')
            ->orderBy('first_name')
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'message' => 'No students found for the selected filters.',
            ], 404);
        }

        $students = $students->map(function ($student) {
            $matching = $student->disbursements;

            $primary = $matching
                ->sortByDesc('disbursement_date')
                ->sortByDesc('created_at')
                ->first();

            $student->current_year_level = $primary->curriculum_year_level ?? null;
            $student->financial_benefits = $matching->sum(function ($disbursement) {
                $payment = $disbursement->payment_amount ?? null;
                if ($payment === null) {
                    $payment = $disbursement->amount ?? 0;
                }
                return (float) $payment;
            });
            $student->remarks = $primary->remarks ?? null;

            return $student;
        });

        $totalBenefits = $students->sum(fn ($student) => $student->financial_benefits ?? 0);

        $pdf = Pdf::loadView('PDFs.masterlist', [
            'students' => $students,
            'program' => $program,
            'semester' => $semester,
            'academicYear' => $academicYear,
            'totalBenefits' => $totalBenefits,
            'preparedBy' => $preparedBy,
            'reviewedBy' => $reviewedBy,
            'approvedName' => $approvedName,
            'approvedPosition' => $approvedPosition,
        ])->setPaper('folio', 'landscape');

        return $pdf->download("masterlist-{$program}-{$semester}-AY-{$academicYear}.pdf");
    }

    /**
     * Lookup authority/program info for a student based on UII and degree_program
     */
    public function lookupProgramInfo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uii' => 'nullable|string',
            'name_of_institution' => 'nullable|string',
            'degree_program' => 'nullable|string',
            'program_major' => 'nullable|string',
        ]);

        $lookupService = app(StudentLookupService::class);
        $result = $lookupService->fillMissingFields($validated);

        return response()->json([
            'uii' => $result['uii'] ?? null,
            'name_of_institution' => $result['name_of_institution'] ?? null,
            'institutional_type' => $result['institutional_type'] ?? null,
            'authority_type' => $result['authority_type'] ?? null,
            'authority_number' => $result['authority_number'] ?? null,
            'series' => $result['series'] ?? null,
            'program_discipline' => $result['program_discipline'] ?? null,
            'program_degree_level' => $result['program_degree_level'] ?? null,
        ]);
    }

    /**
     * Fill missing fields for an existing student
     */
    public function fillMissingFields(Student $student): JsonResponse
    {
        $student->autoFillFromLookup();
        $student->save();

        return response()->json([
            'message' => 'Student fields updated from lookup',
            'data' => $student
        ]);
    }

    /**
     * Get list of institutions for dropdown
     */
    public function getInstitutions(): JsonResponse
    {
        $lookupService = app(StudentLookupService::class);
        return response()->json($lookupService->getInstitutions());
    }

    /**
     * Get programs for a specific institution
     */
    public function getProgramsByUii(Request $request): JsonResponse
    {
        $uii = $request->input('uii');
        if (!$uii) {
            return response()->json([]);
        }

        $lookupService = app(StudentLookupService::class);
        return response()->json($lookupService->getProgramsByUii($uii));
    }

    /**
     * Search institutions by name (for autocomplete)
     */
    public function searchInstitutions(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $lookupService = app(StudentLookupService::class);
        return response()->json($lookupService->searchInstitutions($search));
    }

    /**
     * Debug lookup - diagnose why auto-fill might not be working
     */
    public function debugLookup(Request $request): JsonResponse
    {
        $data = $request->only(['uii', 'name_of_institution', 'degree_program', 'program_major']);
        $lookupService = app(StudentLookupService::class);
        return response()->json($lookupService->debugLookup($data));
    }
}