<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScholarshipProgramRecord;

class ScholarshipProgramRecordController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ScholarshipProgramRecord::all()
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