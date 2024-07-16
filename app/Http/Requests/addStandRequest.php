<?php

namespace App\Http\Requests;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class addStandRequest extends FormRequest
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
               'size' => 'required|min:0',
               'price' => 'required|numeric|min:0',
           ];

    }



    protected function failedValidation(Validator $validator){
        throw (new ValidationException($validator,Response::Validation([],$validator->errors())));
    }
}
