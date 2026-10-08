<?php
namespace App\Exports;
use App\Models\Apartment;
use App\Support\CurrentEstate;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ApartmentExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
{
/**
   * @return \Illuminate\Support\Collection
   */
  public function collection(): Collection
  {
    $apartments = Apartment::with('building', 'floor', 'room', 'tenant', 'collectionItems', 'state')->orderBy('order', 'DESC')->where('estate_id', app(CurrentEstate::class)->id())->get();
    $apartments->sortBy('building.order');
    
    $data = [];
    foreach($apartments as $apartment)
    {
      $row = [
        'Adresse' => $apartment->building->street,
        'Lage' => $apartment->description,
        'Nummer' => $apartment->number,
        'Mietzins' => $apartment->rent_gross,
        'Zimmer' => $apartment->room->abbreviation, 
        'M2' => $apartment->size,
      ];
      foreach($this->exteriors() as $key => $label)
      {
        $row[$label] = $apartment->{'size_' . $key};
      }
      $data[] = $row + [
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
      ...array_values($this->exteriors()),
      'Status',
      'Mieter'
    ];
  }

  /**
   * The estate's exteriors (key => label), one column each
   *
   * @return array
   */
  protected function exteriors()
  {
    return app(CurrentEstate::class)->setting('exteriors');
  }

  /**
   * @return array
   */
  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function(AfterSheet $event) {
        $cellRange = 'A1:' . Coordinate::stringFromColumnIndex(count($this->headings())) . '1';
        $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
      },
    ];
  }
}
