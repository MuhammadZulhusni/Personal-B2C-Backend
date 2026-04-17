<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Order #' . $this->order->id . ' - Mini Shop',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-order',
            with: [
                'order' => $this->order,
                'orderItems' => $this->order->items()->with('product')->get(),
                'customer' => $this->order->user,
                'adminUrl' => config('app.frontend_url') . '/admin/orders',
            ],
        );
    }
}