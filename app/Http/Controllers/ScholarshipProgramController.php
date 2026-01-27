<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ScholarshipProgram;

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
            'in_charge'     => $request->in_charge,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Scholarship program created successfully',
            'data' => $program
        ], 201);
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
            'in_charge'     => $request->in_charge ?? $program->in_charge,
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
        $counts = \App\Models\Student::selectRaw('scholarship_program, COUNT(*) as total')
            ->groupBy('scholarship_program')
            ->get()
            ->keyBy('scholarship_program');

        foreach ($counts as $program => $row) {
        $total = $row->total;

        \App\Models\ScholarshipProgram::updateOrCreate(
            ['scholarship_program_name' => strtoupper(trim($program))], // normalize here
            [

                'filled_slot' => $total,
                'unfilled_slot' => \DB::raw("GREATEST(total_slot - $total, 0)"),
            ]
        );
    }


        return response()->json(['data' => \App\Models\ScholarshipProgram::all()]);
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
