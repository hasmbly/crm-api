<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class InfoPengadu
{ 

     /**
      * [showCommentByTicket description]
      * @param  [type] $ticket [description]
      * @return [type]         [description]
      */
     public static function show( $request, $tblName ) {

      $detail           = null;
      $kjp              = null;
      $kjmu             = null;
      $klj              = null;
      $pkd              = null;
      $disabel          = null;

      $value            = null;

        try {

          if ($request->has('no_pengaduan')) {

            $showBy = 'no_pengaduan';
            $value = $request->input('no_pengaduan');

            $getNIK = DB::table($tblName)->where('no_pengaduan', $value)->pluck('nik');

              if ( count($getNIK) > 0 ) {
                 $value = $getNIK[0];
              } elseif ( count($getNIK) < 0 ){
                  return response()->json([
                  'error'         => false,
                  'message'      => "Data No Found"
                ], 404);                
              }          

           } elseif ($request->has('nik')) {

             $showBy = 'nik';
             $value = $request->input('nik');

             $checkNIK = DB::table($tblName)->where('nik', $value)->pluck('nik');

             if ( count($checkNIK) < 0 ) {
                  return response()->json([
                  'error'         => false,
                  'message'      => "Data No Found"
                ], 404);                  
             }

           }  

        // detail
        $getDetail = DB::table('tbl_bdt_master')
                ->join('tbl_bdt_kriteria', 'tbl_bdt_kriteria.NIK', '=', 'tbl_bdt_master.nik' )
                ->join('tbl_bdt_pengurus', 'tbl_bdt_pengurus.IDPENGURUS', '=', 'tbl_bdt_master.IDPENGURUS' )
                ->select(
                  'tbl_bdt_kriteria.NoKK',
                  'tbl_bdt_master.TglLahir',
                  'tbl_bdt_pengurus.IDPENGURUS',
                  'tbl_bdt_pengurus.Alamat_Pengurus'
                )
                ->where('tbl_bdt_master.nik', $value)
                ->get();

        if ( count($getDetail) > 0 ) $detail = $getDetail[0]; 

        // kjp
        $myKjp = ['berhak' => 'TIDAK'];
        $getKjp = DB::table('tbl_kjp_datasiswa')
                ->join('tbl_kjp_datasekolah', 'tbl_kjp_datasekolah.NPSN', '=', 'tbl_kjp_datasiswa.NPSN')
                ->select(
                  'tbl_kjp_datasiswa.NIK_SISWA',
                  'tbl_kjp_datasiswa.NAMA_SISWA',
                  'tbl_kjp_datasekolah.NAMA SEKOLAH',
                  'tbl_kjp_datasiswa.ADA/TIDAK_DI_BDT',
                  'tbl_kjp_datasiswa.STATUS'
                )
                ->where('tbl_kjp_datasiswa.NIK_SISWA', $value)
                ->get();

        if ( count($getKjp) > 0 ) {
          $kjp = $getKjp[0];
          $myKjp['berhak'] = 'YA';
          foreach ($kjp as $key => $value) {
            $myKjp[$key] = $value;
          }
        }

        // kjmu
        $myKjmu = ['berhak' => 'TIDAK'];
        $getKjmu = DB::table('tbl_kjmu_datasiswa')
                ->join('tbl_kjmu_datasekolah', 'tbl_kjmu_datasekolah.NPSN', '=', 'tbl_kjmu_datasiswa.NPSN')
                ->select(
                  'tbl_kjmu_datasiswa.NIK_SISWA',
                  'tbl_kjmu_datasiswa.NAMA_SISWA',
                  'tbl_kjmu_datasekolah.NAMA SEKOLAH',
                  'tbl_kjmu_datasiswa.ADA/TIDAK DI BDT',
                  'tbl_kjmu_datasiswa.STATUS'
                )
                ->where('tbl_kjmu_datasiswa.NIK_SISWA', $value)
                ->get();

        if ( count($getKjmu) > 0 ) { 
          $kjmu = $getKjmu[0];
          $myKjmu['berhak'] = 'YA';
          foreach ($kjmu as $key => $value) {
            $myKjmu[$key] = $value;
          }
        }   

        // klj
        $myKlj = ['berhak' => 'TIDAK'];
        $getKlj = DB::table('tbl_klj')
                ->where('tbl_klj.NIK', $value)
                ->get();

        if ( count($getKlj) > 0 ) { 
          $klj = $getKlj[0];
          $myKlj['berhak'] = 'YA';
          foreach ($klj as $key => $value) {
            $myKlj[$key] = $value;
          }
        }

        // pkd
        $myPkd = ['berhak' => 'TIDAK'];
        $getPkd = DB::table('tbl_pkd_anak')
                ->where('tbl_pkd_anak.NIK', $value)
                ->get();

        if ( count($getPkd) > 0 ) { 
          $pkd = $getPkd[0];
          $myPkd['berhak'] = 'YA';
          foreach ($pkd as $key => $value) {
            $myPkd[$key] = $value;
          }
        }         

        // disabel
        $myDisabel = ['berhak' => 'TIDAK'];
        $getDisabel = DB::table('tbl_disabel')
                ->where('tbl_disabel.NIK', $value)
                ->get();

        if ( count($getDisabel) > 0 ) { 
          $disabel = $getDisabel[0];
          $myDisabel['berhak'] = 'YA';
          foreach ($disabel as $key => $value) {
            $myDisabel[$key] = $value;
          }
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
            'message'      => $result = [
                                'bdt' => $detail,
                                'kjp' => $myKjp,
                                'kjmu' => $myKjmu,
                                'klj' => $myKlj,
                                'pkd' => $myPkd,
                                'disabel' => $myDisabel
                              ]
          ], 200);

     }



  }