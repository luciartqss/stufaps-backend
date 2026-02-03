<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ScholarshipProgramRecord;

class ScholarshipProgramRecordController extends Controller
{

    public function index()
    {
        $programs = DB::table('scholarship_program_records as s')
            ->leftJoin('students as st', 's.scholarship_program_name', '=', 'st.scholarship_program')
            ->leftJoin('disbursements as d', function ($join) {
                $join->on('d.student_seq', '=', 'st.seq')
                     ->on('d.academic_year', '=', 's.Academic_year');
            })
            ->select(
                's.id',
                's.Academic_year',
                's.scholarship_program_name',
                DB::raw('MAX(s.total_slot) AS total_slot'),
                DB::raw("COUNT(DISTINCT CASE WHEN st.scholarship_status <> 'terminated' THEN d.student_seq END) AS total_students"),
                DB::raw("(MAX(s.total_slot) - COUNT(DISTINCT CASE WHEN st.scholarship_status <> 'terminated' THEN d.student_seq END)) AS unfilled_slot")
            )
            ->groupBy('s.id', 's.Academic_year', 's.scholarship_program_name', 'st.scholarship_status')
            ->orderBy('s.Academic_year')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $programs
        ]);
    }

    public function show($id)
    {
        $program = ScholarshipProgramRecord::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $program
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'scholarship_program_name' => 'nullable|string|max:191',
            'description'              => 'nullable|string|max:255',
            'total_slot'               => 'nullable|integer|min:0',
            'academic_year'            => 'required|string|max:20',
        ]);

        $program = ScholarshipProgramRecord::create($validated);

        return response()->json([
            'message' => 'Scholarship program inserted successfully!',
            'program' => $program,
        ], 201);
    }


    public function slotCount()
{
    
}

    /**
     * Edit slot count manually.
     */
    public function editSlot(Request $request)
    {
        $program = ScholarshipProgramRecord::findOrFail($request->id);
        $program->total_slot = $request->slots;
        $program->unfilled_slot = max($request->slots - $program->filled_slot, 0);
        $program->save();

        return response()->json([
            'success' => true, 
            'program' => $program]);
    }   
}