<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EmployeeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new message instance.
     */
    public function __construct($validated)
    {
        $this->user = $validated;
        $this->onQueue('emails');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'LUCAS - ' . $this->user['title'],
            using: [
                function () {
                    Log::channel('user_management')->info('EmployeeMail successfull sent', [
                        'employee_id' => $this->user['employee_id'] ?? null,
                        'email' => $this->user['email'] ?? null
                    ]);
                }
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::channel('user_management')
            ->info('Processing Employee Mail Job', [
                'employee_id' => $this->user['employee_id'] ?? null,
                'email' => $this->user['email'] ?? null,
            ]);

        $loginUrl = route('login');

        return new Content(
            view: 'emails.employeeMail',
            with: [
                'image_banner' => $this->getBase64Image('assets/images/bmi-banner.jpg'),
                'base' => config('app.url'),
                'employee_id' => $this->user['employee_id'],
                'password' => $this->user['raw_password'],
                'title' => $this->user['title'],
                'name' => $this->user['fname'],
                'url' => $loginUrl,
            ]
        );
    }

    private function getBase64Image($imagePath)
    {
        $fullPath = public_path($imagePath);

        if (!file_exists($fullPath)) {
            return null;
        }

        $imageData = base64_encode(file_get_contents($fullPath));
        $imageMime = mime_content_type($fullPath);

        return 'data:' . $imageMime . ';base64,' . $imageData;
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::channel('user_management')->error('Failed to send employee email after retries', [
            'employee_id' => $this->user['employee_id'] ?? null,
            'email' => $this->user['email'] ?? null,
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
