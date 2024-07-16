<?php

namespace App\Http\Requests;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateExhibitionRequest extends FormRequest
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
            'title'=>'required|string|max:30',
            'cover_img'=>'image|mimes:jpeg,png,jpg|nullable',
            'body'=>'required|string',
            'start_date'=>'required|date',
            'end_date'=>'required|date',
            'time'=>'required',
            'price'=>'required|numeric',
            'location'=>'required|string',
            'exhibition_map'=>'image|mimes:jpeg,png,jpg|nullable',
            'number_of_stands'=>'numeric|nullable',
        ];
    }
    protected function failedValidation(Validator $validator){

        throw(new ValidationException($validator,Response::Validation([],$validator->errors())));
    }
}
