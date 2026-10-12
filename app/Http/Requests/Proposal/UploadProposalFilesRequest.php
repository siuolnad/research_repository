<?php

declare(strict_types=1);

namespace App\Http\Requests\Proposal;

use Illuminate\Foundation\Http\FormRequest;

class UploadProposalFilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'max:10'],
            'files.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,xlsx'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'files.required' => 'Choose at least one file to upload.',
            'files.max' => 'You can upload at most 10 files at a time.',
            'files.*.max' => 'Each file must be 10 MB or smaller.',
            'files.*.mimes' => 'Files must be PDF, DOC, DOCX or XLSX.',
            'files.*.uploaded' => 'A file failed to upload. Each file must be 10 MB or smaller.',
        ];
    }
}
