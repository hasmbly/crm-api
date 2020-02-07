<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class Roles
{ 



    public static function showRoles ( $id )

    {

      $showRoles = DB::table('users_roles')->join('roles', 'roles.id', '=', 'users_roles.id_roles')
                    ->select(
                      'roles.name'
                    )
                    ->where('users_roles.id_users', $id)->get();

      return $showRoles;

    }

  }