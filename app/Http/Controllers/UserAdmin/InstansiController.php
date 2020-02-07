<?php

namespace App\Http\Controllers\UserAdmin;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class InstansiController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    private $tblName = 'tbl_master_instansi';
    
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

     try {

       $show = DB::table('tbl_master_instansi')
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

       return response()->json([

          'content'             => $show->items(),
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

       $id_instansi          = $request->input('id_instansi');
       $nama_instansi        = $request->input('nama_instansi');
       $alamat               = $request->input('alamat');
       $telp                 = $request->input('telp');

        /* Validator */

          $rules = array(

            'id_instansi'       => 'required',
            'nama_instansi'     => 'required'
                  );    
          $messages = array(

            'id_instansi.required'      => 'Silahkan masukan Kode Instansi',
            'nama_instansi.required'     => 'Silahkan masukan Nama Instansi'
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );


        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

       $InsertInstansi = DB::table('tbl_master_instansi')->insertGetId(array(
                     
                     'id_instansi'    => $id_instansi,
                     'nama_instansi'  => $nama_instansi,
                     'alamat'         => $alamat,
                     'telp'           => $telp
         ));
    } catch (QueryException $e) {

        return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);

    }   

   /*   $newInstansi = DB::table('tbl_master_instansi')->where('id_instansi', $InsertInstansi)->get();*/

          return response()->json([
            'error'         => false,
            'message'       => 'Instansi Berhasil Ditambah'
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

       $id           = $request->input('id');
       $nama_instansi        = $request->input('nama_instansi');
       $alamat               = $request->input('alamat');
       $telp                 = $request->input('telp');

        /* Validator */

          $rules = array(
            'id'     => 'required',
            'nama_instansi'     => 'required',
                  );    
          $messages = array(

            'id.required'     => 'Silahkan masukan id',
            'nama_instansi.required'     => 'Silahkan masukan nama_instansi'
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );


        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

       $UpdateInstansi = DB::table('tbl_master_instansi')->where('id_instansi', $id)
       ->update([
                     
                     'id_instansi'    => $id,
                     'nama_instansi'  => $nama_instansi,
                     'alamat'         => $alamat,
                     'telp'           => $telp
         ]);
    } catch (QueryException $e) {

        return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);

    }   

 /*     $newInstansi = DB::table('tbl_master_instansi')->where('id_instansi', $idInstansi)->get();*/

          return response()->json([
            'error'         => false,
            'message'       => "Data Instansi Berhasil di Update"
          ], 200);

          

    }

    public function show( Request $request ) {

      try {

        if ($request->has('id')) $id = $request->input('id');

        $show = DB::table('tbl_master_instansi')->where('id_instansi', $id)->first();

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

        $resultPengaduan = DB::table('tbl_master_instansi')->where('id_instansi', 
          $id)->delete();

      } catch(QueryException $e) {

          $code = $e->getCode();

          if ($code === "23000") {  

            return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => 'Maaf Terdapat Data yang terkait dengan Instansi Ini'
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
            'message'      => 'Data Berhasil di Hapus'
          ], 200);

     }

     public function fetch() {

        $query = DB::table('tbl_master_instansi')->get();

            return response()->json([
            'error'         => false,
            'message'      => $query
          ], 200);

     }
   

    }