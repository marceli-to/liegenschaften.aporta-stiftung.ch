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
  public function get()
  { 
    // Get tenants with apartments where the apartment is not null
    $data = Tenant::with('apartment.room', 'apartment.floor', 'apartment.building')->whereHas('apartment')->get();
    return new DataCollection($data);
  }

}
