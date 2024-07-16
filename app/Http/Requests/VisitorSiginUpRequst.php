<?php

namespace App\Http\Requests;


use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class VisitorSiginUpRequst extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'phone' => 'required|numeric|digits:10',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation'=>'required|same:password',
            'gender' => 'required|in:male,female' ,
            'birth_date' => 'required',

        ];
    }


    protected function failedValidation(Validator $validator){
        throw (new ValidationException($validator,Response::Validation([],$validator->errors())));
    }

}
