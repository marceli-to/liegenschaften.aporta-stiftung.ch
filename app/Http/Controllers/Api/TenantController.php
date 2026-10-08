<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Apartment;
use App\Models\Tenant;
use App\Support\CurrentEstate;
use Illuminate\Http\Request;

class TenantController extends Controller
{
  /**
   * Get a list of the current estate's tenants
   * 
   * @param CurrentEstate $estate
   * @param string|null $searchTerm
   * @return \Illuminate\Http\Response
   */
  public function get(CurrentEstate $estate, $searchTerm = NULL)
  { 
    if ($searchTerm)
    {
      $data = Tenant::with('apartment.room', 'apartment.floor', 'apartment.building')->whereRelation('apartment', 'estate_id', $estate->id())->where(function($query) use ($searchTerm) {
        $query->where('name', 'LIKE', "%{$searchTerm}%")
          ->orWhere('firstname', 'LIKE', "%{$searchTerm}%")
          ->orWhere('email', 'LIKE', "%{$searchTerm}%")
          ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
          ->orWhereHas('apartment.building', function($query) use ($searchTerm) {
            $query->where('street', 'LIKE', "%{$searchTerm}%");
          });
      })->orderBy('name')->get();
    }
    else
    {
      $data = Tenant::with('apartment.room', 'apartment.floor', 'apartment.building')->whereRelation('apartment', 'estate_id', $estate->id())->orderBy('name')->get();
    }
    $data = $data->sortByDesc('apartment.floor.order')->sortBy('apartment.building.order');
    return new DataCollection($data);
  }

}
