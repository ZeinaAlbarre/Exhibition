<?php

namespace App\Http\Requests;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StandBookingRequest extends FormRequest
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
            'stands'=>'present|array',
            'stands.*.id'=>'required|numeric',
            'stands.*.stand_price'=>'required|numeric'
        ];
    }

    protected function failedValidation(Validator $validator){

        throw(new ValidationException($validator,Response::Validation([],$validator->errors())));
    }
}
