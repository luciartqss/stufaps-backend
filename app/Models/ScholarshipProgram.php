<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipProgram extends Model
{
    use HasFactory;

    // ✅ Table name (optional if it matches plural form)
    protected $table = 'scholarship_programs';

    // ✅ Mass assignable fields
    protected $fillable = [
        'scholarship_program_name',
        'description',
        'total_slot',
        'filled_slot',
        'unfilled_slot',
        'in_charge',
    ];
}
