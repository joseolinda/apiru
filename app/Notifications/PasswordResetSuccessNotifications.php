<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
class PasswordResetSuccessNotifications extends Notification implements ShouldQueue
{
    use Queueable;
    protected $user;

    /**
    * Create a new notification instance.
    *
    * @return void
    */
    public function __construct($user)
    {
        $this->user = $user;
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
        $url = "https://ru.cedro.ifce.edu.br";
        return (new MailMessage)
            ->subject('Senha recuperada')
            ->greeting('Olá, '.$this->user->name.'.')
            ->line('Você mudou sua senha com sucesso.')
            ->line('Se você alterou a senha, nenhuma ação adicional será necessária.')
            ->line('Se você não alterou a senha, proteja sua conta.')
            ->action('Acesse o Sistema', url($url));;
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
