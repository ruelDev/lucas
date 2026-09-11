<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Carbon\Carbon;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;


class AccountExpiryMail extends Mailable
{
    use Queueable;
    
    public string $recipientName;
    public string $subjectMessage;
    public string $expiryType;
    public Carbon $expiryDate;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $recipientName,
        string $subjectMessage,
        string $expiryType,
        Carbon $expiryDate
    )
    {
        $this->recipientName = $recipientName;
        $this->subjectMessage = $subjectMessage;
        $this->expiryType = $expiryType;
        $this->expiryDate = $expiryDate;
        $this->onQueue('emails');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'LUCAS - ' . $this->subjectMessage,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $dateToday = Carbon::now()->startOfDay();
        $daysRemaining = $dateToday->diffInDays($this->expiryDate->copy()->startOfDay(), false);

        $formattedDays = abs($daysRemaining) . ' day' . (abs($daysRemaining) == 1 ? '' : 's');

        return new Content(
            view: 'emails.accountExpiryMail',
            with: [
                'image_banner' => $this->getBase64Image('assets/images/bmi-banner.jpg'),
                'name' => $this->recipientName,
                'days' => $formattedDays,
                'title' => $this->subjectMessage,
                'expiryType' => $this->expiryType,
                'url' => route('password.edit'),
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

        return 'data:' .$imageMime . ';base64,' . $imageData;
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
