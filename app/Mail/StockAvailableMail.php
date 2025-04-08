<?php

namespace App\Mail;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockAvailableMail extends Mailable implements ShouldQueue
{
    
    use Queueable, SerializesModels;

    public $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function build()
    {
        return $this->subject('¡Producto disponible!')
                    ->view('emails.stock_available');
    }
}