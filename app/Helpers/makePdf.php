<?php

namespace App\Helpers;

use Illuminate\Http\Response;

use Illuminate\Support\Facades\View;

use PDF;

use Illuminate\Http\Request;   

class makePdf
{   
    
    static private $publicPath = './resources/views';

     public static function getDownloadPdf($ticket, $noAntrian = '') {
      
        $path = makePdf::$publicPath;

        

        // $wordAntrian = '';

        // if ( $noAntrian != '' || $noAntrian != null) {
        //   $wordAntrian = '<a href="#"><strong>'. $noAntrian.'</strong></a>';
        // }

        $data = [
          'ticket' => $ticket,
          'antrian' => $noAntrian
        ];

        $pdf = PDF::loadView('myPdf', $data)
          ->setPaper('a6', 'landscape');

        return $pdf->stream();
        // return $pdf->download('ticket.pdf');          
      
     }

     public function geStreamPdf() {
      
      
     }     

}