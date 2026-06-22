<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AkunAnggotaDibuat extends Mailable
{
    use Queueable, SerializesModels;

    public string $namaAnggota;
    public string $nomorAnggota;
    public string $emailLogin;
    public string $passwordPlain;
    public string $namaApp;

    public function __construct(string $namaAnggota, string $nomorAnggota, string $emailLogin, string $passwordPlain)
    {
        $this->namaAnggota   = $namaAnggota;
        $this->nomorAnggota  = $nomorAnggota;
        $this->emailLogin    = $emailLogin;
        $this->passwordPlain = $passwordPlain;
        $this->namaApp       = config('app.name', 'myKodagama');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun Koperasi Anda Telah Dibuat — ' . $this->namaApp,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.akun-anggota-dibuat',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
