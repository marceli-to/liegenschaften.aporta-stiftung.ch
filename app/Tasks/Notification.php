<?php
namespace App\Tasks;

class Notification
{
  public function __invoke()
  {
    $collection = \App\Models\Collection::with('estate', 'items.apartment.room', 'items.apartment.floor', 'items.apartment.building')->where('processed', 0)->get();
    $collection = collect($collection)->splice(0, 1);
    foreach($collection->all() as $c)
    {
      try {
        \Mail::to($c->email)->send(new \App\Mail\Notification($c));
        $c->processed = 1;
        $c->save();

        foreach($c->items as $item)
        {
          $item->sent_at = \Carbon\Carbon::now();
          $item->save();
        }
      } 
      catch(\Throwable $e) {
        $c->processed = 1;
        $c->error = $e;
        $c->save();
        \Log::error($e);
      }
    }
  }
}