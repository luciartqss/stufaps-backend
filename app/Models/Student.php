<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\LogService;
use App\Services\StudentLookupService;

class Student extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'seq';

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'seq';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'in_charge',
        'award_year',
        'scholarship_program',
        'award_number',
        'surname',
        'first_name',
        'middle_name',
        'extension',
        'sex',
        'date_of_birth',
        'contact_number',
        'email_address',
        'street_brgy',
        'municipality_city',
        'province',
        'congressional_district',
        'zip_code',
        'special_group',
        'certification_number',
        'name_of_institution',
        'uii',
        'institutional_type',
        'region',
        'degree_program',
        'program_major',
        'program_discipline',
        'program_degree_level',
        'authority_type',
        'authority_number',
        'series',
        'is_priority',
        'basis_cmo',
        'scholarship_status',
        'replacement_info',
        'termination_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'award_year' => 'integer',
        'is_priority' => 'boolean',
    ];

    /**
     * Get the full name of the student.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $name = "{$this->surname}, {$this->first_name} {$this->middle_name}";
        if ($this->extension) {
            $name .= " {$this->extension}";
        }
        return $name;
    }

    /**
     * Get all disbursements for the student.
     *
     * @return HasMany
     */
    public function disbursements(): HasMany
    {
        return $this->hasMany(Disbursement::class, 'student_seq', 'seq');
    }

    /**
     * Get the latest disbursement for the student.
     */
    public function latestDisbursement()
    {
        return $this->hasOne(\App\Models\Disbursement::class, 'student_seq', 'seq')->latestOfMany();
    }

    // Boot method for logging CRUD operations and auto-fill
    protected static function boot()
    {
        parent::boot();

        // Auto-fill missing fields from directory/program offerings before creating
        static::creating(function ($model) {
            $model->autoFillFromLookup();
        });

        // Also auto-fill when updating if fields are still empty
        static::updating(function ($model) {
            $model->autoFillFromLookup();
            $oldData = $model->getOriginal();
            $newData = $model->getAttributes();
            LogService::log($model, $model->seq, 'update', $oldData, $newData);
        });

        static::created(function ($model) {
            LogService::log($model, $model->seq, 'create', null, $model->toArray());
        });

        static::deleting(function ($model) {
            LogService::log($model, $model->seq, 'delete', $model->toArray(), null);
        });
    }

    /**
     * Auto-fill missing fields from directory_entries and program_offering_entries
     * Only fills fields that are null/empty - does not overwrite existing data
     */
    public function autoFillFromLookup(): void
    {
        $lookupService = app(StudentLookupService::class);
        $data = $lookupService->fillMissingFields($this->getAttributes());

        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable) && empty($this->$key) && !empty($value)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Get the related directory entry (institution)
     */
    public function directoryEntry()
    {
        return $this->belongsTo(\App\Models\DirectoryEntry::class, 'uii', 'uii');
    }
}