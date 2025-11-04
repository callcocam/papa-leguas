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

class TenantUserProvider extends EloquentUserProvider
{
    public function __construct(Hasher $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }

    /**
     * Retrieve a user by their unique identifier for tenant context.
     * Tenant users must belong to the current tenant
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();
        $tenantId = config('app.tenant_id');

        if (!$tenantId) {
            return null; // No tenant context
        }

        return $this->newModelQuery($model)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token for tenant context.
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();
        $tenantId = config('app.tenant_id');

        if (!$tenantId) {
            return null;
        }

        $retrievedModel = $this->newModelQuery($model)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $retrievedModel) {
            return null;
        }

        $rememberToken = $retrievedModel->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $retrievedModel : null;
    }

    /**
     * Retrieve a user by the given credentials for tenant context.
     */
    public function retrieveByCredentials(array $credentials)
    {
        $tenantId = config('app.tenant_id');

        if (!$tenantId) {
            return null;
        }

        if (
            empty($credentials) ||
            (count($credentials) === 1 &&
                str_contains($this->firstCredentialKey($credentials), 'password'))
        ) {
            return;
        }

        // Build the query
        $query = $this->newModelQuery();

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

        // Ensure we only get users from the current tenant
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
