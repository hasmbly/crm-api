<?php

namespace App\Http\Controllers\Chart;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ChartFrontController extends Controller {

  private $labels = [];
  private $NameMonth = ['jan','feb','mar','apr','mei','jun','jul','agust','sept','oct','nov','des'];
  private $month = [1,2,3,4,5,6,7,8,9,10,11,12];
  private $data = [];
  private $SumData = [];
  private $backgroundColor = ['Apakah saya terdaftar dalam DT FM-OTM ?' => "rgba(255, 99, 132, 0.2)", 'Bagaimana caranya agar terdaftar dalam DT FM-OTM ?' => "rgba(54, 162, 235, 0.2)", 'Saya terdaftar dalam DT FMOTM tapi saya belum mendapatkan bansos' => "rgba(164, 254, 80, 0.02)"];
  private $borderColor   = ['Apakah saya terdaftar dalam DT FM-OTM ?' => "rgba(255,99,132,1)", 'Bagaimana caranya agar terdaftar dalam DT FM-OTM ?' => "rgba(54, 162, 235, 1)", 'Saya terdaftar dalam DT FMOTM tapi saya belum mendapatkan bansos' => "rgba(164, 254, 80, 1)"];

  public function show(Request $request) {
    $getData = $this->showChartCRMFront();
    $showChart = [
                'autoCurrentYear' => date('Y'),
                "labels" => $this->NameMonth,
                "data" => $getData
              ];
       return response()->json($showChart, 200);    
  }

  public function showChartCRMFront() {
    $AllData = [];
    $CurrYear = date('Y');
    try {
      $this->labels = DB::table('tbl_pertanyaan')->where('id_instansi', "01")->pluck('pertanyaan');
      // array_push($labels, $arr);

      foreach ($this->labels as $id_pertanyaan => $value) {
        $this->data = [];
        $AllData['labels'] = $value;
        $AllData['backgroundColor'] = $this->backgroundColor[$value];
        $AllData['borderColor'] = $this->borderColor[$value];
        $AllData['borderWidth'] = 1;

        foreach ($this->month as $key => $value) {
          $GetChart = DB::table('tbl_pengaduan')
          ->whereMonth('created_at', '=', $value)
          ->whereYear('created_at', '=', $CurrYear)
          ->where('id_pertanyaan', $id_pertanyaan+1)
          ->get();
          $TotalChart = count($GetChart);
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