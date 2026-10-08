<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\CurrentEstate;

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
      'items' => 'required|array|min:1',
      'items.*' => Rule::exists('apartments', 'uuid')->where('estate_id', $this->estateId()),
    ];
  }

  /**
   * The offer's estate: the edited offer's, else the current one
   *
   * @return int
   */
  public function estateId()
  {
    return $this->route('collection')?->estate_id ?? app(CurrentEstate::class)->id();
  }

  /**
   * Custom message for validation
   *
   * @return array
   */


  public function messages()
  {
    return [
      'items.*.exists' => 'Die Objekte gehören nicht zur gewählten Liegenschaft. Bitte die Seite neu laden.',
    ];
  }
}