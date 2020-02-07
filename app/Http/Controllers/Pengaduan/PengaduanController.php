<?php

namespace App\Http\Controllers\Pengaduan;

use App\Http\Controllers\UserAdmin\PertanyaanController;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;   
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

use App\Helpers\Antrian;
use App\Helpers\Ticket;
use App\Helpers\Comment;
use App\Helpers\InfoPengadu;

use App\Helpers\makePdf;
use PDF;

use App\Mail\MailtrapExample;
use Illuminate\Support\Facades\Mail;


class PengaduanController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    private $antrian            = '';
    private $id_instansi        = '01'; // for Pusdatin Pengaduan
    private $id_tema            = null;
    private $id_pertanyaan      = null;

    private $history_pengaduan  = null;
    private $history_comment    = null;
    private $info_terkait       = null;

    private $Pertanyaan;
    private $InsertPengaduan    = 0;
    private $noPengaduan        = [];

    private $tblName            = 'tbl_pengaduan';

    /**
     * Fetch All Records from Pengaduan => BDT PUSDATIN
     */

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

      $show = DB::table('tbl_pengaduan')
              ->join('tbl_master_instansi', 'tbl_master_instansi.id_instansi', '=', 'tbl_pengaduan.id_instansi')
              ->join('tbl_pertanyaan', 'tbl_pertanyaan.id_pertanyaan', '=', 'tbl_pengaduan.id_pertanyaan')
              ->select(
                'tbl_pengaduan.no_pengaduan',
                'tbl_pengaduan.nik',
                'tbl_pengaduan.nama',
                'tbl_pertanyaan.pertanyaan',
                'tbl_pengaduan.uraian',
                'tbl_master_instansi.nama_instansi',
                'tbl_pengaduan.status',
                'tbl_pengaduan.created_by',
                'tbl_pengaduan.updated_by',                
                'tbl_pengaduan.created_at',
                'tbl_pengaduan.updated_at'
              )
              ->where('tbl_pengaduan.id_instansi', $this->id_instansi)
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


      // Initialize Obejct for Access PertanyaanController Class
      $this->Pertanyaan = new PertanyaanController;

        try { 

      /* Req Input*/

       /*$status   = $request->input('status');*/ // just for create new
       $id_pertanyaan      = $request->input('id_pertanyaan');
       $uraian             = $request->input('uraian');
       $nik                = $request->input('nik');
       $nama               = $request->input('nama');
       $alamat             = $request->input('alamat');
       $email              = $request->input('email');
       $nohp               = $request->input('nohp');
       $tglkedatangan      = $request->input('tanggal');
       $jamkedatangan      = $request->input('jam');
       $created_by         = $request->input('created_by');
       /*$updated_by         = $request->input('updated_by'); */ // just for create new

        /* Validator */

          $rules = array(
            'id_pertanyaan'        => 'required',
            'nik'               => 'required|digits:16',
            'nama'              => 'required',
            'alamat'            => 'required'
                  );    
          $messages = array(

            'id_pertanyaan.required'         => 'Silahkan masukan id_pertanyaan',
            'nik.required'                => 'Silahkan masukan nik',
            'nik.digits'                => 'Mohon Maaf NIK yang di Input harus 16 digit',
            'nama.required'               => 'Silahkan masukan nama',
            'alamat.required'             => 'Silahkan masukan alamat'
                      
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

        /*get id_tema and id_id_pertanyaan from tema */

          $getIdTema          = DB::table('tbl_pertanyaan')->where('id_pertanyaan', $id_pertanyaan)->pluck('id_tema');

          if ( count($getIdTema) > 0 ) {

            $this->id_tema = $getIdTema[0];

          }

          $this->id_pertanyaan     = $id_pertanyaan;
        
        if ( $checkTicket == true ) {

       $this->InsertPengaduan = DB::table('tbl_pengaduan')->insertGetId(array(

                     'tiket_id'               => null,
                     'no_pengaduan'           => null,
                     'id_instansi'            => $this->id_instansi,
                     'id_tema'                => $this->id_tema,
                     'id_pertanyaan'          => $this->id_pertanyaan,
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

      // get ticket number after submit
      if ( $this->InsertPengaduan != '' ) {

        $this->noPengaduan = DB::table('tbl_pengaduan')->where('tiket_id', $this->InsertPengaduan)->pluck('no_pengaduan');
        $this->noPengaduan = $this->noPengaduan[0];

            /*Check input for antrian*/
          if ($tglkedatangan != '' && $jamkedatangan != ''){

            $tiket_id = $this->InsertPengaduan;

            // send params to antrian func
            $antrian       = Antrian::antrian( $tglkedatangan, $jamkedatangan, $tiket_id );
            // get value from response json and store value to $antrian variable
            $this->antrian = $antrian->getData()->no_antrian;
          
          }   

      }

        Mail::to($email)->send(new MailtrapExample($this->noPengaduan, $this->antrian, $nama)); 

          return response()->json([
            'error'            => false,
            'no_pengaduan'     => $this->noPengaduan,
            'no_antrian'       => $this->antrian
          ], 200);

     }  

     /**
      * [showHistoryByTicket description]
      * @param  [type] $ticket [description]
      * @return [type]         [description]
      */
     public function show ( Request $request ) {

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

      // Initialize Obejct for Access PertanyaanController Class
      /*$this->Pertanyaan = new PertanyaanController;*/

      try {

      /* Req Input*/

       $no_pengaduan       = $request->input('no_pengaduan');
       $status             = $request->input('status');
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

      $resultPengaduan = DB::table('tbl_pengaduan')->where('no_pengaduan', $no_pengaduan)
      ->update([ 

                     'status'       => $status,
                     'updated_by'             => $updated_by
                     /*'is_notified'             => null*/

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
            'message'      => "Data Pengaduan Berhasil di Update"
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

        $resultPengaduan = DB::table('tbl_pengaduan')->where('no_pengaduan', $ticket)->delete();

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

     public function getPdf(Request $request) {

      $ticket = $request->input('no_pengaduan');
      $antrian = $request->input('no_antrian');
      $nama = $request->input('nama');
      $email = $request->input('email');

        /* Validator */

          $rules = array(
            'no_pengaduan'     => 'required'
                  );    
          $messages = array(

            'no_pengaduan.required'     => 'Silahkan masukan no_pengaduan'
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );

        if ($validator->fails()) {

            return response()->json([
       
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

        Mail::to($email)->send(new MailtrapExample($ticket, $antrian, $nama)); 

        return 'A message has been sent to Mailtrap!';        

     }

     public function fetchAntrian() {

      $show = DB::table('tbl_antrian')
              ->join('tbl_pengaduan', 'tbl_pengaduan.tiket_id', '=', 'tbl_antrian.tiket_id')
              ->select(
                'tbl_antrian.no_antrian',
                'tbl_pengaduan.no_pengaduan',
                'tbl_pengaduan.nama',
                'tbl_antrian.tglkedatangan',
                'tbl_antrian.jamkedatangan'
              )
              ->get();

            return response()->json([
                'error' => 'false',
                'message' => $show
            ]);

     }

    }