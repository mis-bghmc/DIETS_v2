<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DietOrderFormRequest extends FormRequest
{
    protected $OralDietCodes = ['19', '01', '46', '18', '17', '16', '30', '05', '40', '06', '12', '11', '09', '25'];
    protected $EnteralDietCodes = ['34', '35', '36', '37', '38', '39', '45'];

    protected $allergyTypes = ['08', '09', '10', '11'];
    protected $allergySubtypes = ['01', '02', '03', '04', '05', '06', '07'];
    protected $precautionTypes = ['24', '27', '28', '41', '10', '58'];

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
        $rules = [
            'hpercode' => 'required|string',
            'enccode' => 'required|string',
            'dietCategory' => ['required', 'string', 'size:1', Rule::in(['1', '2'])], // 1 for Oral Diet, 2 for Enteral Diet
            'calories' => 'required|string',
            'patAge' => 'required|string',
            'allergyType' => ['required', 'string', 'size:2', Rule::in($this->allergyTypes)],
            'licno' => 'required|string',
            'entryBy' => 'required|string',
        ];

        // If patient is Greater than 1 year old
        if ((int) $this->input('age') >= 1) {
            $rules['protein'] = 'required|string';
            $rules['fiber'] = 'required|string';


            // If SNS is Not Selected Fats & Carbs are Required
            if (!$this->input('onsType')) {
                $rules['fats'] = 'required|string';
                $rules['carbohydrates'] = 'required|string';
            }
        }

        // If patient is with food allergies
        if ($this->input('allergyType') === '10') {
            $rules['allergySubtype'] = ['required', 'array', 'size:2', Rule::in($this->allergySubtypes)];
        } else if ($this->input('allergyType') === '11') {
            $rules['precautionType'] = ['required', 'string'];
        }

        // If patient is with SNS
        if ($this->input('onsType')) {
            $rules['onsType'] = 'required|string';
            $rules['onsFrequency'] = 'required|array';
        }

        // If Oral Diet is selected
        if ($this->input('dietCategory') === '1') {


            $rules['dietCode1'] = ['required', 'string', 'size:2', Rule::in($this->OralDietCodes)];

            // If Diet Type 1 is Therapeutic: Requires Diet Code 2
            if ($this->input('dietCode1') === '46') {
                $rules['dietCode2'] = 'required|string';
            }


        }

        // If Enteral Diet is selected
        else if ($this->input('dietCategory') === '2') {
            $rules['dietCode1'] = ['required', 'string', 'size:2', Rule::in($this->EnteralDietCodes)];
            $rules['feedingMode'] = 'required|string';
            $rules['feedingDuration'] = 'required|string';
            $rules['feedingFrequency'] = 'required|string';
        }


        return $rules;

    }

    public function messages(): array
    {
        return [
            'hpercode.required' => 'Hospital code is required.',
            'enccode.required' => 'Encounter code is required.',

            'dietCategory.required' => 'Diet category is required.',
            'dietCode1.required' => 'Diet code 1 is required.',
            'dietCode2.required' => 'Diet code 2 is required for Therapeutic Diet Type.',

            'age.required' => 'Age is required.',
            'entryBy.required' => 'Entry by is required.',
            'previousDiet.required' => 'Previous diet is required.',

            'calories.required' => 'Calories are required.',
            'allergyType.required' => 'Allergy type is required.',

            'protein.required' => 'Protein is required.',
            'fiber.required' => 'Fiber is required.',
            'fats.required' => 'Fats are required.',
            'carbohydrates.required' => 'Carbohydrates are required.',

            'onsType.required' => 'ONS type is required.',
            'onsFrequency.required' => 'ONS frequency is required.',

            'feedingMode.required' => 'Feeding mode is required for Enteral Diet.',
            'feedingDuration.required' => 'Feeding duration is required for Enteral Diet.',
            'feedingFrequency.required' => 'Feeding frequency is required for Enteral Diet.',

        ];
    }
}
