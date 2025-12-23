<?php

namespace App\Http\Requests\Clothes;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClothesStoreRequest extends FormRequest
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
            'clothes_name' => ['required'],
            'clothes_image' => ['required']
        ];
    }

    public function messages()
    {
        return [
            'clothes_name.required' => "Malumotni kirit"
        ];
    }

    /**
     * Validation muvaffaqiyatsiz bo'lganda 200 status bilan javob qaytarish
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validatsiya xatoligi',
                'errors' => $validator->errors()
            ], 200) // 200 status code
        );
    }
}
