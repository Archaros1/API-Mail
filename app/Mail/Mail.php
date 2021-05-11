<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Mail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /** @var object */
    public $data;

    /**
     * Create a new message instance.
     *
     * @param array|object $data
     *
     * @return void
     */
    public function __construct($data)
    {
        if (\is_array($data)) {
            $data = (object) $data;
        }
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = $this->data;

        return $this->from($data->from)
                    ->subject($data->subject)
                    ->html($data->html);
    }
}
