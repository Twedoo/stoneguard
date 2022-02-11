<?php

namespace Twedoo\StoneGuard\Models;

use Twedoo\StoneGuard\StoneGuardRole;
use Config;

class Role extends StoneGuardRole
{

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, Config::get('stone::permission_role_table'));
    }

}
