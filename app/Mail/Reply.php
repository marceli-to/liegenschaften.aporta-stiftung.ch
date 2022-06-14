<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ReplyQueue;

class Reply extends Mailable
{
  use Queueable, SerializesModels;

  protected $reply;

  /**
   * Create a new message instance.
   *
   * @param Collection $collection
   * @return void
   */
  public function __construct(ReplyQueue $reply)
  {
    $this->reply = $reply;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->from(\Config::get('client.email.from'), 'Dr. Stephan à Porta-Stiftung')
                ->subject('Antwort Wohnungsangebot '. $this->reply->item->collection->estate->description .' – Dr. Stephan à Porta-Stiftung')
                ->with(['item' => $this->reply->item])
                ->markdown('mails.reply');
  }
}
