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
    $mail = $this->from(\Config::get('client.email.from'), config('app.name'))
                ->subject('Wohnungsangebot '. $this->data->estate->description .' – '. config('app.name'))
                ->with(['collection' => $this->data])
                ->markdown('mails.offer');
    
    // Per apartment its plan, and the furnished one if there is one
    foreach($this->data->items as $item)
    {
      $plan = public_path() . '/assets/media/' . $item->apartment->number . '-' . $item->apartment->uuid;
      $mail->attach($plan . '.pdf', ['mime' => 'application/pdf']);
      if (is_file($plan . '-moebliert.pdf'))
      {
        $mail->attach($plan . '-moebliert.pdf', ['mime' => 'application/pdf']);
      }
    }

    // The estate's documents (e.g. the Ausbaubeschrieb)
    foreach(glob(public_path() . '/assets/media/estates/' . $this->data->estate->domain . '/*.pdf') as $file)
    {
      $mail->attach($file, ['mime' => 'application/pdf']);
    }

    return $mail;


  }
}
