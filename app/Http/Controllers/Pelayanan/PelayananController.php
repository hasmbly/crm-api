<?php

namespace App\Http\Controllers\Pelayanan;

use App\Http\Controllers\UserAdmin\ListPelayananController;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

use App\Mail\MailPelayanan;
use Illuminate\Support\Facades\Mail;

class PelayananController extends Controller {

	private $publicPath = 'uploads/pelayanan';
	private $urlUpload	= '/uploads/pelayanan/';
  private $filePath   = '';

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

      $show = DB::table('tbl_pelayanan')
              ->join('tbl_list_pelayanan', 'tbl_list_pelayanan.id_list', '=', 'tbl_pelayanan.id_list')
              ->join('tbl_pelayanan_tracker', 'tbl_pelayanan_tracker.id', '=', 'tbl_pelayanan.id_tracker')
              ->select(
                'tbl_pelayanan.id_pelayanan',
                'tbl_pelayanan.no_pelayanan',
                'tbl_pelayanan.instansi',
                'tbl_pelayanan.nik',
                'tbl_pelayanan.nama',
                'tbl_pelayanan.nohp',
                'tbl_pelayanan.email',
                'tbl_list_pelayanan.pelayanan',
                'tbl_pelayanan.uraian',
                'tbl_pelayanan.status',
                'tbl_pelayanan.id_tracker',
                'tbl_pelayanan_tracker.n_tracker',
                'tbl_pelayanan.created_at',
                'tbl_pelayanan.updated_at',
                'tbl_pelayanan.created_by',
                'tbl_pelayanan.updated_by'
              )
              ->Where(function ($query) use( $filters ) {
                   for ($i = 0; $i < count($filters); $i++){
                      if ($filters[$i]{'operation'} == 'LIKE') {
                        $query->where($filters[$i]{'key'}, 'LIKE' ,  '%' . $filters[$i]{'value'} .'%');
                    } elseif ($filters[$i]{'operation'} == ':') {
                        // if ($filters[$i]{'value'} == '') {
                        //   continue;
                        // }
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
        // 'auth'                => $request->auth
        
    ], 200);

	}

    public function create(Request $request) {

      try {

      /* Req Input*/

       $instansi = $request->input('instansi');
       $nik = $request->input('nik');
       $nama 	 = $request->input('nama');
       $nohp 	 = $request->input('nohp');
       $email 	 = $request->input('email');
       $id_list	 = $request->input('id_list');
       $uraian 	 = $request->input('uraian');
       $file     = $request->file('file');
       $created_by     = $request->input('created_by');

        /* Validator */
          $rules = array(
            'instansi'     => 'required',
            'nama'     => 'required',
            'nik'     => 'required|numeric|digits:16',
            'nohp'     => 'required|numeric',
            'email'     => 'required|email',
            'id_list'     => 'required',
            'uraian'     => 'required',
            'file'     => 'required'
           );    

          $messages = array(
            'instansi.required'     => 'Silahkan masukan Nama instansi',
            'nama.required'     => 'Silahkan masukan Nama nama',
            'nik.required'     => 'Silahkan masukan nik',
            'nik.numeric'     => 'Maaf NIK yang di masukan harus angka',
            'nik.digits'     => 'Maaf NIK yang di masukan harus 16 angka',
            'nohp.required'     => 'Silahkan masukan Nama nohp',
            'nohp.numeric'     => 'Maaf No HP yang di masukan harus angka',
            'email.required'     => 'Silahkan masukan Nama email',
            'email.email'     => 'Harap masukan alamat Email anda dengan benar',
            'id_list.required'     => 'Silahkan masukan Nama id list pertanyaan',
            'file.required'     => 'Silahkan Upload file anda'
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
        
        $temp = file_get_contents($file);
        $blob = base64_encode($temp);
        
        $fileRename = 'Document.pdf';

       $InsertPelayanan = DB::table('tbl_pelayanan')->insertGetId(array(
                     
                     'instansi' => $instansi,
                     'nik' => $nik,
                     'nama'  => $nama,
                     'nohp'  => $nohp,
                     'email'  => $email,
                     'id_list'  => $id_list,
                     'uraian'  => $uraian,
                     'file'  => $blob,
                     'filename'  => $fileRename,
                     'created_by'  => $created_by
         ));

    } catch (QueryException $e) {

        return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);
    }   
        // get no pelayanan after submit
        $no_pelayanan = DB::table('tbl_pelayanan')->where('id_pelayanan', $InsertPelayanan)->pluck('no_pelayanan');
        $no_pelayanan = $no_pelayanan[0];

        Mail::to($email)->send(new MailPelayanan($no_pelayanan, $nama)); 
          return response()->json([
            'error'              => false,
            'no_pelayanan'       => $no_pelayanan
          ], 200);

    }

	public function show(Request $request) {

      try {

        if ($request->has('id')) $id = $request->input('id');

      $show = DB::table('tbl_pelayanan')
              ->join('tbl_list_pelayanan', 'tbl_list_pelayanan.id_list', '=', 'tbl_pelayanan.id_list')
              ->join('tbl_pelayanan_tracker', 'tbl_pelayanan_tracker.id', '=', 'tbl_pelayanan.id_tracker')
              ->select(
                'tbl_pelayanan.id_pelayanan',
                'tbl_pelayanan.no_pelayanan',
                'tbl_pelayanan.instansi',
                'tbl_pelayanan.nik',
                'tbl_pelayanan.nama',
                'tbl_pelayanan.nohp',
                'tbl_pelayanan.email',
                'tbl_list_pelayanan.pelayanan',
                'tbl_pelayanan.file',
                'tbl_pelayanan.filename',
                'tbl_pelayanan.uraian',
                'tbl_pelayanan.status',
                'tbl_pelayanan.id_tracker',
                'tbl_pelayanan_tracker.n_tracker',
                'tbl_pelayanan.created_at',
                'tbl_pelayanan.updated_at',
                'tbl_pelayanan.created_by',
                'tbl_pelayanan.updated_by'
              )
        ->where('id_pelayanan', $id)
        ->first();
        } catch (Exception $e) {

            return response()->json([
                'error'       => true,
                'code'        => $e->getCode(),
                'message'     => $e->getMessage()
              ], 500);
        }

        if ( $show != '') {

          $filePath = $this->downloadFile($id);
          $filePath = $filePath->getData()->message;

          $fetch = [];
          // $history = ['hello', 'hallo'];
          foreach ($show as $key => $value) {
            if ($key == 'file' || $key == 'filename' ) { 
                $fetch['filepath'] = $filePath;
                continue;
            }
            $fetch[$key] = $value;
          }

          $getTracker = DB::table('tbl_pelayanan_tracker_history')
          ->join('tbl_pelayanan', 'tbl_pelayanan.id_pelayanan', '=', 'tbl_pelayanan_tracker_history.id_pelayanan')
          ->join('tbl_pelayanan_tracker', 'tbl_pelayanan_tracker.id', '=', 'tbl_pelayanan_tracker_history.id_tracker')
          ->select(
            'tbl_pelayanan_tracker.n_tracker',
            'tbl_pelayanan_tracker_history.updated_at',
            'tbl_pelayanan_tracker_history.updated_by'
          )
          ->where('tbl_pelayanan_tracker_history.id_pelayanan', $id)
          ->get();

          $fetch['status_history'] = $getTracker;

            return response()->json([
            'error'        => false,
            'message'      => $fetch,
            'auth'         => $request->auth->email

          ], 200);      

        } else {

            return response()->json([
            'error'        => false,
            'message'      => 'Data Not Found'
          ], 404);      

        }
	}

	public function downloadFile($id) {

        if ($id != '') {

          $file   = DB::table('tbl_pelayanan')->where('id_pelayanan', $id)->pluck('file');
          $file = $file[0];

        	$filename 	= DB::table('tbl_pelayanan')->where('id_pelayanan', $id)->pluck('filename');
        
        $encodedFile = "encoded.txt";
        $putContent = file_put_contents(public_path($this->publicPath) . '/' . $encodedFile, $file);
        $pdf_base64 = public_path($this->publicPath) . '/'. $encodedFile;
        $pdf_base64_handler = fopen($pdf_base64,'rb');
        $pdf_content = fread ($pdf_base64_handler,filesize($pdf_base64));
        fclose ($pdf_base64_handler);
        $pdf = fopen (public_path($this->publicPath) . '/'. $filename[0],'wb+');
        $pdf_decoded = base64_decode ($pdf_content);
        fwrite ($pdf,$pdf_decoded);
        fclose ($pdf);

        $filePath = url($this->urlUpload) . '/'. $filename[0];

        	 return response()->json([
            'error'        => false,
            'message'      => $filePath
          ], 200);   

        } 

	}	

	public function sampleDownload() {

		$filename 	= "M. Hasbi A..pdf";
		$path 	 	= public_path('/images/sample/' . $filename);

	    $headers = array(
	              'Content-Type: application/pdf',
	            );

		return response()->download($path, $filename, $headers);

	}	

	public function update(Request $request) {

      try {

      /* Req Input*/

       $id           	= $request->input('id');
       $status          = $request->input('status');
       $id_tracker        	= $request->input('id_tracker');
       // $updated_by          = $request->input('updated_by');
       $updated_by        	= $request->auth->email;

        /* Validator */
          $rules = array(
            'id'     => 'required',
            'status'     => 'required',
            'updated_by'     => 'required'
                  );    
          $messages = array(
            'id.required'     => 'Silahkan masukan id',
            'status.required'     => 'Silahkan masukan status',
            'updated_by.required'     => 'Silahkan masukan updated_by'
          );

          $validator = Validator::make( $request->all(), $rules, $messages );

          // add history_status
         $tracker = $this->status_tracker($id, $id_tracker, $updated_by);

        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

       $UpdateInstansi = DB::table('tbl_pelayanan')->where('id_pelayanan', $id)
       ->update([
                     'status'    => $status,
                     'id_tracker'    => $id_tracker,
                     'updated_by'    => $updated_by
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
            'message'       => "Data Pelayanan Berhasil di Update"
          ], 200);
	}

	public function destroy($id) {

        try {

        $delete = DB::table('tbl_pelayanan')->where('id_pelayanan', $id)->delete();

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

  public function status_tracker($id, $id_tracker, $updated_by) {
    try {

        $checkIdTracker = DB::table('tbl_pelayanan')->where('id_pelayanan', $id)->pluck('id_tracker');

      if (count($checkIdTracker) > 0) {
          if ($id_tracker != $checkIdTracker[0]) {
             $insert = DB::table('tbl_pelayanan_tracker_history')->insert(array(
               'id_pelayanan' => $id,
               'id_tracker' => $id_tracker,
               'updated_by' => $updated_by
             ));          
          } else {
            return false;
          }
      }

      } catch(QueryException $e) {
            return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);              
      }

      return true;
  } 

}
