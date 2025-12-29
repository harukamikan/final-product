<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],

            'email' => [
                'sometimes',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],


            'background_type' => [
                'sometimes',
                'nullable',
                'in:color,gradient',
            ],

            'background_value' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
            'nickname' => [
                'sometimes', 
                'nullable',
                'string',
                'max:255'
            ],
        ];
    }
}
