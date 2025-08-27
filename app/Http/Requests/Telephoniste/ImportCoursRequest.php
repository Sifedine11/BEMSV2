<?php

namespace App\Http\Requests\Telephoniste;

use Illuminate\Foundation\Http\FormRequest;

class ImportCoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // routes déjà protégées par 'auth' + 'role:telephoniste'
    }

    public function rules(): array
    {
        return [
            'fichier' => ['required', 'file', 'mimes:xlsx', 'max:10240'], // 10 Mo
        ];
    }

    public function messages(): array
    {
        return [
            'fichier.required' => 'Veuillez sélectionner un fichier Excel.',
            'fichier.mimes'    => 'Le fichier doit être au format .xlsx.',
            'fichier.max'      => 'Le fichier est trop volumineux (max 10 Mo).',
        ];
    }
}
