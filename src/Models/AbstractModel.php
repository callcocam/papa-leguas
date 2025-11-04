<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Models;

use Callcocam\PapaLeguas\Support\Sluggable\HasSlug;
use Callcocam\PapaLeguas\Support\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbstractModel extends Model
{
    use SoftDeletes, HasUlids, HasSlug;

    protected $guarded = ['id'];

    public function tenant()
    {
        return $this->belongsTo(config('papa-leguas.tenant_model', Tenant::class));
    }
    /**
     * @return SlugOptions
     */
    public function getSlugOptions()
    {
        if (is_string($this->slugTo())) {
            return SlugOptions::create()
                ->generateSlugsFrom($this->slugFrom())
                ->saveSlugsTo($this->slugTo());
        }
    }
}
