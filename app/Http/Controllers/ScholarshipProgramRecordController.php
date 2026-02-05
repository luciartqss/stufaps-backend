<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\ScholarshipProgramRecord;

class ScholarshipProgramRecordController extends Controller
{

        public function index()
{
    // Per-year rows
    $perYear = DB::table('scholarship_program_records as s')
        ->leftJoin('students as st', 's.scholarship_program_name', '=', 'st.scholarship_program')
        ->leftJoin('disbursements as d', function ($join) {
            $join->on('d.student_seq', '=', 'st.seq')
                 ->on('d.academic_year', '=', 's.Academic_year');
        })
        ->select(
            's.Academic_year',
            's.scholarship_program_name',
            's.description',
            DB::raw('MAX(s.total_slot) AS total_slot'),
            DB::raw("COUNT(DISTINCT CASE WHEN st.scholarship_status <> 'terminated' THEN d.student_seq END) AS total_students"),
            DB::raw("(MAX(s.total_slot) - COUNT(DISTINCT CASE WHEN st.scholarship_status <> 'terminated' THEN d.student_seq END)) AS unfilled_slot")
        )
        ->groupBy('s.Academic_year', 's.scholarship_program_name', 's.description');

    // Global "All" row
    $all = DB::table('scholarship_program_records as s')
        ->leftJoin('students as st', 's.scholarship_program_name', '=', 'st.scholarship_program')
        ->select(
            DB::raw("'All' AS Academic_year"),
            's.scholarship_program_name',
            's.description',
            DB::raw('SUM(s.total_slot) AS total_slot'),
            DB::raw("COUNT(DISTINCT CASE WHEN st.scholarship_status <> 'terminated' THEN st.seq END) AS total_students"),
            DB::raw("(SUM(s.total_slot) - COUNT(DISTINCT CASE WHEN st.scholarship_status <> 'terminated' THEN st.seq END)) AS unfilled_slot")
        )
        ->groupBy('s.scholarship_program_name', 's.description');

    // Combine per-year + All
    $programs = $perYear->unionAll($all)
        ->orderBy('Academic_year')
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
            'Academic_year'            => 'required|string|max:20',
        ]);

        $program = ScholarshipProgramRecord::create($validated);

        return response()->json([
            'message' => 'Scholarship program inserted successfully!',
            'program' => $program,
        ], 201);
    }

    public function getPrograms(): JsonResponse
    {
        $raw = DB::table('scholarship_program_records as s')
        ->leftJoin('students as st', 's.scholarship_program_name', '=', 'st.scholarship_program')
        ->leftJoin('disbursements as d', function ($join) {
            $join->on('d.student_seq', '=', 'st.seq')
                ->on('d.academic_year', '=', 's.Academic_year');
    })
    ->select(
        's.id',
        's.scholarship_program_name',
        's.description',
        's.Academic_year',
        's.total_slot',
        DB::raw("COUNT(DISTINCT CASE WHEN st.scholarship_status <> 'terminated' THEN d.student_seq END) AS total_students"),
        DB::raw("(s.total_slot - COUNT(DISTINCT CASE WHEN st.scholarship_status <> 'terminated' THEN d.student_seq END)) AS unfilled_slot")
    )
        ->groupBy('s.id','s.scholarship_program_name','s.description','s.Academic_year','s.total_slot') 
        ->get();

        $grouped = $raw->groupBy('scholarship_program_name')->map(function ($items) {
            return [
                'id' => $items->first()->id,
                'scholarship_program_name' => $items->first()->scholarship_program_name,
                'description' => $items->first()->description,
                'years' => $items->map(function ($i) {
                    return [
                        'Academic_year' => $i->Academic_year,
                        'total_slot' => $i->total_slot,
                        'total_students' => $i->total_students ?? 0, 
                        'unfilled_slot' => $i->unfilled_slot ?? 0,
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'success' => true,
            'programs' => $grouped
        ]);
    }


    public function updateSlots(Request $request) : JsonResponse
    {

        $program = ScholarshipProgramRecord::findOrFail($request->id);

        $program->total_slot = $request->total_slot;
        $program->Academic_year = $request->Academic_year;
        $program->description = $request->description;

        $program->save();

        return response()->json([
            'success' => true,
            'program' => $program
        ]);
    }
  
}