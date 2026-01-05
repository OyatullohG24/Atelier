<?php

namespace App\Http\Requests\Material;

use Illuminate\Foundation\Http\FormRequest;

class MaterialUpdateRequest extends FormRequest
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
            'type' => 'required|in:mato,tugma,ip,zamok',
            'material_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'color_code' => 'required|string|max:50',
            'code' => 'required|string|max:100',
            'material_name' => 'required|string|max:255',
            'measurement' => 'required|string|max:50'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Material turi majburiy',
            'type.in' => 'Material turi noto\'g\'ri',
            'material_image.image' => 'Fayl rasm bo\'lishi kerak',
            'material_image.mimes' => 'Rasm formati: jpeg, png, jpg, gif, webp',
            'material_image.max' => 'Rasm hajmi 2MB dan oshmasligi kerak',
            'color_code.required' => 'Rang kodi majburiy',
            'code.required' => 'Material kodi majburiy',
            'material_name.required' => 'Material nomi majburiy',
            'measurement.required' => 'O\'lchov birligi majburiy'
        ];
    }
}
