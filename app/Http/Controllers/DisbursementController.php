<?php

namespace App\Http\Controllers;

use App\Models\Disbursement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DisbursementController extends Controller
{
    /**
     * Display a listing of disbursements.
     */
    public function index(): JsonResponse
    {
        $disbursements = Disbursement::with('student')->get();
        return response()->json($disbursements);
    }

    /**
     * Store a newly created disbursement.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_seq' => 'required|exists:students,seq',
            'academic_year' => 'nullable|string|max:191',
            'semester' => 'nullable|string|max:191',
            'curriculum_year_level' => 'nullable|in:I,II,III,IV,V,VI',
            'nta' => 'nullable|string|max:191',
            'fund_source' => 'nullable|string|max:191',
            'amount' => 'nullable|numeric|min:0',
            'voucher_number' => 'nullable|string|max:191',
            'mode_of_payment' => 'nullable|in:ATM,Cheque,Through the HEI',
            'account_check_no' => 'nullable|string|max:191',
            'payment_amount' => 'nullable|numeric|min:0',
            'lddap_number' => 'nullable|string|max:191',
            'disbursement_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $disbursement = Disbursement::create($validated);

        return response()->json([
            'message' => 'Disbursement created successfully',
            'data' => $disbursement
        ], 201);
    }

    /**
     * Display the specified disbursement.
     */
    public function show(Disbursement $disbursement): JsonResponse
    {
        $disbursement->load('student');
        return response()->json($disbursement);
    }

    /**
     * Update the specified disbursement.
     */
    public function update(Request $request, Disbursement $disbursement): JsonResponse
    {
        $validated = $request->validate([
            'student_seq' => 'sometimes|required|exists:students,seq',
            'academic_year' => 'nullable|string|max:191',
            'semester' => 'nullable|string|max:191',
            'curriculum_year_level' => 'nullable|in:I,II,III,IV,V,VI',
            'nta' => 'nullable|string|max:191',
            'fund_source' => 'nullable|string|max:191',
            'amount' => 'nullable|numeric|min:0',
            'voucher_number' => 'nullable|string|max:191',
            'mode_of_payment' => 'nullable|in:ATM,Cheque,Through the HEI',
            'account_check_no' => 'nullable|string|max:191',
            'payment_amount' => 'nullable|numeric|min:0',
            'lddap_number' => 'nullable|string|max:191',
            'disbursement_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $disbursement->update($validated);

        return response()->json([
            'message' => 'Disbursement updated successfully',
            'data' => $disbursement
        ]);
    }

    /**
     * Remove the specified disbursement.
     */
    public function destroy(Disbursement $disbursement): JsonResponse
    {
        $disbursement->delete();

        return response()->json([
            'message' => 'Disbursement deleted successfully'
        ]);
    }
}