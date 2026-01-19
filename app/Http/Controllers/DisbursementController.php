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
            'academic_year' => 'required|string|max:191',
            'semester' => 'required|string|max:191',
            'curriculum_year_level' => 'required|in:I,II,III,IV,V,VI',
            'nta' => 'required|string|max:191',
            'fund_source' => 'required|string|max:191',
            'amount' => 'required|numeric|min:0',
            'voucher_number' => 'required|string|max:191',
            'mode_of_payment' => 'required|in:ATM,Cheque,Through the HEI',
            'account_check_no' => 'nullable|string|max:191',
            'payment_amount' => 'required|numeric|min:0',
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
            'academic_year' => 'sometimes|required|string|max:191',
            'semester' => 'sometimes|required|string|max:191',
            'curriculum_year_level' => 'sometimes|required|in:I,II,III,IV,V,VI',
            'nta' => 'sometimes|required|string|max:191',
            'fund_source' => 'sometimes|required|string|max:191',
            'amount' => 'sometimes|required|numeric|min:0',
            'voucher_number' => 'sometimes|required|string|max:191',
            'mode_of_payment' => 'sometimes|required|in:ATM,Cheque,Through the HEI',
            'account_check_no' => 'nullable|string|max:191',
            'payment_amount' => 'sometimes|required|numeric|min:0',
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