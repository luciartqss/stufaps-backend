<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\LogService;

class Disbursement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_seq',
        'academic_year',
        'semester',
        'curriculum_year_level',
        'nta',
        'fund_source',
        'amount',
        'voucher_number',
        'mode_of_payment',
        'account_check_no',
        'payment_amount',
        'lddap_number',
        'disbursement_date',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'disbursement_date' => 'date',
    ];

    /**
     * Get the student that owns the disbursement.
     *
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_seq', 'seq');
    }

    // Boot method for logging CRUD operations
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            LogService::log($model, $model->id, 'create', null, $model->toArray());
        });

        static::updating(function ($model) {
            $oldData = $model->getOriginal();
            $newData = $model->getAttributes();
            LogService::log($model, $model->id, 'update', $oldData, $newData);
        });

        static::deleting(function ($model) {
            LogService::log($model, $model->id, 'delete', $model->toArray(), null);
        });
    }
}