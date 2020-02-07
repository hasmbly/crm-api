<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Carbon\Carbon;

class Antrian
{ 

     public static function antrian(string $tglkedatangan, string $jamkedatangan, string $tiket_id ) {

      try { 

       $checkResult = self::check();

       $today                 = Carbon::today()->toDateString();

       /*$convertDate           = Carbon::createFromFormat('Y-m-d', $tglkedatangan);*/
       $convert_tgl           = Carbon::parse($tglkedatangan)->format('Y-m-d');

       $InsertAntrian = DB::table('tbl_antrian')->insertGetId(array(
                      
                      'tiket_id'         => $tiket_id,
                      'tglkedatangan'    => $convert_tgl,
                      'jamkedatangan'    => $jamkedatangan,
                      'created_at'       => $today
         ));

    } catch (QueryException $e) {

        return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);

    }
        $noAntrian = DB::table('tbl_antrian')
                  ->where('id_antrian', $InsertAntrian)
                  ->pluck('no_antrian');

          return response()->json([
            'error'           => false,
            'no_antrian'      => $noAntrian[0],
            'checked_status'  => $checkResult->getData()->message
          ], 200);


     }

     public static function check() {

      $today = Carbon::today()->toDateString();

      $DateAntrian = DB::table('tbl_antrian')->where('created_at', '<', $today )->first();

      if ($DateAntrian != null){

        DB::table('tbl_antrian')->truncate();

            return response()->json([
            'error'         => false,
            'message'      => "table truncated"
          ]);

      } else {

            return response()->json([
            'error'         => false,
            'message'      => "nothing truncated"
          ]);

      }
    
     }        

  }