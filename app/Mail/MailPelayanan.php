<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class MailPelayanan extends Mailable
{

    private $publicPath = 'uploads/pelayanan';

    private $ticket = '';

    private $nama = '';

    use Queueable, SerializesModels;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ticket, $nama)
    {
        $this->ticket = $ticket;
        $this->nama = $nama;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
public function build()
    {

        return $this->from('t3sting.net@gmail.com', 'Pusdatin Jamsos')
            ->subject('Pusdatin Jamsos')
            ->markdown('mails.pelayanan')
            ->with([
                'name' => $this->nama,
                'ticket' => $this->ticket
            ]);
    }

}