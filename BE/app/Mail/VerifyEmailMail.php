<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $verifyUrl
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Xác thực email tài khoản MindHub')
            ->html(
                '<h2>Xin chào ' . e($this->user->full_name) . '</h2>' .
                '<p>Bạn vừa đăng ký tài khoản tại MindHub.</p>' .
                '<p>Vui lòng bấm vào link bên dưới để xác thực email:</p>' .
                '<p><a href="' . e($this->verifyUrl) . '">Xác thực email</a></p>' .
                '<p>Nếu nút không hoạt động, hãy copy link sau:</p>' .
                '<p>' . e($this->verifyUrl) . '</p>' .
                '<p>Link có hiệu lực trong 60 phút.</p>'
            );
    }
}
