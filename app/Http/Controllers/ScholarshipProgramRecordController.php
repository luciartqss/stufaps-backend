<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScholarshipProgramRecords;

class ScholarshipProgramRecordController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ScholarshipProgramRecords::all()
        ]);
    }

    public function show($id)
    {
        $program = ScholarshipProgramRecords::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $program
        ]);
    }
    
    public function store(Request $request)
    {
        $program = ScholarshipProgramRecords::create($request->only([
            'scholarship_program_name',
            'description'
        ]));

        return response()->json([
            'success' => true,
            'data' => $program
        ], 201);
    }
            
    public function update(Request $request, $id)
    {
        $program = ScholarshipProgramRecords::findOrFail($id);
        $program->update($request->only([
            'scholarship_program_name',
            'description'
        ]));

        return response()->json([
            'success' => true,
            'data' => $program
        ]);
    }
    
    public function destroy($id)
    {
        $program = ScholarshipProgramRecords::findOrFail($id);
        $program->delete();

        return response()->json([
            'success' => true,
            'message' => 'Program deleted successfully'
        ]);
    }


    /**
     * Totals across all programs.
     */
    public function totals()
    {
        return response()->json([
            'total_slots'   => ScholarshipProgram::sum('total_slot'),
            'total_filled'  => ScholarshipProgram::sum('filled_slot'),
            'total_unfilled'=> ScholarshipProgram::sum('unfilled_slot'),
        ]);
    }

    /**
     * Sync slots dynamically from disbursements.
     */
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

            $programRecord = ScholarshipProgramRecord::where(
                'scholarship_program_name',
                $programName
            )->first();

            if ($programRecord) {
                ScholarshipProgram::updateOrCreate(
                    [
                        'program_id'    => $programRecord->id,
                        'academic_year' => $academicYear,
                    ],
                    [
                        'scholarship_program_name' => $programRecord->scholarship_program_name,
                        'total_slot'    => $programRecord->total_slot,
                        'filled_slot'   => $total,
                        'unfilled_slot' => max($programRecord->total_slot - $total, 0),
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'data'    => ScholarshipProgram::with('programRecord')
                            ->orderBy('academic_year')
                            ->get()
        ]);
    }

    /**
     * Edit slot count manually.
     */
    public function editSlot(Request $request)
    {
        $program = ScholarshipProgram::findOrFail($request->id);
        $program->total_slot = $request->slots;
        $program->unfilled_slot = max($request->slots - $program->filled_slot, 0);
        $program->save();

        return response()->json(['success' => true, 'program' => $program]);
    }













    
}
