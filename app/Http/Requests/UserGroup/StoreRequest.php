<?php

namespace App\Http\Requests\UserGroup;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'roleName'=>'required',
            'user_type'=>'required',
            'permissionArray'=>'required',
        ];
    }
    public function messages(): array
    {
        return [
            'roleName.required'=>'Role Name Is Required',
            'user_type.required'=>'User Type Is Required',
            'permissionArray.required'=>'Alteast One Permission Required',
        ];
    }
}
