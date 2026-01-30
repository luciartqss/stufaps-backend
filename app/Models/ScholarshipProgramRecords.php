<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScholarshipProgramRecords extends Model
{
    protected $table = 'scholarship_program_records';
    protected $fillable = [
        'scholarship_program_name',
        'description',
        'total_slot',
        'academic_year'
    ];

}
