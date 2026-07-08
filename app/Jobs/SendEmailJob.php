<?php

namespace App\Jobs;

use App\Services\GmailMailerService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string|array $recipients;
    protected string $from_email;
    protected string $from_name;
    protected string $subject;
    protected string $view;
    protected array $data;
    protected mixed $others;
    protected string $attachment;
    protected bool $delete_attachment_on_sent;

    public $tries = 3;
    public $backoff = 60; // segundos

    /**
     * Create a new job instance.
     */
    public function __construct(
        string|array $recipients,
        string $from_email = '',
        string $from_name = '',
        string $subject,
        string $view,
        array $data = [],
        mixed $others = null,
        string $attachment = '',
        bool $delete_attachment_on_sent = false
    ) {
        $this->recipients = $recipients;
        $this->from_email = $from_email;
        $this->from_name = $from_name;
        $this->subject = $subject;
        $this->view = $view;
        $this->data = $data;
        $this->others = $others;
        $this->attachment = $attachment;
        $this->delete_attachment_on_sent = $delete_attachment_on_sent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $html = view($this->view, $this->data)->render();

        try {
            $mailer = new GmailMailerService();
            $mailer->send(
                $this->recipients,
                $this->subject,
                $html,
                $this->from_email,
                $this->from_name,
                $this->others,
                $this->attachment
            );
        } catch (\Throwable $e) {
            logger()->error('❌ Error al enviar correo: ' . $e->getMessage());
            throw $e;
        }

        if ($this->attachment && $this->delete_attachment_on_sent && file_exists($this->attachment)) {
            logger()->error("Entro a borrar el archivo");
            unlink($this->attachment);
        }
    }

    public function retryUntil()
    {
        return now()->addMinutes(5);
    }
}
