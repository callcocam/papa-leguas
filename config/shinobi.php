<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Shinobi Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for Shinobi package.
    | This includes model mappings and table names.
    |
    */

    'models' => [
        'user' => \App\Models\User::class,
        'role' => \Callcocam\PapaLeguas\Support\Shinobi\Models\Role::class,
        'permission' => \Callcocam\PapaLeguas\Support\Shinobi\Models\Permission::class,
    ],

    'tables' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'role_user' => 'role_user',
        'permission_user' => 'permission_user',
        'permission_role' => 'permission_role',
    ],  
];
