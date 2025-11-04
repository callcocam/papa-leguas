<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Shinobi\Models;

use Callcocam\PapaLeguas\Models\AbstractModel;
use Callcocam\PapaLeguas\Support\Shinobi\Concerns\RefreshesPermissionCache;
use Callcocam\PapaLeguas\Support\Shinobi\Contracts\Permission as PermissionContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends AbstractModel implements PermissionContract
{
    use RefreshesPermissionCache;

    /**
     * Create a new Permission instance.
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('shinobi.tables.permissions'));
    }

    /**
     * Permissions can belong to many roles.
     *
     * @return Model
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(config('shinobi.models.role'))->withTimestamps();
    }

    protected function slugTo()
    {
        return false;
    }
}
