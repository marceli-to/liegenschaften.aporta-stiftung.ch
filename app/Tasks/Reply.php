<?php
namespace App\Tasks;

class Reply
{
  public function __invoke()
  {
    $replies = \App\Models\ReplyQueue::with('item.collection.estate', 'item.apartment')->unprocessed()->get();
    $replies = collect($replies)->splice(0, 1);
    foreach($replies->all() as $r)
    {
      try {
        \Mail::to($r->email)->send(new \App\Mail\Reply($r));
        $r->processed = 1;
        $r->save();
      } 
      catch(\Throwable $e) {
        $r->processed = 1;
        $r->error = $e;
        $r->save();
        \Log::error($e);
      }
    }
  }
}