<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Apartment;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
  /**
   * Get a list of tenants
   * 
   * @return \Illuminate\Http\Response
   */
  public function get($searchTerm = NULL)
  { 
    if ($searchTerm)
    {
      $data = Tenant::with('apartment.room', 'apartment.floor', 'apartment.building')->whereHas('apartment')->where(function($query) use ($searchTerm) {
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
      $data = Tenant::with('apartment.room', 'apartment.floor', 'apartment.building')->whereHas('apartment')->orderBy('name')->get();
    }
    $data = $data->sortByDesc('apartment.floor.order')->sortBy('apartment.building.order');
    return new DataCollection($data);
  }

}
