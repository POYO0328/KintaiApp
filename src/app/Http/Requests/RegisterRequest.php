<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // 確認用はpassword_confirmationフィールドに対応
        ];
    }

    public function authorize()
    {
        return true;
    }
}
