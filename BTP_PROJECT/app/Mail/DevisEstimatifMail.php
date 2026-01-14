<?php

namespace App\Mail;

use App\Models\DevisEstimatif;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class DevisEstimatifMail extends Mailable
{
    use Queueable, SerializesModels;

    public $devis;
    public $messagePersonnalise;

    /**
     * Create a new message instance.
     */
    public function __construct(DevisEstimatif $devis, $messagePersonnalise = null)
    {
        $this->devis = $devis->load([
            'batiment.projet.organisation',
            'composants.niveau',
            'composants.unite',
        ]);

        $this->messagePersonnalise = $messagePersonnalise;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Devis Estimatif - " . $this->devis->code . " - " . $this->devis->batiment->projet->nom,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $totalGeneral = $this->devis->total();
        $totauxParNiveau = $this->devis->totalParNiveau()->map(fn($row) => [
            'niveau_nom' => $row->niveau->nom,
            'total' => $row->total,
        ])->values();

        $devise = $this->devis->batiment->projet->devise['libelle'] ?? 'MAD';

        return new Content(
            view: 'DevisEstimatif.email',
            with: [
                'devis' => $this->devis,
                'totalGeneral' => $totalGeneral,
                'totauxParNiveau' => $totauxParNiveau,
                'devise' => $devise,
                'messagePersonnalise' => $this->messagePersonnalise,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        // Générer le PDF
        $totalGeneral = $this->devis->total();
        $totauxParNiveau = $this->devis->totalParNiveau()->map(fn($row) => [
            'niveau_nom' => $row->niveau->nom,
            'total' => $row->total,
        ])->values();

        $devise = $this->devis->batiment->projet->devise['libelle'] ?? 'MAD';

        $pdf = PDF::loadView('DevisEstimatif.pdf', [
            'devis' => $this->devis,
            'totalGeneral' => $totalGeneral,
            'totauxParNiveau' => $totauxParNiveau,
            'devise' => $devise,
        ]);


        $filename = "devis-{$this->devis->code}.pdf";

        return [
            Attachment::fromData(fn() => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}