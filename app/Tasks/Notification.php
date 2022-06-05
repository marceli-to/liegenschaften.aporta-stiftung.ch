<?php
namespace App\Tasks;

class Notification
{
  public function __invoke()
  {
    $collection = \App\Models\Collection::with('estate', 'items.apartment.room', 'items.apartment.floor', 'items.apartment.building')->whereNull('sent_at')->get();
    $collection = collect($collection)->splice(0, 1);
    foreach($collection->all() as $c)
    {
      try {
        \Mail::to($c->email)->send(new \App\Mail\Notification($c));
        $c->sent_at = \Carbon\Carbon::now();
        $c->save();
      } 
      catch(\Throwable $e) {
        $c->sent_at = \Carbon\Carbon::now();
        $c->error = $e;
        $c->save();
        \Log::error($e);
      }
    }
  }
}