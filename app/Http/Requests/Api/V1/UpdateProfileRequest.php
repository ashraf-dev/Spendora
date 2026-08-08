<?php

namespace App\Http\Requests\Api\V1;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateProfileRequest extends ApiFormRequest
{
    use ProfileValidationRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->profileRules($this->user()->id);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email') && is_string($this->email)) {
            $this->merge([
                'email' => strtolower(trim($this->email)),
            ]);
        }
    }
}
