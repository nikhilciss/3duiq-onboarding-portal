<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'crm_enabled',
        'reporting_enabled',
        'api_enabled',
        'crm_vhost',
        'reporting_vhost',
        'api_vhost',
        'databases_created',
    ];

    protected $casts = [
        'crm_enabled' => 'boolean',
        'reporting_enabled' => 'boolean',
        'api_enabled' => 'boolean',
        'databases_created' => 'array',
    ];
}
