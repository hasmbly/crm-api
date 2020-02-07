<?php

namespace App\Http\Controllers\UserAdmin;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class RolesController extends Controller {

   /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    public function index(Request $request) {

    }

    public function create(Request $request) {

   

    }

    /**
     * [update description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function update(Request $request) {

   
    }

    public function show( Request $request ) {


    }

     public function destroy($id) {
    
     }

     public function fetch() {

        $query = DB::table('roles')->get();

            return response()->json([
            'error'         => false,
            'message'      => $query
          ], 200);

     }     

}