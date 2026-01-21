<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(): JsonResponse
    {
        $students = \App\Models\Student::with('latestDisbursement')->get();

        // Add academic_year and semester from latest disbursement to each student
        $students = $students->map(function ($student) {
            $student->academic_year = $student->latestDisbursement->academic_year ?? null;
            $student->semester = $student->latestDisbursement->semester ?? null;
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
            'award_year' => 'nullable|integer|min:2000|max:2100',
            'scholarship_program' => 'nullable|string|max:191',
            'award_number' => 'nullable|string|max:191',
            'surname' => 'nullable|string|max:191',
            'first_name' => 'nullable|string|max:191',
            'middle_name' => 'nullable|string|max:191',
            'extension' => 'nullable|string|max:191',
            'sex' => 'nullable|in:Male,Female',
            'date_of_birth' => 'nullable|date',
            'contact_number' => 'nullable|string|max:191',
            'email_address' => 'nullable|email|max:191',
            'street_brgy' => 'nullable|string|max:191',
            'municipality_city' => 'nullable|string|max:191',
            'province' => 'nullable|string|max:191',
            'congressional_district' => 'nullable|string|max:191',
            'zip_code' => 'nullable|string|max:191',
            'special_group' => 'nullable|in:IP,PWD,Solo Parent',
            'certification_number' => 'nullable|string|max:191',
            'name_of_institution' => 'nullable|string|max:191',
            'uii' => 'nullable|string|max:191',
            'institutional_type' => 'nullable|string|max:191',
            'region' => 'nullable|string|max:191',
            'degree_program' => 'nullable|string|max:191',
            'program_major' => 'nullable|string|max:191',
            'program_discipline' => 'nullable|string|max:191',
            'program_degree_level' => 'nullable|in:Pre-baccalaureate,Baccalaureate,Post Baccalaureate,Masters,Doctorate',
            'authority_type' => 'nullable|in:GP,GR,RRPA,COPC',
            'authority_number' => 'nullable|string|max:191',
            'series' => 'nullable|string|max:191',
            'is_priority' => 'nullable|boolean',
            'basis_cmo' => 'nullable|string|max:191',
            'scholarship_status' => 'nullable|in:On-going,Graduated,Terminated',
            'replacement_info' => 'nullable|string',
            'termination_reason' => 'nullable|string',
        ]);

        $student = Student::create($validated);

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
            'award_year' => 'nullable|integer|min:2000|max:2100',
            'scholarship_program' => 'nullable|string|max:191',
            'award_number' => 'nullable|string|max:191',
            'surname' => 'nullable|string|max:191',
            'first_name' => 'nullable|string|max:191',
            'middle_name' => 'nullable|string|max:191',
            'extension' => 'nullable|string|max:191',
            'sex' => 'nullable|in:Male,Female',
            'date_of_birth' => 'nullable|date',
            'contact_number' => 'nullable|string|max:191',
            'email_address' => 'nullable|email|max:191',
            'street_brgy' => 'nullable|string|max:191',
            'municipality_city' => 'nullable|string|max:191',
            'province' => 'nullable|string|max:191',
            'congressional_district' => 'nullable|string|max:191',
            'zip_code' => 'nullable|string|max:191',
            'special_group' => 'nullable|in:IP,PWD,Solo Parent',
            'certification_number' => 'nullable|string|max:191',
            'name_of_institution' => 'nullable|string|max:191',
            'uii' => 'nullable|string|max:191',
            'institutional_type' => 'nullable|string|max:191',
            'region' => 'nullable|string|max:191',
            'degree_program' => 'nullable|string|max:191',
            'program_major' => 'nullable|string|max:191',
            'program_discipline' => 'nullable|string|max:191',
            'program_degree_level' => 'nullable|in:Pre-baccalaureate,Baccalaureate,Post Baccalaureate,Masters,Doctorate',
            'authority_type' => 'nullable|in:GP,GR,RRPA,COPC',
            'authority_number' => 'nullable|string|max:191',
            'series' => 'nullable|string|max:191',
            'is_priority' => 'nullable|boolean',
            'basis_cmo' => 'nullable|string|max:191',
            'scholarship_status' => 'nullable|in:On-going,Graduated,Terminated',
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
            $studentData = array_filter($studentData, fn($v) => $v !== '');

            // Only keep keys that actually exist in the DB table
            $allowed = (new Student())->getFillable();
            $filtered = array_intersect_key($studentData, array_flip($allowed));

            $created[] = Student::create($filtered);
        }

        return response()->json([
            'message' => 'Students imported successfully',
            'data' => $created
        ], 201);
    }
//ends here
}