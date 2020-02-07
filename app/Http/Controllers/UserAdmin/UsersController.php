<?php

namespace App\Http\Controllers\UserAdmin;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Helpers\Roles;

class UsersController extends Controller {

   /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    private $tblName = 'users';
    
    public function index(Request $request) {

      $first     = false;
      $last      = false;
      $sorted    = null;
      $unsorted  = null;

      $filters   = [];
      
        /* Validator */

          $rules = array(
            'page'       => 'required',
            'size'       => 'required',
            'sortField'  => 'required',
            'sortOrder'  => 'required'
                  );    
          $messages = array(

            'page.required'       => 'Please Provide Page Number',
            'size.required'       => 'Please Provide Size for Each Page',
            'sortField.required'  => 'Please Provide SortField',
            'sortOrder.required'  => 'Please Provide SortOrder'
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );


        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

     /**
      * Check Requset if has -> page ?, size ?, sortField ?, sortOrder ?
      * create fun page, size, sortfield, sortorder
      */
      if ($request->has('page'))      $page      = $request->input('page');
      if ($request->has('size'))      $size      = $request->input('size');

      if ($request->has('sortField') && $request->has('sortOrder') ) { 
        $sortField = $request->input('sortField');
        $sortOrder = $request->input('sortOrder');
        $sorted    = true;
        $unsorted  = false;

       }

     $sort = (object) array(

        'sorted' => $sorted, 
        'unsorted' => $unsorted

      );


     /**
      * Check Request if has -> Search, and Filter
      * key : NIK
      * operation : LIKE
      * value : ex -> 31203..
      * call fun search nik, ticket and filter status, instansi
      */
     if ($request->has('filters')) $filters = $request->input('filters');

     $items = [];

     try {

       $show = DB::table('users')
                  ->join('roles', 'roles.id', '=', 'users.id_roles')
                  ->join('tbl_master_instansi', 'tbl_master_instansi.id_instansi', '=', 'users.id_instansi')
                  ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.email',
                    'users.username',
                    'users.password',
                    'users.id_roles',
                    'roles.name AS roles_name',
                    'users.id_instansi',
                    'tbl_master_instansi.nama_instansi',
                    'users.created_at',
                    'users.updated_at'
                  )
                  ->where(function ($query) use( $filters ) {
                       for ($i = 0; $i < count($filters); $i++){
                          if ($filters[$i]{'operation'} == 'LIKE') {
                            $query->where($this->tblName.$filters[$i]{'key'}, 'LIKE' ,  '%' . $filters[$i]{'value'} .'%');
                        } elseif ($filters[$i]{'operation'} == ':') {
                            $query->where($this->tblName.$filters[$i]{'key'}, '=' , $filters[$i]{'value'});
                        }
                       }
                  })                    
                  ->orderBy($sortField, $sortOrder)
                  ->paginate($size);

      } catch (QueryException $e) {

        return response()->json([
            'error'       => true,  
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);   
      }               

     $pageable[] = (object) array(
        'offset'      => $show->lastItem(), // last sum of records from first loaded
        'pageNumeber' => $show->currentPage(),
        'pageSize'    => $show->count(),
        'paged'       => true,
        'unpaged'     => false
    );     

      if ($show->onFirstPage()) $first = true;
      if ($show->currentPage() === $show->lastPage()) $last = true;

      foreach ($show as $key => $value) {
        $items[$key] = $value;
        $items[$key] = $value;
      }

       return response()->json([

          'content'             => $items,
          'first'               => $first,
          'last'                => $last,
          'number'              => $show->currentPage(),
          'numberOfElements'    => $show->count(),
          'pageable'            => $pageable,
          'sort'                => $sort, 
          'totalPages'          => $show->lastPage(),
          'totalElements'       => $show->total()

      ], 200);

    }
    
    public function create(Request $request) {

      try {

      /* Req Input*/

       $name = $request->input('name');
       $email = $request->input('email');
       $username = $request->input('username');
       $password = $request->input('password');
       $id_roles   = $request->input('id_roles');
       $id_instansi   = $request->input('id_instansi');

        /* Validator */

          $rules = array(
            'name'     => 'required',
            'email'     => 'required',
            'username'     => 'required',
            'password'     => 'required',
            'id_roles'     => 'required',
            'id_instansi'     => 'required'
                  );    
          $messages = array(

            'name.required'     => 'Silahkan masukan nama anda',
            'email.required'     => 'Silahkan masukan email anda',
            'username.required'     => 'Silahkan masukan username anda',
            'password.required'     => 'Silahkan masukan password anda',
            'id_roles.required'     => 'Silahkan masukan id_roles anda',
            'id_instansi.required'     => 'Silahkan masukan id_instansi anda'
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );

        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

       $InsertUsers = DB::table('users')->insertGetId(array(
                     
                     'name'  => $name,
                     'email'  => $email,
                     'username'  => $username,
                     'password'  => password_hash($password, PASSWORD_BCRYPT),
                     'id_roles' => $id_roles,
                     'id_instansi' => $id_instansi
         ));

    } catch (QueryException $e) {

        return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);

    }   
          return response()->json([
            'error'         => false,
            'message'       => 'Data Users Berhasil Ditambah'
          ], 200);

    }

    /**
     * [update description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function update(Request $request) {

      try {

      /* Req Input*/

       $id = $request->input('id');
       $name = $request->input('name');
       $email = $request->input('email');
       $username = $request->input('username');
       $password = $request->input('password');
       $id_roles   = $request->input('id_roles');
       $id_instansi   = $request->input('id_instansi');

        /* Validator */

          $rules = array(
            'id'     => 'required',
            'name'     => 'required',
            'email'     => 'required',
            'username'     => 'required',
            'password'     => 'required'
                  );    
          $messages = array(

            'id.required'     => 'Silahkan masukan id anda',
            'name.required'     => 'Silahkan masukan nama anda',
            'email.required'     => 'Silahkan masukan email anda',
            'username.required'     => 'Silahkan masukan username anda',
            'password.required'     => 'Silahkan masukan password anda',
            'id_roles.required'     => 'Silahkan masukan id_roles anda',
            'id_instansi.required'     => 'Silahkan masukan id_instansi anda'
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );

        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

       $UpdateUsers = DB::table('users')->where('id', $id)
       ->update([
                     
                     'name'  => $name,
                     'email'  => $email,
                     'username'  => $username,
                     'password'  => password_hash($password, PASSWORD_BCRYPT),
                     'id_roles' => $id_roles,
                     'id_instansi' => $id_instansi                     
         ]);

    } catch (QueryException $e) {

        return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);

    }   
          return response()->json([
            'error'         => false,
            'message'       => "Data User Berhasil di Update"
          ], 200);

    }

  public function resetPass(Request $request) {
    $defaultPass = '123456';
    $id = $request->input('id');

    try {
      $resetPass = DB::table('users')->where('id', $id)
      ->update([
        'password' => password_hash($defaultPass, PASSWORD_BCRYPT)
      ]);
    } catch (QueryException $e) {
        return response()->json([
        'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);      
    }
          return response()->json([
          'error'        => false,
          'message'      => 'Password Baru Anda adalah : : '.$defaultPass

        ], 200);  
    }

    public function show( Request $request ) {

      try {

        /* Validator */

          $rules = array(
            'id'     => 'required'
                  );    
          $messages = array(
            'id.required'     => 'Silahkan masukan id'
          );

          $validator = Validator::make( $request->all(), $rules, $messages );

        if ($validator->fails()) {
            return response()->json([
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);
        }        

        if ($request->has('id')) $id = $request->input('id');

        $getRolesName = Roles::showRoles($id);

        $show = DB::table('users')->where('id', $id)->first();

        $roles  = [];

        foreach ($getRolesName as $key) {
          array_push($roles,$key->name);
        }

        } catch (Exception $e) {

            return response()->json([
                'error'       => true,
                'code'        => $e->getCode(),
                'message'     => $e->getMessage()
              ], 500);

        }  

        if ( $show != '') {

            return response()->json([
            'error'        => false,
            'message'      => $show

          ], 200);      

        } else {

            return response()->json([
            'error'        => false,
            'message'      => 'Data Not Found'
          ], 404);      

        }

    }

     public function destroy($id) {
    
        try {

        $resultPengaduan = DB::table('users')->where('id', 
          $id)->delete();

      } catch(QueryException $e) {

          $code = $e->getCode();

          if ($code === "23000") {  

            return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => 'Maaf Terdapat Data lain yang terkait dengan data Ini'
          ], 500);

          } else { 

            return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);              

          }

      }   
            return response()->json([
            'error'         => false,
            'message'      => 'Data Berhasil di Hapus',
            'id' => $id
          ], 200);

     }

     public function fetch() {

        $query = DB::table('users')->get();

            return response()->json([
            'error'         => false,
            'message'      => $query
          ], 200);

     }     

}