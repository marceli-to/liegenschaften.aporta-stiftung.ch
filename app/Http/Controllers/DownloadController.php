<?php
namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;
use App\Exports\ApartmentExport;
use App\Exports\TenantExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class DownloadController extends BaseController
{

  protected $headers = [
    'Content-Type: application/pdf',
    'Cache-Control: no-store, no-cache, must-revalidate',
    'Expires: Sun, 01 Jan 2014 00:00:00 GMT',
    'Pragma: no-cache'
  ];

  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Export Applications to Excel
   * 
   * @return \Illuminate\Http\Response
   */

  public function exportApartments()
  {
    $filename = 'liegenschaft-eglistrasse-objekte' . date('d-m-Y-H:i:s') . '.xlsx';
    return Excel::download(new ApartmentExport, $filename);
  }

  /**
   * Export Tenants to Excel
   * 
   * @return \Illuminate\Http\Response
   */

   public function exportTenants()
   {
     $filename = 'liegenschaft-eglistrasse-mieter' . date('d-m-Y-H:i:s') . '.xlsx';
     return Excel::download(new TenantExport, $filename);
   }

}
