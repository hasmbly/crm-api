<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MailResetPass extends Mailable
{

    private $nama           = '';
    private $defaultPass    = '';

    use Queueable, SerializesModels;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nama, $defaultPass)
    {
        $this->nama        = $nama;
        $this->defaultPass = $defaultPass;
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
            ->markdown('mails.resetPass')
            ->with([
                'name' => $this->nama,
                'defaultPass' => $this->defaultPass
            ]);
    }

}