<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StockExceeded extends Mailable
{
    use Queueable, SerializesModels;

    public $item;
    public $requestedQuantity;

    public function __construct($item, $requestedQuantity)
    {
        $this->item = $item;
        $this->requestedQuantity = $requestedQuantity;
    }

    public function build()
    {
        return $this->subject('Demanda alta de productos')
                    ->view('emails.stock_exceeded')
                    ->with([
                        'item' => $this->item,
                        'requestedQuantity' => $this->requestedQuantity,
                    ]);
    }
}