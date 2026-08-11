<?php

namespace App\Http\Requests\Expenses;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseIndexRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'month' => ['sometimes', 'integer', 'between:1,12'],
            'year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'category_id' => ['sometimes', 'integer', Rule::exists(Category::class, 'id')],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'search' => ['sometimes', 'string', 'max:255'],
            'sort' => ['sometimes', 'string', Rule::in(['expense_date', 'amount', 'created_at'])],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
