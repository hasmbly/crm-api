<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Helpers\makePdf;
use PDF;

class MailtrapExample extends Mailable
{

    private $publicPath = 'uploads/pelayanan';

    private $urlUpload  = '/uploads/info-grafis/';

    private $ticket = '';

    private $antrian = 0;

    private $nama = '';

    use Queueable, SerializesModels;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ticket, $antrian = '', $nama)
    {
        $this->ticket = $ticket;
        $this->antrian = $antrian;
        $this->nama = $nama;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
public function build()
    {

        // $filename   = DB::table('tbl_pelayanan')->where('id_pelayanan', $id)->pluck('filename');        

        // $path = public_path($this->publicPath) . '/' . $filename[0];
        // $path = public_path($this->publicPath) . '/' . "1692249643_CRM-FRONT_V1.1.pdf";

         $getDownloadPdf = makePdf::getDownloadPdf($this->ticket, $this->antrian);


        return $this->from('t3sting.net@gmail.com', 'Pusdatin Jamsos')
            ->subject('Pusdatin Jamsos')
            ->markdown('mails.exmpl')
            ->with([
                'name' => $this->nama,
                'ticket' => $this->ticket,
                'antrian' => $this->antrian
                // 'link' => $getDownloadPdf
            ])
            ->attachData($getDownloadPdf, $this->nama.'-Tiket-'. $this->ticket .'.pdf', [
                'mime' => 'application/pdf',
            ]);
    }

}
