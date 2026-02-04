<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DirectoryEntry extends Model
{
    protected $table = 'directory_entries';

    protected $fillable = [
        'uii',
        'name',
        'name_registered_sec',
        'former_names',
        'is_active',
        'remarks_status',
        'institutional_type',
        'sector',
        'year_established',
        'autonomous_status',
        'autonomous_validity',
        'complete_address',
        'street_brgy',
        'municipality_city',
        'province',
        'district',
        'contact_numbers',
        'mobile_numbers',
        'email_address',
        'head_name',
        'head_designation',
        'head_telephone',
        'head_mobile',
        'head_email',
        'registrar_name',
        'registrar_telephone',
        'registrar_mobile',
        'registrar_email',
        'additional_remarks',
        'head_name_alt',
        'head_designation_alt',
        'name_alt',
        'complete_address_alt',
        'email_address_alt',
        'head_email_alt',
        'registrar_email_alt',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'year_established' => 'integer',
    ];

    /**
     * Get all program offerings for this institution
     */
    public function programOfferings(): HasMany
    {
        return $this->hasMany(ProgramOfferingEntry::class, 'uii', 'uii');
    }

    /**
     * Get all students enrolled in this institution
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'uii', 'uii');
    }
}
