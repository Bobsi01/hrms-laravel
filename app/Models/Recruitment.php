<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recruitment extends Model
{
    protected $table = 'recruitment';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'position_applied',
        'template_id',
        'converted_employee_id',
        'resume_path',
        'status',
        'notes',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(RecruitmentFile::class, 'recruitment_id');
    }

    public function template()
    {
        return $this->belongsTo(RecruitmentTemplate::class, 'template_id');
    }
}
