<?php

namespace App\Http\Controllers\Informasi;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;


class InfoGrafisController extends Controller {

	private $publicPath = 'uploads/info-grafis';
	private $urlUpload	= '/uploads/info-grafis/';

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

     /**
      * Check Request if has -> Search, and Filter
      * key : NIK
      * operation : LIKE
      * value : ex -> 31203..
      * call fun search nik, ticket and filter status, instansi
      */
     if ($request->has('filters')) $filters = $request->input('filters');

      $show = DB::table('tbl_info_grafis')
              ->select(
                'tbl_info_grafis.*'
              )
              ->Where(function ($query) use( $filters ) {
                   for ($i = 0; $i < count($filters); $i++){
                      if ($filters[$i]{'operation'} == 'LIKE') {
                        $query->where($filters[$i]{'key'}, 'LIKE' ,  '%' . $filters[$i]{'value'} .'%');
                    } elseif ($filters[$i]{'operation'} == ':') {
                        $query->where($filters[$i]{'key'}, '=' , $filters[$i]{'value'});
                    }
                   }
              })                 
              ->orderBy($sortField,$sortOrder)
              ->paginate($size);
     	
     $sort = (object) array(

        'sorted' => $sorted, 
        'unsorted' => $unsorted

      );

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
        // 'users_auth'          => $request->auth{'username'}
        
    ], 200);

	}

    public function create(Request $request) {

      try {

      /* Req Input*/

       $title          = $request->input('title');
       $description 	 = $request->input('description');
       $file           = $request->file('file');

        /* Validator */
          $rules = array(
            'title'     => 'required',
            'description'     => 'required',
            'file'     => 'required|image|max:2048'
           );    

          $messages = array(
            'title.required'     => 'Silahkan masukan Nama title',
            'description.required'     => 'Silahkan masukan Nama description',
            'file.required'     => 'Silahkan Upload file anda',
            'file.images'     => 'File yang di upload harus berbentuk Image',
            'file.max'     => 'File yang di upload tidak boleh lebih dari 2 MB',
          );

          $validator = Validator::make( $request->all(), $rules, $messages );

        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

        /**
         * check upload file $today = Carbon::today()->toDateString();
         */

        $fileRename = rand() . '_' .$file->getClientoriginalName();
        $file->move(public_path($this->publicPath), $fileRename);
        $filePath = url($this->urlUpload) . '/' . $fileRename;

       $InsertInfoGrafis = DB::table('tbl_info_grafis')->insertGetId(array(
                     
                     'title'  => $title,
                     'description'  => $description,
                     'filename'  => $fileRename,
                     'filepath'  => $filePath
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
            'message'       => 'Request Data Info Grafis Berhasil Ditambah'
          ], 200);

    }

	public function show(Request $request) {

      try {

        if ($request->has('id')) $id = $request->input('id');

        $show = DB::table('tbl_info_grafis')->where('id', $id)->first();

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

	public function update(Request $request) {

      try {

      /* Req Input*/

       $id           	= $request->input('id');
       $title        	= $request->input('title');
       $description        	= $request->input('description');

        /* Validator */

          $rules = array(
            'id'     => 'required',
            'title'     => 'required',
            'description'     => 'required'
                  );    
          $messages = array(

            'id.required'     => 'Silahkan masukan id',
            'title.required'     => 'Silahkan masukan title',
            'description.required'     => 'Silahkan masukan description'
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );

        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

       $UpdateInstansi = DB::table('tbl_info_grafis')->where('id', $id)
       ->update([
                     'title'    => $title,
                     'description'    => $description
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
            'message'       => "Data Info Grafis Berhasil di Update"
          ], 200);


	}

	public function destroy($id) {

        try {

        $delete = DB::table('tbl_info_grafis')->where('id', $id)->delete();

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

     public function fetch() {

        $query = DB::table('tbl_info_grafis')->get();

            return response()->json([
            'error'         => false,
            'message'      => $query
          ], 200);

     }     



}
