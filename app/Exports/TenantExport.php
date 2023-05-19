<?php
namespace App\Exports;
use App\Models\Tenant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class TenantExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
{
/**
   * @return \Illuminate\Support\Collection
   */
  public function collection()
  {
    $tenants = Tenant::with('apartment.room', 'apartment.floor', 'apartment.building')->whereHas('apartment')->get();
    
    $data = [];
    foreach($tenants as $tenant)
    {
      $data[] = [
        'Vorname' => $tenant->firstname,
        'Name' => $tenant->name,
        'Telefon' => $tenant->phone,
        'E-Mail' => $tenant->email,
        'Adresse' => $tenant->apartment->building->street, 
        'Wohnung' => $tenant->apartment->number . ' / ' . $tenant->apartment->description
      ];
    }
    return collect($data);
  }

  public function headings(): array
  {
    return [
      'Vorname',
      'Name',
      'Telefon',
      'E-Mail',
      'Adresse', 
      'Wohnung',
    ];
  }

  /**
   * @return array
   */
  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function(AfterSheet $event) {
        $cellRange = 'A1:F1';
        $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
      },
    ];
  }
}
