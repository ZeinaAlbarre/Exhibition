<?php

namespace App\Http\Requests;

use App\Http\Responses\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
class CompanySiginUpRequst extends FormRequest
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
            'name' =>'required|string|max:255',
            'email' => 'required|max:255',
            'phone' => 'required|numeric|digits:10',
            'password' => 'required|string|min:8|max:30|confirmed',
            'password_confirmation'=>'required|same:password',
            'company_name' => 'required|string|max:255',
            'business_email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'office_address'=>'string|max:255',
            'summary' => 'required|string|max:1000',
            'body' => 'required|string',
            'commercial_register'=>'required|image|mimes:jpeg,png,pdf,jpg',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator){
        throw new ValidationException($validator,Response::Validation([],$validator->errors()));
    }


}
