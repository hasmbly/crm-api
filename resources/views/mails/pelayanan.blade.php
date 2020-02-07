@component('mail::message')
Hi, **{{ $name }}**,
Terima kasih sudah mengirimkan permintaan data anda.

No Tiket Pelayanan Anda : **{{ $ticket }}**

Sincerely,  
Pusdatin Jamsos DKI.
@endcomponent