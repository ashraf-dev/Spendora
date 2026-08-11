<?php

namespace App\Http\Requests\Expenses;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists(Category::class, 'id')->where('is_active', true),
            ],
            'expense_date' => ['sometimes', 'required', 'date'],
            'amount' => ['sometimes', 'required', 'numeric', 'gt:0', 'decimal:0,2'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
