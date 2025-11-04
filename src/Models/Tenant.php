<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Models; 

class Tenant extends AbstractModel
{ 

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database',
        'prefix',
        'email',
        'phone',
        'document',
        'settings',
        'status',
        'is_primary',
        'description',
    ];

    protected $casts = [
        'settings' => 'array',
        'status' => 'string',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
