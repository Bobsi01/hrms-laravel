<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentTemplate extends Model
{
    protected $table = 'recruitment_templates';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];
}
