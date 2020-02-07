<?php

namespace App\Http\Controllers\UserAdmin;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class PertanyaanController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    private $tblName = 'tbl_pertanyaan';
    
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
        
     $show = DB::table('tbl_pertanyaan')
              ->join('tbl_master_instansi', 'tbl_master_instansi.id_instansi', '=', 'tbl_pertanyaan.id_instansi')
              ->join('tbl_tema', 'tbl_tema.id_tema', '=', 'tbl_pertanyaan.id_tema')
              ->select('tbl_pertanyaan.id_pertanyaan', 'tbl_master_instansi.nama_instansi', 'tbl_tema.tema', 'tbl_pertanyaan.pertanyaan')
              ->where(function ($query) use( $filters ) {
                   for ($i = 0; $i < count($filters); $i++){
                      if ($filters[$i]{'operation'} == 'LIKE') {
                        $query->where($this->tblName.$filters[$i]{'key'}, 'LIKE' ,  '%' . $filters[$i]{'value'} .'%');
                    } elseif ($filters[$i]{'operation'} == ':') {
                        $query->where($this->tblName.$filters[$i]{'key'}, '=' , $filters[$i]{'value'});
                    }
                   }
              })                    
              ->orderBy($this->tblName.'.'.$sortField, $sortOrder)
              ->paginate($size);

      } catch (QueryException $e) {

        return response()->json([
            'error'       => true,  
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);

    }   
     return response()->json([
      'error'    => 'false',
      'message'  => $show
      ], 200);

    }

     public function showByJenis($jenis) {

      try {
        
     $show = DB::table('tbl_pertanyaan')
              ->join('tbl_master_instansi', 'tbl_master_instansi.id_instansi', '=', 'tbl_pertanyaan.id_instansi')
              ->join('tbl_tema', 'tbl_tema.id_tema', '=', 'tbl_pertanyaan.id_tema')
              ->select('tbl_pertanyaan.id_pertanyaan', 'tbl_master_instansi.nama_instansi', 'tbl_tema.tema', 'tbl_pertanyaan.pertanyaan')
              ->where('tema', $jenis)
              ->get();

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

       $id_instansi           = $request->input('id_instansi');
       $id_tema                    = $request->input('id_tema');
       $pertanyaan              = $request->input('pertanyaan');

        /* Validator */

          $rules = array(

            'id_instansi'         => 'required',
            'id_tema'                  => 'required',
            'pertanyaan'            => 'required'

            );

          $messages = array(

            'id_instansi.required'       => 'Silahkan masukan Kode Instansi',
            'id_tema.required'                => 'Silahkan masukan id tema',
            'pertanyaan.required'          => 'Silahkan masukan Pertanyaan',
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );


        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

       $InsertPertanyaan = DB::table('tbl_pertanyaan')->insertGetId(array(
                     
                     'id_instansi'       => $id_instansi,
                     'id_tema'          => $id_tema,
                     'pertanyaan'        => $pertanyaan
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
            'message'       => 'Pertanyaan Berhasil Ditambah'
          ], 200);

          

    }

    public function update(Request $request) {

      try {

      /* Req Input*/

       $id_pertanyaan            = $request->input('id_pertanyaan');
       $id_instansi           = $request->input('id_instansi');
       $id_tema                    = $request->input('id_tema');
       $pertanyaan              = $request->input('pertanyaan');

        /* Validator */

          $rules = array(
            'id_pertanyaan'        => 'required',
            'id_instansi'         => 'required',
            'id_tema'             => 'required',
            'pertanyaan'          => 'required'

            );

          $messages = array(
            'id_pertanyaan.required'       => 'Silahkan masukan id pertanyaan',
            'id_instansi.required'       => 'Silahkan masukan Kode Instansi',
            'id_tema.required'    => 'Silahkan masukan id tema',
            'pertanyaan.required'          => 'Silahkan masukan Pertanyaan',
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );


        if ($validator->fails()) {

            return response()->json([
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

       $UpdatePertanyaan = DB::table('tbl_pertanyaan')->where('id_pertanyaan', 
        $id_pertanyaan)
       ->update([
                     
                     'id_instansi'       => $id_instansi,
                     'id_tema'          => $id_tema,
                     'pertanyaan'        => $pertanyaan
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
            'message'       => 'Data Pertanyaan Berhasil di Update'
          ], 200);

          

    }

    public function show ( Request $request ) {

      try {

        if ($request->has('id')) $id = $request->input('id');

        $show = DB::table('tbl_pertanyaan')->where('id_pertanyaan', $id)->first();

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

        $deletePertanyaan = DB::table('tbl_pertanyaan')->where('id_pertanyaan', $id)->delete();

      } catch(QueryException $e) {

            return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);
      }   
            return response()->json([
            'error'         => false,
            'message'      => 'Data Berhasil di Hapus'
          ], 200);

     }

      public function check(Request $request) {

        /*get id_tema and id_instansi from tema */
        
        try {

        $pertanyaan = $request->input('pertanyaan');

        $id_pertanyaan   = DB::table('tbl_pertanyaan')->where('pertanyaan', $pertanyaan)->pluck('id_pertanyaan');

        $id_tema   = DB::table('tbl_pertanyaan')->where('pertanyaan', $pertanyaan)->pluck('id_tema');


      } catch(QueryException $e) {

            return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);
      }   
            return response()->json([
            'error'         => false,
            'id_tema'      => $id_tema[0],
            'id_pertanyaan' => $id_pertanyaan[0]
          ], 200);

     }

     public function fetch() {

        $query = DB::table('tbl_pertanyaan')
                ->join('tbl_master_instansi', 'tbl_master_instansi.id_instansi', '=', 'tbl_pertanyaan.id_instansi')
                ->join('tbl_tema', 'tbl_tema.id_tema', '=', 'tbl_pertanyaan.id_tema')
                ->select(
                  'tbl_pertanyaan.id_pertanyaan',
                  'tbl_master_instansi.id_instansi',
                  'tbl_master_instansi.nama_instansi',
                  'tbl_tema.id_tema',
                  'tbl_tema.tema',
                  'tbl_pertanyaan.pertanyaan'
                )
                ->get();

            return response()->json([
            'error'         => false,
            'message'      => $query
          ], 200);

     }     


    }