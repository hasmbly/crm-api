<?php

namespace App\Http\Controllers\Chart;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ChartController extends Controller {

  private $getTicket = [];
  private $allTable = ['tbl_pelayanan', 'tbl_pengaduan', 'tbl_pengaduan_iask'];
  private $labels = ['pelayanan', 'pengaduan', 'iask'];
  private $NameMonth = ['jan','feb','mar','apr','mei','jun','jul','agust','sept','oct','nov','des'];
  private $month = [1,2,3,4,5,6,7,8,9,10,11,12];
  private $data = [];
  private $SumData = [];

  private $backgroundColor = [
    'pelayanan' => "rgba(255, 99, 132, 0.2)", 
    'pengaduan' => "rgba(54, 162, 235, 0.2)",
    'iask'      => "rgba(164, 254, 80, 0.02)"
  ];
  private $borderColor   = [
    'pelayanan' => "rgba(255,99,132,1)", 
    'pengaduan' => "rgba(54, 162, 235, 1)",
    'iask'      => "rgba(164, 254, 80, 1)"
  ];

  public function show(Request $request) {
    // $tblName    = '';
    
    // $id_roles   = $request->auth->id_roles;
    // $getRoles   = DB::table('roles')->where('id', $id_roles)->pluck('name');
    // $nameRoles  = $getRoles[0];

    // if ($nameRoles == 'ADMIN' || $nameRoles == 'OPERATOR' || $nameRoles == 'SUPER_ADMIN') {
    //   $tblName = 'tbl_pengaduan';
    // } else {
    //   $tblName = 'tbl_pengaduan_iask';
    // }
    // $idInstansi = $request->auth->id_instansi;
    
    // $getData = $this->showChartAllKindOpenClose($tblName, $idInstansi);
    $getData = $this->showChartAllKindOpenClose();
    $showChart = [
                  'autoCurrentYear' => date('Y'),
                  "labels" => $this->NameMonth,
                  "data" => $getData,
                  "ticket" => $this->getTicket
              ];
       return response()->json($showChart, 200);
  }

  // public function showAllSumOpenClose($tblName, $idInstansi) {
  //   $AllData = [];
  //   $CurrYear = date('Y');
  //   try {
  //     foreach ($this->labels as $status => $value) {
  //       $this->data = [];
  //       $AllData['labels'] = $value;
  //       $AllData['backgroundColor'] = $this->backgroundColor[$value];
  //       $AllData['borderColor'] = $this->borderColor[$value];
  //       $AllData['borderWidth'] = 1;

  //   if ($tblName == 'tbl_pengaduan') {
  //       $QueryDB = DB::table('tbl_pengaduan')
  //       ->join('tbl_pertanyaan', 'tbl_pertanyaan.id_pertanyaan', '=', 'tbl_pengaduan.id_pertanyaan')
  //       ->join('tbl_master_instansi', 'tbl_master_instansi.id_instansi', '=', 'tbl_pengaduan.id_instansi')
  //       ->select(
  //               'tbl_pengaduan.no_pengaduan',
  //               'tbl_master_instansi.nama_instansi',
  //               'tbl_pengaduan.status'
  //       )
  //       ->where('tbl_pengaduan.status', $status)
  //       ->get();
  //       $this->getTicket[$value] = $QueryDB;
  //   } else {
  //       $QueryDB = DB::table('tbl_pengaduan_iask')
  //       ->join('tbl_tema', 'tbl_tema.id_tema', '=', 'tbl_pengaduan_iask.id_tema')
  //       ->join('tbl_master_instansi', 'tbl_master_instansi.id_instansi', '=', 'tbl_pengaduan_iask.id_instansi')
  //       ->select(
  //               'tbl_pengaduan_iask.no_pengaduan',
  //               'tbl_master_instansi.nama_instansi',
  //               'tbl_pengaduan_iask.status'
  //       )
  //       ->where('tbl_pengaduan_iask.status', $status)
  //       ->get();
  //       $this->getTicket[$value] = $QueryDB;      
  //   }

  //       foreach ($this->month as $key => $value) {
  //         $GetCount = DB::table($tblName)
  //         ->where('id_instansi', $idInstansi)
  //         ->whereMonth('created_at', '=', $value)
  //         ->whereYear('created_at', '=', $CurrYear)
  //         ->where('status', $status)
  //         ->get();
  //         $TotalChart = count($GetCount);
  //         array_push($this->data, $TotalChart);
  //       }
  //       $AllData['data'] = $this->data;
  //       array_push($this->SumData, $AllData);
  //     }
  //   } catch(QueryException $e) {
  //             return response()->json([
  //             'error'       => true,
  //             'code'        => $e->getCode(),
  //             'message'     => $e->getMessage()
  //           ], 500);
  //       }
  //       return $this->SumData;
  // }

    public function showChartAllKindOpenClose() {
    
    $AllData = [];
    $CurrYear = date('Y');
    $arrCount = [];

    try {
      foreach ($this->labels as $label => $value) {
        $this->data = [];
        $AllData['labels'] = $value;
        $AllData['backgroundColor'] = $this->backgroundColor[$value];
        $AllData['borderColor'] = $this->borderColor[$value];
        $AllData['borderWidth'] = 1;

          $arrCount["labels"] = $value;
          for ($i = 0; $i <= 1; $i++) {
            if ($i == 0) {
              $rowCount = DB::table($this->allTable[$label])->where('status', $i)->count();
              $arrCount["open"]  = $rowCount;
            } else if ($i == 1) {
              $rowCount = DB::table($this->allTable[$label])->where('status', $i)->count();
              $arrCount["closed"]  = $rowCount;
            }
          }
          array_push($this->getTicket, $arrCount);

          foreach ($this->month as $key => $month) {
              $GetCount = DB::table($this->allTable[$label])
              ->whereMonth('created_at', '=', $month)
              ->whereYear('created_at', '=', $CurrYear)
              ->count();
              $TotalChart = $GetCount;
              array_push($this->data, $TotalChart);            
          }
        $AllData['data'] = $this->data;
        array_push($this->SumData, $AllData);
      }
    } catch(QueryException $e) {
              return response()->json([
              'error'       => true,
              'code'        => $e->getCode(),
              'message'     => $e->getMessage()
            ], 500);
        }
        return $this->SumData;
  }


}