<?php
namespace App\Http\Requests\Auth;
use App\Http\Requests\BaseApiRequest;
use Illuminate\Validation\Rules\Password;
class ResetPasswordRequest extends BaseApiRequest
{
    public function rule() {
        return
        [
            'email'=> ['required','email','max:255'],
            'token'=> ['required','string'],
            'password'=> ['required','string','confirmed',Password::min(8)]
        ];
    }
    public function message()
    {
        return
        [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 255 kí tự.',
            'token.required' => 'Token đặt lại mật khẩu không được để trống.',
            'token.string' => 'Token đặt lại mật khẩu phải là dạng chuỗi.',
            'password.required' => 'Mật khẩu mới không được để trống',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 kí tự',
        ];
    }
}
