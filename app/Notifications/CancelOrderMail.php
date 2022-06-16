<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CancelOrderMail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $arr;

    public function __construct(array $arr) {
            $this->arr = $arr;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $order = $this->arr;
        $ordercode = $order['ordercode'];
        $name = $order['name'];
        return (new MailMessage)
                    ->subject('Order Cancelled- Rozana')
                    ->greeting("Dear ".$name)
                    ->line("We're writing to let you know that we have processed your request to cancel your order ".$ordercode.". If you have paid online your total payment will be credited to your source account in 5-7 working days. You will not be charged for the cancelled items at any stage. 
                    
                    Thank you for shopping with Rozana.in 
                    
                    We look forward to serving you again.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
