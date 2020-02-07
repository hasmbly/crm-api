<?php
namespace App\Http\Controllers\Nik;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Validator;
use App\Helpers\SearchNik;

class SearchNIKController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

     public function show($nik)
     {

          $subnik = substr( $nik, 0, 2 ) === "31";
          $lenNik = strlen($nik);

        if($nik != ''){

          if($lenNik == 16){

            // if ($subnik == true) {

                if (is_numeric($nik)) {

                  try {

                   // check NIK
                   $checkNIK = DB::table('view_bdt_master')->where('NIK', $nik)->first();

                  } catch (QueryException $e) {

                      return response()->json([
                          'error'       => true,
                          'code'        => $e->getCode(),
                          'message'     => $e->getMessage()
                        ], 500);

                  }  

                    if ($checkNIK != ''){

                      try {
                    // check BANSOS
                    $SearchNIK    = new SearchNik();
                    $checkBANSOS  = $SearchNIK->checkNikBansos($nik);

                  } catch (QueryException $e) {

                      return response()->json([
                          'error'       => true,
                          'code'        => $e->getCode(),
                          'message'     => $e->getMessage()
                        ], 500);

                  }  

                        if ($checkBANSOS != '' || $checkBANSOS != false) {
                         return response()->json([
                          'error'       => false,
                          'message'     => $checkBANSOS
                        ], 200);
                    } else {

                         return response()->json([
                          'error'   => true,
                          'message' => 'NIK anda terdaftar dalam BDT, tapi belum terdaftar dalam bansos',
                          'checkBansos' => $checkBANSOS
                        ], 404);

                       }

                     } else {

                          return response()->json([
                          'error'   => true,
                          'message' => 'Maaf anda tidak terdaftar di BDT'
                          ], 404);
                     }

                } else {
                          return response()->json([
                          'error'   => true,
                          'message' => 'Invalid Type, Nik must be integer'
                  ], 400);
              }

                // }
              //   else {
              //             return response()->json([
              //             'error'   => true,
              //             'message' => 'Not Found, Maaf NIK anda bukan di Jakarta'
              //     ], 404);
              // }

          } else {
                      return response()->json([
                      'error'   => true,
                      'message' => 'Invalid, Jumlah NIK Harus 16 digit'
              ], 400);

            }

        }

     }

    }
