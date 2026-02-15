<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyConsent extends Model
{
    protected $table = 'privacy_consents';

    protected $fillable = [
        'user_id',
        'consent_type',
        'consented',
        'consented_at',
        'withdrawn_at',
        'ip_address',
        'user_agent',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'consented' => 'boolean',
            'consented_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Available consent types per RA 10173.
     */
    public static function consentTypes(): array
    {
        return [
            'data_processing' => [
                'label' => 'Data Processing Consent',
                'description' => 'I consent to the collection, processing, and storage of my personal information for employment purposes as required by the Data Privacy Act of 2012 (RA 10173).',
                'required' => true,
            ],
            'data_sharing' => [
                'label' => 'Data Sharing Consent',
                'description' => 'I consent to the sharing of my personal information with government agencies (SSS, PhilHealth, Pag-IBIG, BIR) for statutory compliance purposes.',
                'required' => true,
            ],
            'marketing' => [
                'label' => 'Communications Consent',
                'description' => 'I consent to receiving company communications, announcements, and updates via email and in-app notifications.',
                'required' => false,
            ],
        ];
    }
}
