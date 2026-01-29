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
        'program_id',
        'scholarship_program_name',
        'academic_year',
        'total_slot',
        'filled_slot',
        'unfilled_slot',
    ];

    public function programRecord()
    {
        return $this->belongsTo(\App\Models\ScholarshipProgramRecord::class, 'program_id');
    }
}
