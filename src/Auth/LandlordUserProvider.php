<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Auth;

use Illuminate\Auth\EloquentUserProvider; 
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Support\Arrayable;

class LandlordUserProvider extends EloquentUserProvider
{
    public function __construct(Hasher $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }

    /**
     * Retrieve a user by their unique identifier for landlord context.
     * Landlord users have tenant_id = null
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();
        $tenantId = config('app.tenant_id', null);

        return $this->newModelQuery($model)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->where('tenant_id', $tenantId) // Landlord users don't belong to any tenant
            ->first();
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token for landlord context.
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();
        $tenantId = config('app.tenant_id', null);

        $retrievedModel = $this->newModelQuery($model)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->where('tenant_id', $tenantId) // Landlord users don't belong to any tenant
            ->first();

        if (! $retrievedModel) {
            return null;
        }

        $rememberToken = $retrievedModel->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $retrievedModel : null;
    }

    /**
     * Retrieve a user by the given credentials for landlord context.
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (
            empty($credentials) ||
            (count($credentials) === 1 &&
                str_contains($this->firstCredentialKey($credentials), 'password'))
        ) {
            return;
        }

        // Build the query
        $query = $this->newModelQuery();
        $tenantId = config('app.tenant_id', null);

        foreach ($credentials as $key => $value) {
            if (str_contains($key, 'password')) {
                continue;
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        // Ensure we only get landlord users (tenant_id = null)
        $query->where('tenant_id', $tenantId);

        return $query->first();
    }

    /**
     * Get the first key from the credential array.
     */
    protected function firstCredentialKey(array $credentials)
    {
        foreach ($credentials as $key => $value) {
            return $key;
        }
    }
}
