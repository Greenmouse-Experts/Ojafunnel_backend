<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FailedLoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $name;
    public $date;
    public $request;
    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $appName;

    /**
     * Create a new notification instance.
     *
     * @param $name
     * @param $date
     * @param $request
     */
    public function __construct($name, $date, $request)
    {
        //
        $this->name = $name;
        $this->date = $date;
        $this->request = $request;
        $this->appName = config('app.name');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("$this->appName - Failed Login Notification")
            ->markdown('mails.failed_login_notification', [
                'name' => $this->name,
                'date' => $this->date,
                'ip' => $this->request['ip'],
                'channel' => $this->request['is_mobile'] ? 'Mobile' : 'Web',
                'os' => $this->request['os'] ?? '',
                'browserName' => $this->request['browser_name'] ?? '',
                'version' => $this->request['version'] ?? ''
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
