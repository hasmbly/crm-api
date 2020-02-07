<?php

namespace App\Helpers;
use Illuminate\Http\Response;

class myResponse
{

    public static function hello_world()
    {
        return 'Hello World';
    }  
    
    public static function response(bool $error, $message, int $code) {

         return response()->json([
          'error'       => $error,
          'message'     => $message
        ], $code);    	

    }

}