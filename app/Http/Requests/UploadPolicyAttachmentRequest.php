<?php

namespace App\Http\Requests;

class UploadPolicyAttachmentRequest extends FormRequest
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
            'attachment_key' => ['required', 'string', 'size:16'],
            'attachment' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,bmp,gif,svg,webp,csv,txt',
                'max:1024',
            ],
        ];
    }
}
