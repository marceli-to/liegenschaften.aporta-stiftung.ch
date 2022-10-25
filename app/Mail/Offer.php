<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Offer extends Mailable
{
  use Queueable, SerializesModels;

  protected $data;

  /**
   * Create a new message instance.
   *
   * @param $data
   * @return void
   */
  public function __construct($data)
  {
    $this->data = $data;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    $mail = $this->from(\Config::get('client.email.from'), env('APP_NAME'))
                ->subject('Wohnungsangebot '. $this->data->estate->description .' – '. env('APP_NAME'))
                ->with(['collection' => $this->data])
                ->markdown('mails.offer');
    
    foreach($this->data->items as $item)
    {
      $mail->attach(
        public_path() . '/assets/media/' . $item->apartment->number . '-' . $item->apartment->uuid . '.pdf',
        ['mime' => 'application/pdf']
      );
    }

    return $mail;


  }
}
