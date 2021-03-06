<?php

namespace App\Http\Controllers\Informasi;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;


class FaqController extends Controller {

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

      $show = DB::table('tbl_faq')
              ->select(
                'tbl_faq.*'
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
        
    ], 200);

	}

    public function create(Request $request) {

      try {

      /* Req Input*/

       $question          = $request->input('question');
       $answer 	 = $request->input('answer');

        /* Validator */
          $rules = array(
            'question'     => 'required',
            'answer'     => 'required'
           );    

          $messages = array(
            'question.required'     => 'Silahkan masukan Nama question',
            'answer.required'     => 'Silahkan masukan Nama answer'
          );

          $validator = Validator::make( $request->all(), $rules, $messages );

        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

       $InsertFaq = DB::table('tbl_faq')->insertGetId(array(
                     
                     'question'  => $question,
                     'answer'  => $answer
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
            'message'       => 'Request Data FAQ Berhasil Ditambah'
          ], 200);

    }

	public function show(Request $request) {

      try {

        if ($request->has('id')) $id = $request->input('id');

        $show = DB::table('tbl_faq')->where('id', $id)->first();

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
       $question        	= $request->input('question');
       $answer        	= $request->input('answer');

        /* Validator */

          $rules = array(
            'id'     => 'required',
            'question'     => 'required',
            'answer'     => 'required'
                  );    
          $messages = array(

            'id.required'     => 'Silahkan masukan id',
            'question.required'     => 'Silahkan masukan question',
            'answer.required'     => 'Silahkan masukan answer'
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );

        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

       $UpdateFaq = DB::table('tbl_faq')->where('id', $id)
       ->update([
                     'question'    => $question,
                     'answer'    => $answer
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
            'message'       => "Data FAQ Berhasil di Update"
          ], 200);


	}

	public function destroy($id) {

        try {

        $delete = DB::table('tbl_faq')->where('id', $id)->delete();

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

        $query = DB::table('tbl_faq')->get();

            return response()->json([
            'error'         => false,
            'message'      => $query
          ], 200);

     }     



}
