<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends FormRequest
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
            'phone' => 'required|numeric|digits:10',
            'business_email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'office_address'=>'string|max:255',
            'summary' => 'required|string|max:1000',
            'body' => 'required|string',
        ];
    }
}
