<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PasswordChangeNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
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
                    Log::channel('user_management')->info('Password Change Notification Mail sent successfully', [
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
            ->info('Processing Password Change Notification Mail Job', [
                'employee_id' => $this->user['employee_id'] ?? null,
                'email' => $this->user['email'] ?? null,
            ]);

        $loginUrl = route('login');

        return new Content(
            view: 'emails.passwordChangeNotificationMail',
            with: [
                'image_banner' => $this->getBase64Image('assets/images/bmi-banner.jpg'),
                'base' => config('app.url'),
                'employee_id' => $this->user['employee_id'],
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
        Log::channel('user_management')->error('Failed to send password change notification email after retries', [
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
