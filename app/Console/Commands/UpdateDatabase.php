<?php
namespace App\Console\Commands;
use App\Models\Apartment;
use App\Models\Tenant;
use Illuminate\Console\Command;

class UpdateDatabase extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'update:database';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Update database tables';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    // 1) Get all apartements and update
    // - rent_net
    // - additional_cost
    // - rent_gross
    $apartments = Apartment::get();

    foreach($apartments as $a)
    {
      $a->rent_net = 0;
      $a->additional_cost = 0;
      $a->rent_gross = 0;
      $a->save();
    }

    // 2) Get all tenants and update
    // - name
    // - firstname

    $firstnames = ["Anna", "Hans", "Maria", "Max", "Sophie", "Jakob", "Lena", "Markus", "Lea", "Paul", "Nina", "Sebastian", "Lisa", "Alexander", "Julia", "Michael", "Lena", "David", "Marie", "Andreas"];

    $lastnames = ["Müller", "Schmidt", "Schneider", "Mustermann", "Meyer", "Schmidt", "Bauer", "Weber", "Schmidt", "Müller", "Mayer", "Schulz", "Schmidt", "Bauer", "Schmidt", "Weber", "Bauer", "Schmidt", "Schneider", "Mayer"];

    $tenants = Tenant::get();
    foreach($tenants as $t)
    {
      $rand1 = mt_rand(0,19);
      $rand2 = mt_rand(0,19);
      $t->name = $firstnames[$rand1];
      $t->firstname = $lastnames[$rand2];
      $t->email = $firstnames[$rand1].'.'.$lastnames[$rand2].'@testmail.ch';
      $t->save();
    }
    return; 
  }
}
