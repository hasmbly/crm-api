<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;   


class validMe
{

    public static function isValid( Request $request, $rules = [], $messages = [] )
    {

        /* Validator */

          $validator = Validator::make( $request->all(), $rules, $messages );

        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

    }  

}