<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use App\User;

class EmailVerify extends Notification
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
        $userid = User::where('email', $detail['email'])->select('id')->first();
        $base_url  = "https://www.rozana.in";
        // $url = $base_url."/users/verify_reg/". encrypt($userid->id);
        $url = $base_url."/api/v5/auth/users/verify_reg/". encrypt($userid->id);
        if($detail['email']!='')
        {
            return (new MailMessage)
                    ->greeting('Congratulations!')
                    ->line('You are successfully registered with rozana.in')
                    ->line("Welcome to Team Rozana! We are delighted to have you with us!")
                    ->line("Here's your verification link:")
                    ->line(new HtmlString("<a href=".$url."><button>Verify Email</button></a>"))
                    ->line('Please click above link to verify your account.')
                    ->line('Look forward with you.');
        }

      
                   
        
        
                    
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
