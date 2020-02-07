<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class Ticket
{ 

    public static function checkStatusTicket( $nik, $tblName )

    {

      $checkTicket = DB::table($tblName)->where('nik', $nik)->where('status', 0)->pluck('status');

      if ( count($checkTicket) != 0  ) {

          return false;

      } else {

          return true;

       }

    }

     /**
      * [showPengaduanTicket description]
      * @param  [type] $ticket [description]
      * @return [type]         [description]
      */
     public static function show( $request, $tblName ) {

        $no_pengaduan = $request->input('no_pengaduan');
        $nik          = $request->input('nik');

      try {     

           if ( $tblName == 'tbl_pengaduan' ) {

           $show = DB::table($tblName)
                            ->join('tbl_master_instansi', 'tbl_master_instansi.id_instansi', '=', $tblName . '.id_instansi')
                            ->join('tbl_pertanyaan', 'tbl_pertanyaan.id_pertanyaan', '=', $tblName . '.id_pertanyaan')
                            ->select(
                              $tblName . '.no_pengaduan',
                              'tbl_master_instansi.nama_instansi',
                              'tbl_pertanyaan.pertanyaan',
                              $tblName . '.uraian',
                              $tblName . '.nama',
                              $tblName . '.nik',
                              $tblName . '.alamat',
                              $tblName . '.email',
                              $tblName . '.nohp',
                              $tblName . '.created_at',
                              $tblName . '.created_by',
                              $tblName . '.updated_at',
                              $tblName . '.updated_by',
                              $tblName . '.status'
                            )
                            ->where($tblName . '.no_pengaduan', $no_pengaduan)
                            ->where($tblName . '.nik', $nik)
                            ->first();

         } elseif ( $tblName == 'tbl_pengaduan_iask' ) {

           $show = DB::table($tblName)
                            ->join('tbl_master_instansi', 'tbl_master_instansi.id_instansi', '=', $tblName . '.id_instansi')
                            ->join('tbl_tema', 'tbl_tema.id_tema', '=', $tblName . '.id_tema')
                            ->select(
                              $tblName . '.no_pengaduan',
                              'tbl_master_instansi.nama_instansi',
                              'tbl_tema.tema',
                              $tblName . '.uraian',
                              $tblName . '.nama',
                              $tblName . '.nik',
                              $tblName . '.alamat',
                              $tblName . '.email',
                              $tblName . '.nohp',
                              $tblName . '.created_at',
                              $tblName . '.created_by',
                              $tblName . '.updated_at',
                              $tblName . '.updated_by',
                              $tblName . '.status'
                            )
                            ->where($tblName . '.no_pengaduan', $no_pengaduan)
                            ->where($tblName . '.nik', $nik)
                            ->first();;

         }                          
      
      } catch(QueryException $e) {

            return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);
      }   
            return response()->json([
            'error'         => false,
            'message'      => $show
          ], 200);

     }    

  }