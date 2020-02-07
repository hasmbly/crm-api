<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class SearchNik
{ 

    private $tableKJP       = 'tbl_kjp_datasiswa';
    private $tableKJMU      = 'tbl_kjmu_datasiswa';
    private $tableDisabel   = 'tbl_disabel';
    private $tableKLJ       = 'tbl_klj';
    private $tablePKD       = 'tbl_pkd_anak';

    private $colNikKJP      = 'NIK_SISWA';
    private $colNikKJMU     = 'NIK_SISWA';
    private $colNikDisabel  = 'NIK';
    private $colNikKLJ      = 'NIK';
    private $colNikPKD      = 'NIK';

    private $statusKJP     = false;
    private $statusKJMU    = false; 
    private $statusDisabel = false;
    private $statusKLJ     = false;
    private $statusPKD     = false;

    private $passKJP          = false;
    private $passKJMU         = false;
    private $passDisabel      = false;
    private $passKLJ          = false;
    private $passPKD          = false;

    private $riwayatKJP     = [];
    private $riwayatKJMU    = [];
    private $riwayatDisabel = [];
    private $riwayatKLJ     = [];
    private $riwayatPKD     = [];

    private $result         = [];

    private $nik            = '';

    private $countFound     = 0;

    public function checkNikBansos($nik)
    {

      $this->nik = $nik;

      $tbl_kjp_datasiswa  = DB::table($this->tableKJP)->where($this->colNikKJP, $nik)->pluck($this->colNikKJP);
      if (count($tbl_kjp_datasiswa) != 0) {
        $this->countFound++;
        $tbl_kjp_datasiswa  = $tbl_kjp_datasiswa[0];
      }

      $tbl_kjmu_datasiswa  = DB::table($this->tableKJMU)->where($this->colNikKJMU, $nik)->pluck($this->colNikKJMU);
      if (count($tbl_kjmu_datasiswa) != 0) {
        $this->countFound++;
        $tbl_kjmu_datasiswa  = $tbl_kjmu_datasiswa[0];
      }

      $tbl_disabel  = DB::table($this->tableDisabel)->where($this->colNikDisabel, $nik)->pluck($this->colNikDisabel);
      if (count($tbl_disabel) != 0) {
        $this->countFound++;
        $tbl_disabel  = $tbl_disabel[0];
      }

      $tbl_klj  = DB::table($this->tableKLJ)->where($this->colNikKLJ, $nik)->pluck($this->colNikKLJ);
      if (count($tbl_klj) != 0) {
        $this->countFound++;
        $tbl_klj  = $tbl_klj[0];
      }

      $tbl_pkd  = DB::table($this->tablePKD)->where($this->colNikPKD, $nik)->pluck($this->colNikPKD);
      if (count($tbl_pkd) != 0) {
        $this->countFound++;
        $tbl_pkd  = $tbl_pkd[0];
      }      

    for ($i = 0; $i < $this->countFound; $i++) {

      switch ($nik) {

        case $tbl_kjp_datasiswa:
        if ($this->passKJP == false) {
          $this->statusKJP      = true;
          $this->getResult($this->tableKJP, $this->colNikKJP, $this->nik, 'riwayatKJP');
          $this->passKJP = true;
          $tbl_kjp_datasiswa = 0;
          continue;
         } else {
            continue;
         }
          // return $this->result;

        case $tbl_kjmu_datasiswa:
         if ($this->passKJMU == false) {
          $this->statusKJMU     = true;
          $this->getResult($this->tableKJMU, $this->colNikKJMU, $this->nik, 'riwayatKJMU');
          $this->passKJMU = true;
          $tbl_kjmu_datasiswa = 0;
          continue;
         } else {
            continue;
         }                    
          // return $this->result;

        case $tbl_disabel:
         if ($this->passDisabel == false) {
          $this->statusDisabel  = true;
          $this->getResult($this->tableDisabel, $this->colNikDisabel, $this->nik, 'riwayatDisabel');
          $this->passDisabel = true;
          $tbl_disabel = 0;
          continue;
         } else {
            continue;
         }            
          // return $this->result;

        case $tbl_klj:
         if ($this->passKLJ == false) {
          $this->statusKLJ      = true;
          $this->getResult($this->tableKLJ, $this->colNikKLJ, $this->nik, 'riwayatKLJ');
          $this->passKLJ = true;
          $tbl_klj = 0;
          continue;
         } else {
            continue;
         }            
          // return $this->result;

        case $tbl_pkd:
         if ($this->passPKD == false) {
          $this->statusPKD      = true;
          $this->getResult($this->tablePKD, $this->colNikPKD, $this->nik, 'riwayatPKD');
          $this->passPKD = true;
          $tbl_pkd = 0;
          continue;
         } else {
            continue;
         }             
          // return $this->result;

        default:
          return false;
          break;
      }

    }

      return $this->result;

    }

    public function getResult( string $tableName, string $columnName, string $nik, string $riwayatName)
    {
         $query   = DB::table($tableName)->where($columnName, $this->nik)->get();

         if ($tableName == $this->tableKJMU || $tableName == $this->tableKJP) {

             foreach ($query as $row) {
                  
                $tahap    = $row->TAHAP;
                $tahun    = $row->TAHUN;
                $periode  = $row->PERIODE;

                array_push($this->$riwayatName, 
                  [
                      'periode' => "Tahap $tahap $periode $tahun"
                  ]);
               }  
         } 
         else if ($tableName == $this->tableDisabel || $tableName == $this->tableKLJ || $tableName == $this->tablePKD) {

              foreach ($query as $row) {            
                $year    = $row->TAHUN;
                array_push($this->$riwayatName, 
                  [
                      'periode' => "Tahun $year"
                  ]);
               }              
         }
 
         $this->result = array(

            [
              'KJP'     => $this->statusKJP,
              'riwayat' => $this->riwayatKJP
            ],
            [
              'KJMU'    => $this->statusKJMU,
              'riwayat' => $this->riwayatKJMU
            ],
            [
              'KPDJ' => $this->statusDisabel,
              'riwayat' => $this->riwayatDisabel
            ],
            [
              'KLJ'     => $this->statusKLJ,
              'riwayat' => $this->riwayatKLJ                            
            ],
            [
              'PKD Anak'     => $this->statusPKD,
              'riwayat'      => $this->riwayatPKD
            ]            

        );          

        return $this->result;
    }

  }