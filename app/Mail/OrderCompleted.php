<?php

namespace App\Mail;

use DB;
use App\Checkout;
use App\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderCompleted extends Mailable
{
    use Queueable, SerializesModels;


    private function loadCheckout($checkoutId)
    {
        $checkout = array();
        if(@$checkoutId)
        {
            $checkout = DB::table('checkouts')->where(['id' => $checkoutId])->first();
            $checkout->products = DB::table('checkouts_items')
                ->where(['checkoutId' => $checkoutId])
                ->leftJoin('products', 'checkouts_items.productId', '=', 'products.id')
                ->get();
        }
        return $checkout;
    }
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($checkoutId)
    {
        $this->checkout = $this->loadCheckout($checkoutId);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('support@perfectpair.nz')
        ->view('emails.orderCompleted')
        ->with([
            'checkout' => $this->checkout
        ]);
    }
}
