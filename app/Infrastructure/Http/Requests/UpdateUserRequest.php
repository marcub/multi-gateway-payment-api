<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['sometimes', 'email'],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => ['sometimes', 'string', 'in:admin,manager,finance,user']
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (!$this->hasAny(['email', 'password', 'role'])) {
                $validator->errors()->add('payload', 'At least one field must be provided for update.');
            }
        });
    }
}