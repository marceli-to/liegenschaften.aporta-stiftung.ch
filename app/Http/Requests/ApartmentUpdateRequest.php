<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class ApartmentUpdateRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'rent_gross' => 'required',
      'rent_net' => 'required',
      'additional_cost' => 'required',
    ];
  }

  /**
   * Custom message for validation
   *
   * @return array
   */


  public function messages()
  {
    return [
      'rent_gross.required' => [
        'field' => 'name',
        'error' => 'Mietzins Brutto wird benötigt!'
      ],
      'rent_net.required' => [
        'field' => 'name',
        'error' => 'Mietzins Netto wird benötigt!'
      ],
      'additional_cost.required' => [
        'field' => 'name',
        'error' => 'Nebenkosten werden benötigt!'
      ],
    ];
  }
}