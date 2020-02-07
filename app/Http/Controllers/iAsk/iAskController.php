<?php

namespace App\Http\Controllers\iAsk;

use App\Http\Controllers\UserAdmin\JenisPertanyaanController;
use App\Http\Controllers\UserAdmin\PertanyaanController;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

use App\Helpers\Ticket;
use App\Helpers\Comment;
use App\Helpers\InfoPengadu;

use App\Helpers\makePdf;
use PDF;

use App\Mail\MailtrapExample;
use Illuminate\Support\Facades\Mail;

class iAskController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    private $antrian        = null;
    private $id_instansi    = null;
    private $id_tema        = null;
    private $id_pertanyaan  = null;
    private $tema;
    private $Pertanyaan;

    private $history_pengaduan  = null;
    private $history_comment    = null;
    private $info_terkait       = null;


    private $tblName            = 'tbl_pengaduan_iask';

    /**
     * Fetch All Records from Pengaduan iAsk for Admin User
     */

    public function index(Request $request) {
        
      $first     = false;
      $last      = false;
      $sorted    = null;
      $unsorted  = null;

      $filters   = [];

      $user_instansi = $request->auth->id_instansi;
      $user_roles = $request->auth->id_roles;

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
      if ($request->has('page'))        $page         = $request->input('page');
      if ($request->has('size'))        $size         = $request->input('size');

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

      /**
       * Create Logic to handle random object 0 - 3 and send to function
       * filterPengaduan()
       */

      $show = DB::table('tbl_pengaduan_iask')
              ->join('tbl_master_instansi', 'tbl_master_instansi.id_instansi', '=', 'tbl_pengaduan_iask.id_instansi')
              ->join('tbl_tema', 'tbl_tema.id_tema', '=', 'tbl_pengaduan_iask.id_tema')
              ->select(
                'tbl_pengaduan_iask.no_pengaduan',
                'tbl_pengaduan_iask.nik',
                'tbl_pengaduan_iask.nama',
                'tbl_master_instansi.nama_instansi',
                'tbl_tema.tema',
                'tbl_pengaduan_iask.uraian',
                'tbl_pengaduan_iask.status',
                'tbl_pengaduan_iask.created_by',
                'tbl_pengaduan_iask.updated_by',
                'tbl_pengaduan_iask.created_at',
                'tbl_pengaduan_iask.updated_at'
              )
              ->where(function ($query) use($user_instansi, $user_roles) {
                if ($user_roles == 5){
                  $query->where('tbl_pengaduan_iask.id_instansi', $user_instansi );
                }
              })
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

    /**
     * Create Pengaduan Baru + Antrian
     */

     public function create(Request $request) {

      // Initialize JenisPertanaanController Class
      $this->tema = new JenisPertanyaanController;

        try { 

      /* Req Input*/

       $nik                = $request->input('nik');
       $nama               = $request->input('nama');
       $alamat             = $request->input('alamat');
       $nohp               = $request->input('nohp');
       $email              = $request->input('email');
       $id_tema               = $request->input('id_tema'); // lookup & select
       $uraian             = $request->input('uraian');
       $created_by         = $request->input('created_by');

        /* Validator */

          $rules = array(
            'nama'              => 'required',
            'nik'     => 'required|numeric|digits:16',
            'email'     => 'required|email',
            'nohp'     => 'required|numeric',
            'id_tema'              => 'required',
            'alamat'            => 'required'
                  );    
          $messages = array(

            'nik.required'     => 'Silahkan masukan nik',
            'nik.numeric'     => 'Maaf NIK yang di masukan harus angka',
            'nik.digits'     => 'Maaf NIK yang di masukan harus 16 angka',
            'nohp.required'     => 'Silahkan masukan Nama nohp',
            'nohp.numeric'     => 'Maaf No HP yang di masukan harus angka',
            'email.required'     => 'Silahkan masukan Nama email',
            'email.email'     => 'Harap masukan alamat Email anda dengan benar',                        
            'nama.required'               => 'Silahkan masukan nama',
            'alamat.required'             => 'Silahkan masukan alamat',
            'id_tema.required'               => 'Silahkan pilih id_tema'
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );


        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

        /**
         * Check NIK is already has ticket that still opened
         */
        $checkTicket = Ticket::checkStatusTicket($nik, $this->tblName);        

        /*get id_id_tema and id_instansi from id_tema */

          $getIdInstansi          = DB::table('tbl_tema')->where('id_tema', $id_tema)->pluck('id_instansi');

          if ( count($getIdInstansi) > 0 ) {

            $this->id_instansi = $getIdInstansi[0];

          }

          $this->id_tema           = $id_tema;
        

        if ( $checkTicket == true ) {        

       $InsertPengaduan = DB::table('tbl_pengaduan_iask')->insertGetId(array(
                 
                     /*'status'       => $status,*/
                     'tiket_id'               => null,
                     'no_pengaduan'           => null,
                     'id_instansi'            => $this->id_instansi,
                     'id_tema'                => $this->id_tema,
                     'uraian'                 => $uraian,
                     'nik'                    => $nik,
                     'nama'                   => $nama,
                     'email'                  => $email,
                     'alamat'                 => $alamat,
                     'nohp'                   => $nohp,
                     'created_at'             => null,
                     'updated_at'             => null,
                     'created_by'             => $created_by,
                     'updated_by'             => null
                
         ));

       } else {

            return response()->json([
              'error'            => true,
              'message'          => 'Maaf Ada ticket anda yang belum closed'
            ], 422);
       }

    } catch (QueryException $e) {

        return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);

    }   

        $noPengaduan = DB::table('tbl_pengaduan_iask')->where('tiket_id', $InsertPengaduan)->pluck('no_pengaduan');

        $tiket = $noPengaduan[0];

        $antrian = 0;

      
         Mail::to($email)->send(new MailtrapExample($tiket, $antrian, $nama)); 

          return response()->json([
            'error'            => false,
            'no_pengaduan'     => $noPengaduan[0]
          ], 200);

     }  

     /**
      * [showHistoryByTicket description]
      * @param  [type] $ticket [description]
      * @return [type]         [description]
      */
     public function show ( Request $request ) {
        /* Validator */

          $rules = array(
            'no_pengaduan'     => 'required',
            'nik'     => 'required'
                  );    
          $messages = array(
            'no_pengaduan.required'     => 'Silahkan masukan no_pengaduan',
            'nik.required'     => 'Silahkan masukan nik'
          );
          $validator = Validator::make( $request->all(), $rules, $messages );
        if ($validator->fails()) {
            return response()->json([
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);
        }

        if (!$request->has('no_pengaduan') && !$request->has('nik')) {

            return response()->json([
            'error'        => true,
            'message'      => "Bad Request, Please fill no_pengaduan or nik"
          ], 400); 

         }       

        try {

            $pengaduan                    = Ticket::show($request, $this->tblName);
            $this->history_pengaduan      = $pengaduan->getData()->message;

            $comment                      = Comment::show($request, $this->tblName);
            $getComment                   = $comment->getData()->message; 
            if ( !empty($getComment) ) $this->history_comment = $getComment;
            
            if ( $this->history_pengaduan != '' ) {

            $infoTerkait                  = InfoPengadu::show($request, $this->tblName);
            $getinfoTerkait               = $infoTerkait->getData()->message; 
            if ( !empty($getinfoTerkait) ) $this->info_terkait = $getinfoTerkait;

          }          


        } catch(Exception $e) {

              return response()->json([
              'error'       => true,
              'code'        => $e->getCode(),
              'message'     => $e->getMessage()
            ], 500);
        }   

          $content = [ 
                      'pengaduan' => $this->history_pengaduan, 
                      'comment'   => $this->history_comment,
                      'info_terkait' => $this->info_terkait
                    ];

            if ( $this->history_pengaduan == null ) {
                return response()->json([
                'error'       => true,
                'message'     => "Data not found"
              ], 404);              
            }            

            return response()->json([
            'error'        => false,
            'message'      => $content
          ], 200);

     }        

      /**
      * [createComment description]
      * this func for tindak lanjut from OP and Sanggahan from Masyarakat.
      * @return [type] [description]
      */
     public function addComment(Request $request) {

          $comment = Comment::addComment( $request, $this->tblName );

          return $comment;

     }


     /**
      * [update description]
      * @return [type] [description]
      */
     public function update(Request $request) {

      try {

        /* Req Input*/
       
       $no_pengaduan       = $request->input('no_pengaduan');
       $status   = $request->input('status');
       $updated_by         = $request->input('updated_by');
       // $is_notified         = $request->input('is_notified');


        /* Validator */

          $rules = array(
            'no_pengaduan'     => 'required',
            'status'     => 'required',
            'updated_by'     => 'required'  
                  );    
          $messages = array(

            'no_pengaduan.required'     => 'Silahkan masukan no_pengaduan',
            'status.required'     => 'Silahkan masukan status',
            'updated_by.required'     => 'Silahkan masukan updated_by'
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );

        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }


      $resultPengaduan = DB::table('tbl_pengaduan_iask')->where('no_pengaduan', $no_pengaduan)
      ->update([ 

                     'status'       => $status,
                     'updated_by'             => $updated_by

      ]);
      
      } catch(QueryException $e) {

            return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);
      }   
            return response()->json([
            'error'         => false,
            'message'      => "Data iAsk Berhasil di Update"
          ], 200);

     }

     /**
      * [destroy description]
      * 
      * @param  [type] $id [description]
      * @return [type]     [description]
      */
     public function destroy($ticket) {
    
        try {

        $resultPengaduan = DB::table('tbl_pengaduan_iask')->where('no_pengaduan', $ticket)->delete();

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


    }