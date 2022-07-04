<?php
namespace App\Tasks;

class Notification
{
  public function __invoke()
  {
    $mails = \App\Models\MailQueue::unprocessed()->get();
    $mails = collect($mails)->splice(0,1);

    foreach($mails->all() as $m)
    {
      $data = json_decode($m->data);
      try {

        // Reply
        if ($m->type == 'reply') {
          \Mail::to(env('APORTA_REPLY_TO'))->send(new \App\Mail\Reply($data));
          $m->processed = 1;
          $m->save();
        }

        // Confirmation
        if ($m->type == 'confirmation') {
          \Mail::to($data->collection->email)->send(new \App\Mail\Confirmation($data));
          $m->processed = 1;
          $m->save();
        }

        // Offer
        if ($m->type == 'offer') {
          \Mail::to($data->email)->send(new \App\Mail\Offer($data));
          $m->processed = 1;
          $m->save();
  
          foreach($data->items as $i)
          {
            $item = \App\Models\CollectionItem::find($i->id);
            $item->sent_at = \Carbon\Carbon::now();
            $item->save();
          }
        }
      } 
      catch(\Throwable $e) {
        $m->processed = 1;
        $m->error = $e;
        $m->save();
        \Log::error($e);
      }
    }  
  }
}