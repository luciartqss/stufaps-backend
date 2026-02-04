<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramOfferingEntry extends Model
{
    protected $table = 'program_offering_entries';

    protected $fillable = [
        'uii',
        'is_active',
        'hei_name',
        'municipality_city',
        'province',
        'institutional_type',
        'program',
        'major_specialization',
        'discipline_group',
        'program_level',
        'ga_level_i',
        'ga_level_ii',
        'ga_level_iii',
        'ga_level_iv',
        'ga_level_v',
        'ga_level_vi',
        'accreditation_level',
        'accreditation_accreditor',
        'accreditation_validity',
        'coe_cod',
        'validity',
        'gpr',
        'gp_gr_no',
        'series',
        'issued_by',
        'remarks',
        'remarks2',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the institution (directory entry) for this program
     */
    public function directoryEntry(): BelongsTo
    {
        return $this->belongsTo(DirectoryEntry::class, 'uii', 'uii');
    }
}
