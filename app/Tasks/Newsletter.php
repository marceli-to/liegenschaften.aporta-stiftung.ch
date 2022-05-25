<?php
namespace App\Tasks;
use App\Models\NewsletterQueue;

class Newsletter
{
  public function __invoke()
  {
    $subscribers = NewsletterQueue::with('subscriber', 'newsletter')->get();
    $subscribers = collect($subscribers)->splice(0, \Config::get('client.cron_chunk_size'));

    foreach($subscribers->all() as $s)
    {
      try {
        \Mail::to($s->subscriber->email)->send(new \App\Mail\Newsletter($s->newsletter, $s->subscriber->uuid));
        $s->state = 'processed';
        $s->save();
        $s->delete();

        // update newsletter state
        if ($s->newsletter->state == 'queued' && NewsletterQueue::with('subscriber', 'newsletter')->where('newsletter_id', $s->newsletter->id)->count() == 0)
        {
          $s->newsletter->state = 'processed';
          $s->newsletter->save();
        }

      } 
      catch(\Throwable $e) {
        $s->state = 'failed';
        $s->save();
        $s->delete();
        \Log::error($e);
      }
    }
  }
}