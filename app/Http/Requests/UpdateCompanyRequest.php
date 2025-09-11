<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'email' => ['required', 'email', 'regex:/(.*)@([A-Za-z0-9\-\_\.]+)\.com/i', Rule::unique('companies')->ignore($this->companyId)]
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The company name is required.',
        ];
    }
}
