<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataCorrectionRequest extends Model
{
    protected $table = 'data_correction_requests';

    protected $fillable = [
        'employee_id',
        'requested_by',
        'category',
        'field_name',
        'current_value',
        'requested_value',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Available categories for correction requests.
     */
    public static function categories(): array
    {
        return [
            'personal_info'  => 'Personal Information',
            'employment'     => 'Employment Details',
            'payroll'        => 'Payroll & Compensation',
            'government_ids' => 'Government IDs & Numbers',
        ];
    }

    /**
     * Fields available for correction, grouped by category.
     */
    public static function correctableFields(): array
    {
        return [
            'personal_info' => [
                'first_name' => 'First Name',
                'last_name' => 'Last Name',
                'email' => 'Email Address',
                'phone' => 'Phone Number',
                'address' => 'Address',
            ],
            'employment' => [
                'employee_code' => 'Employee Code',
                'hire_date' => 'Hire Date',
                'employment_type' => 'Employment Type',
                'department_id' => 'Department',
                'position_id' => 'Position',
                'branch_id' => 'Branch',
            ],
            'payroll' => [
                'salary' => 'Salary',
                'bank_name' => 'Bank Name',
                'bank_account_number' => 'Bank Account Number',
            ],
            'government_ids' => [
                'tin' => 'TIN',
                'sss_number' => 'SSS Number',
                'philhealth_number' => 'PhilHealth Number',
                'pagibig_number' => 'Pag-IBIG Number',
            ],
        ];
    }
}
