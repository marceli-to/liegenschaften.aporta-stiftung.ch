<?php
namespace App\Exports;
use App\Models\Apartment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;


class ApartmentExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
{
/**
   * @return \Illuminate\Support\Collection
   */
  public function collection()
  {
    $apartments = Apartment::with('building', 'floor', 'room', 'tenant', 'collectionItems', 'state')->orderBy('order', 'DESC')->where('estate_id', env('ESTATE_ID'))->get();
    $apartments->sortBy('building.order');
    
    $data = [];
    foreach($apartments as $apartment)
    {
      $data[] = [
        'Adresse' => $apartment->building->street,
        'Lage' => $apartment->description,
        'Nummer' => $apartment->number,
        'Mietzins' => $apartment->rent_gross,
        'Zimmer' => $apartment->room->abbreviation, 
        'M2' => $apartment->size,
        'Terrasse' => $apartment->size_terrace,
        'Sitzplatz' => $apartment->size_patio,
        'Balkon' => $apartment->size_balcony,
        'Status' => $apartment->state->description,
        'Mieter' => $apartment->tenant ? $apartment->tenant->full_name : ''
      ];
    }
    return collect($data);

  }

  public function headings(): array
  {
    return [
      'Adresse',
      'Lage',
      'Nummer',
      'Mietzins',
      'Zimmer', 
      'M2',
      'Terrasse',
      'Sitzplatz',
      'Balkon',
      'Status',
      'Mieter'
    ];
  }

  /**
   * @return array
   */
  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function(AfterSheet $event) {
        $cellRange = 'A1:K1';
        $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
      },
    ];
  }
}
