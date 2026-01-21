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
            'in_charge' => 'required|string|max:191',
            'award_year' => 'required|integer|min:2000|max:2100',
            'scholarship_program' => 'required|string|max:191',
            'award_number' => 'required|string|max:191',
            'surname' => 'required|string|max:191',
            'first_name' => 'required|string|max:191',
            'middle_name' => 'required|string|max:191',
            'extension' => 'nullable|string|max:191',
            'sex' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'contact_number' => 'required|string|max:191',
            'email_address' => 'required|email|max:191',
            'street_brgy' => 'required|string|max:191',
            'municipality_city' => 'required|string|max:191',
            'province' => 'required|string|max:191',
            'congressional_district' => 'required|string|max:191',
            'zip_code' => 'required|string|max:191',
            'special_group' => 'nullable|in:IP,PWD,Solo Parent',
            'certification_number' => 'nullable|string|max:191',
            'name_of_institution' => 'required|string|max:191',
            'uii' => 'required|string|max:191',
            'institutional_type' => 'required|string|max:191',
            'region' => 'required|string|max:191',
            'degree_program' => 'required|string|max:191',
            'program_major' => 'required|string|max:191',
            'program_discipline' => 'required|string|max:191',
            'program_degree_level' => 'required|in:Pre-baccalaureate,Baccalaureate,Post Baccalaureate,Masters,Doctorate',
            'authority_type' => 'required|in:GP,GR,RRPA,COPC',
            'authority_number' => 'required|string|max:191',
            'series' => 'required|string|max:191',
            'is_priority' => 'required|boolean',
            'basis_cmo' => 'nullable|string|max:191',
            'scholarship_status' => 'required|in:On-going,Graduated,Terminated',
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
            'in_charge' => 'sometimes|required|string|max:191',
            'award_year' => 'sometimes|required|integer|min:2000|max:2100',
            'scholarship_program' => 'sometimes|required|string|max:191',
            'award_number' => 'sometimes|required|string|max:191',
            'surname' => 'sometimes|required|string|max:191',
            'first_name' => 'sometimes|required|string|max:191',
            'middle_name' => 'sometimes|required|string|max:191',
            'extension' => 'nullable|string|max:191',
            'sex' => 'sometimes|required|in:Male,Female',
            'date_of_birth' => 'sometimes|required|date',
            'contact_number' => 'sometimes|required|string|max:191',
            'email_address' => 'sometimes|required|email|max:191',
            'street_brgy' => 'sometimes|required|string|max:191',
            'municipality_city' => 'sometimes|required|string|max:191',
            'province' => 'sometimes|required|string|max:191',
            'congressional_district' => 'sometimes|required|string|max:191',
            'zip_code' => 'sometimes|required|string|max:191',
            'special_group' => 'nullable|in:IP,PWD,Solo Parent',
            'certification_number' => 'nullable|string|max:191',
            'name_of_institution' => 'sometimes|required|string|max:191',
            'uii' => 'sometimes|required|string|max:191',
            'institutional_type' => 'sometimes|required|string|max:191',
            'region' => 'sometimes|required|string|max:191',
            'degree_program' => 'sometimes|required|string|max:191',
            'program_major' => 'sometimes|required|string|max:191',
            'program_discipline' => 'sometimes|required|string|max:191',
            'program_degree_level' => 'sometimes|required|in:Pre-baccalaureate,Baccalaureate,Post Baccalaureate,Masters,Doctorate',
            'authority_type' => 'sometimes|required|in:GP,GR,RRPA,COPC',
            'authority_number' => 'sometimes|required|string|max:191',
            'series' => 'sometimes|required|string|max:191',
            'is_priority' => 'sometimes|required|boolean',
            'basis_cmo' => 'nullable|string|max:191',
            'scholarship_status' => 'sometimes|required|in:On-going,Graduated,Terminated',
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
}