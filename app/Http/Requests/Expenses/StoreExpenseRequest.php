<?php

namespace App\Http\Requests\Expenses;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
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
                'required',
                'integer',
                Rule::exists(Category::class, 'id')->where('is_active', true),
            ],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
