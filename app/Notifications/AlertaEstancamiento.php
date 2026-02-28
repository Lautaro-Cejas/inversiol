<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AlertaEstancamiento extends Notification
{
    use Queueable;

    public $inversion;
    public $dias;
    public $rendimiento;

    /**
     * Create a new notification instance.
     */
    public function __construct($inversion, $dias, $rendimiento)
    {
        $this->inversion = $inversion;
        $this->dias = $dias;
        $this->rendimiento = $rendimiento;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $signo = $this->rendimiento > 0 ? '+' : '';
        $porcentaje = number_format($this->rendimiento, 2);

        return (new MailMessage)
            ->subject('⚠️ Costo de Oportunidad: ' . $this->inversion->activo . ' estancado')
            ->greeting('¡Hola Lauti!')
            ->line("El bot detectó que tu inversión en **{$this->inversion->activo}** lleva **{$this->dias} días** en tu cartera sin movimientos significativos.")
            ->line("Rendimiento actual: **{$signo}{$porcentaje}%**")
            ->line("Tener capital inmovilizado genera un costo de oportunidad en mercados volátiles. Considerá si conviene vender para rotar este dinero hacia una nueva 'Oportunidad' que tengas pendiente.")
            ->action('Ver Panel InversIOL', url('/admin/inversions'))
            ->line('¡Que el mercado esté a tu favor!');
    }
}