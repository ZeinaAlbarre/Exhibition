<?php

namespace App\Http\Requests;

use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class addScheduleRequest extends FormRequest
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
            'topic_name' => 'required|string|max:255',
            'speaker_name' => 'required|string|max:255',
            'summary' => 'required|string',
            'body' => 'required|string',
            'time' => 'required',
            'date' => 'required',
            'about_speaker' => 'required|string',
            'speaker_email' => 'required|email|max:255',
            'linkedin' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',

        ];
    }



    protected function failedValidation(Validator $validator){
        throw (new ValidationException($validator,Response::Validation([],$validator->errors())));
    }
}
