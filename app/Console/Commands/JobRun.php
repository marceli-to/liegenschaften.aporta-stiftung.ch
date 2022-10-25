<?php
namespace App\Console\Commands;
use App\Tasks\Notification;
use App\Models\MailQueue;
use Illuminate\Console\Command;

class JobRun extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'job:run {--latest}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Run the job task';

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
    $this->info('Starting jobs...');

    $latest = $this->option('latest');

    if ($latest)
    {
      $latest = MailQueue::processed()->latest()->first();
      $latest->processed = 0;
      $latest->save();
      $this->info('The reset command was successful!');
    }

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

    $this->info('The queue has been cleared!');

    return; 
  }
}
