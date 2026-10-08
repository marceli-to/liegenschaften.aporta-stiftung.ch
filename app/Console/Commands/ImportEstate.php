<?php
namespace App\Console\Commands;
use App\Models\Apartment;
use App\Models\Building;
use App\Models\Estate;
use App\Models\Floor;
use App\Models\Room;
use App\Models\State;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Loads an estate from database/data/{estate}.json (made by tools/koro/build.php).
 *
 * Safe to run again: estate and apartments are matched by uuid, buildings by
 * estate + description, floors and rooms by abbreviation. On existing
 * apartments only the plan data changes (number, location, sizes, order);
 * rents, state, tenant and availability belong to the admin and stay.
 */

class ImportEstate extends Command
{
  protected $signature = 'estate:import {file : JSON file, e.g. database/data/kornhaus-roetelstrasse.json}';

  protected $description = 'Create or update an estate with its buildings and apartments';

  public function handle()
  {
    $data = json_decode(file_get_contents($this->argument('file')), true, flags: JSON_THROW_ON_ERROR);

    DB::transaction(function () use ($data) {
      $estate = Estate::withTrashed()->updateOrCreate(['uuid' => $data['estate']['uuid']], $data['estate'] + ['publish' => 1]);

      $floors = [];
      foreach ($data['floors'] as $f) {
        $floors[$f['abbreviation']] = Floor::firstOrCreate(['abbreviation' => $f['abbreviation']], $f + ['publish' => 1]);
      }

      $rooms = [];
      foreach ($data['rooms'] as $abbreviation) {
        $rooms[$abbreviation] = Room::firstOrCreate(['abbreviation' => $abbreviation], ['description' => "{$abbreviation}-Zimmerwohnung", 'order' => -1, 'publish' => 1]);
      }

      $buildings = [];
      foreach ($data['buildings'] as $b) {
        $buildings[$b['description']] = Building::updateOrCreate(['estate_id' => $estate->id, 'description' => $b['description']], $b + ['publish' => 1]);
      }

      // Only the floors and rooms the estate uses (its filters list them)
      $used = collect($data['apartments']);
      $estate->floors()->syncWithoutDetaching($used->pluck('floor')->unique()->map(fn($f) => $floors[$f]->id));
      $estate->rooms()->syncWithoutDetaching($used->pluck('room')->unique()->map(fn($r) => $rooms[$r]->id));

      // Sizes as the column stores them, so an unchanged one isn't dirty
      $size = fn($v) => number_format($v, 1, '.', '');
      $created = 0;
      foreach ($data['apartments'] as $a) {
        $plan = [
          'number' => $a['number'],
          'description' => $a['description'],
          'size' => $size($a['size']),
          'size_terrace' => $size($a['size_terrace']),
          'size_patio' => $size($a['size_patio']),
          'size_balcony' => $size($a['size_balcony']),
          'size_loggia' => $size($a['size_loggia']),
          'order' => $a['order'],
          'estate_id' => $estate->id,
          'building_id' => $buildings[$a['building']]->id,
          'floor_id' => $floors[$a['floor']]->id,
          'room_id' => $rooms[$a['room']]->id,
        ];
        $apartment = Apartment::withTrashed()->where('uuid', $a['uuid'])->first();
        if ($apartment) {
          $apartment->update($plan);
          continue;
        }
        Apartment::create($plan + [
          'uuid' => $a['uuid'],
          'state_id' => State::FREE,
          'publish' => 1,
          'rent_net' => $a['rent_net'],
          'additional_cost' => $a['additional_cost'],
          'rent_gross' => $a['rent_gross'],
        ]);
        $created++;
      }

      $this->info("{$estate->description}: " . count($data['apartments']) . " apartments ({$created} new), " . count($buildings) . ' buildings');
    });
  }
}
