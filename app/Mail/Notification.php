<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Collection;

class Notification extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * Create a new message instance.
   *
   * @param Collection $collection
   * @return void
   */
  public function __construct(Collection $collection)
  {
    $this->collection = $collection;
    $this->subject = 'Wohnungsangebot '. $collection->estate->description .' – Dr. Stephan à Porta-Stiftung';
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->from(\Config::get('client.email.from'), 'Dr. Stephan à Porta-Stiftung')
                ->subject($this->subject)
                ->with(['collection' => $this->collection])
                ->markdown('mails.notification');
  }
}
