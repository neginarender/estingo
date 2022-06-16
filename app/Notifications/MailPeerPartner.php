<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class MailPeerPartner extends Notification
{
    use Queueable;

    protected $arr;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $arr)
    {
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
        $detail = $this->arr;
        if($detail['status']=='1' || $detail['status']==1)
        {
            return (new MailMessage)
                    ->greeting('Congratulations!')
                    ->line('You are now a Peer Partner with rozana.in')
                    ->line("Welcome to Team Rozana! We are delighted to have you with us. You're going to be a valuable asset for us and we can't wait to see what we can accomplish together!")
                    ->line(new HtmlString("Here's your unique code: <strong>".$detail['peercode']."</strong>"))
                    ->line('You can refer this code amongst your friends, family and business associates. Each time someone makes a purchase using your code, you will earn points which will eventually convert into monetary return from Rozana.in')
                    ->line('Look forward to working with you.');
        }

        return (new MailMessage)
                    ->greeting('Hi!')
                    ->line("We really appreciate that you took the time to consider coming onboard with rozana.in as a Peer Partner. However, we don't have an opening at present. We hope that you don't mind if we reach out to you in the future. Thank you for your interest in us!");
                   
        
        
                    
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
