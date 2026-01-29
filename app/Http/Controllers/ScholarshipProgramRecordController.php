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
}
