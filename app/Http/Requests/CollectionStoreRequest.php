<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CollectionStoreRequest extends FormRequest
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
      'candidates.*.firstname' => 'required',
      'candidates.*.name' => 'required',
      'candidates.*.email' => 'required|email',
      'items' => 'required|array|min:1'
    ];
  }

  /**
   * Custom message for validation
   *
   * @return array
   */


  public function messages()
  {
    return [];
  }
}