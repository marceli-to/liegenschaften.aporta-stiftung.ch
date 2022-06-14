<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Collection;

class Offer extends Mailable
{
  use Queueable, SerializesModels;

  protected $collection;

  /**
   * Create a new message instance.
   *
   * @param Collection $collection
   * @return void
   */
  public function __construct(Collection $collection)
  {
    $this->collection = $collection;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->from(\Config::get('client.email.from'), 'Dr. Stephan à Porta-Stiftung')
                ->subject('Wohnungsangebot '. $this->collection->estate->description .' – Dr. Stephan à Porta-Stiftung')
                ->with(['collection' => $this->collection])
                ->markdown('mails.offer');
  }
}
