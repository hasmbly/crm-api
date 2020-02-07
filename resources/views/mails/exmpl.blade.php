@component('mail::message')
Hi, **{{ $name }}**,
Terima kasih sudah mengirimkan pengaduan anda.

No Tiket Pengaduan Anda : **{{ $ticket }}**

No Tiket Antrian Anda : **{{ $antrian }}**

Silahkan Download attachment Nomer Pengaduan anda
Sincerely,  
Pusdatin Jamsos DKI.
@endcomponent