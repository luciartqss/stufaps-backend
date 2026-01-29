<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ScholarshipProgram;
use Illuminate\Support\Facades\DB;

class ScholarshipProgramController extends Controller
{
    /**
     * Display a listing of scholarship programs.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ScholarshipProgram::all()
        ]);
    }

    /**
     * Store a newly created scholarship program.
     */
    public function store(Request $request)
    {
        $program = ScholarshipProgram::create([
            'scholarship_program_name'  => $request->scholarship_program_name,
            'total_slot'    => $request->total_slot,
            'filled_slot'   => $request->filled_slot,
            'unfilled_slot' => $request->total_slot - $request->filled_slot, // auto compute
            'academic_year' => $request->academic_year,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Scholarship program created successfully',
            'data' => $program
        ], 191);
    }



    /**
     * Show a specific scholarship program.
     */
    public function show($id)
    {
        $program = ScholarshipProgram::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $program
        ]);
    }

    /**
     * Update an existing scholarship program.
     */
    public function update(Request $request, $id)
    {
        $program = ScholarshipProgram::findOrFail($id);

        $program->update([
            'program_name'  => $request->program_name ?? $program->program_name,
            'total_slot'    => $request->total_slot ?? $program->total_slot,
            'filled_slot'   => $request->filled_slot ?? $program->filled_slot,
            'unfilled_slot' => ($request->total_slot ?? $program->total_slot) 
                                - ($request->filled_slot ?? $program->filled_slot),
        
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Scholarship program updated successfully',
            'data' => $program
        ]);
    }

    /**
     * Remove a scholarship program.
     */
    public function destroy($id)
    {
        ScholarshipProgram::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Program deleted successfully'
        ]);
    }

    public function totals()
    {
        return response()->json([
            'total_slots' => ScholarshipProgram::sum('total_slot'),
            'total_filled' => ScholarshipProgram::sum('filled_slot'),
            'total_unfilled' => ScholarshipProgram::sum('unfilled_slot'),
        ]);
    }

    public function updateSlots(): JsonResponse
{
    $counts = \DB::table('students')
        ->join('disbursements', 'students.seq', '=', 'disbursements.student_seq')
        ->select(
            'students.scholarship_program',
            'disbursements.academic_year',
            \DB::raw('COUNT(DISTINCT students.seq) as total')
        )
        ->groupBy('students.scholarship_program', 'disbursements.academic_year')
        ->get();

    foreach ($counts as $row) {
        $programName  = strtoupper(trim($row->scholarship_program));
        $academicYear = $row->academic_year;
        $total        = $row->total;

        // Find program in master list
        $programRecord = \App\Models\ScholarshipProgramRecord::where(
            'scholarship_program_name',
            $programName
        )->first();

        if ($programRecord) {
            \App\Models\ScholarshipProgram::updateOrCreate(
                [
                    'program_id'    => $programRecord->id,
                    'academic_year' => $academicYear,
                ],
                [
                    'scholarship_program_name' => $programRecord->scholarship_program_name, // ✅ added
                    'total_slot'    => $programRecord->total_slot,
                    'filled_slot'   => $total,
                    'unfilled_slot' => max($programRecord->total_slot - $total, 0),
                ]
            );
        }
    }

    return response()->json([
        'success' => true,
        'data'    => \App\Models\ScholarshipProgram::with('programRecord')
                        ->orderBy('academic_year')
                        ->get()
    ]);
}


    public function editSlot(Request $request)
    {
        $program = ScholarshipProgram::findOrFail($request->id);
        $program->total_slot = $request->slots;
        $program->unfilled_slot = $request->slots - $program->filled_slot;
        $program->save();

        return response()->json(['success' => true, 'program' => $program]);
    }



}
