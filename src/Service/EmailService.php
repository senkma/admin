<?php

namespace App\Service;

use App\Entity\Communication;
use App\Entity\Invoice;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class EmailService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private LoggerInterface $logger;
    private string $fromEmail;
    private string $fromName;

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        LoggerInterface $logger,
        string $fromEmail = 'noreply@example.com',
        string $fromName = 'Fakturační systém'
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    /**
     * Odešle email na základě Communication entity
     */
    public function sendCommunicationEmail(Communication $communication): bool
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to($communication->getEmail())
                ->subject($this->generateSubject($communication))
                ->htmlTemplate('emails/communication.html.twig')
                ->context([
                    'communication' => $communication,
                    'supplier' => $communication->getSupplier(),
                    'client' => $communication->getClient(),
                    'service' => $communication->getService(),
                    'invoice' => $communication->getInvoice(),
                ]);

            // Připojit fakturu jako přílohu, pokud existuje
            if ($communication->getInvoice()) {
                $this->attachInvoicePdf($email, $communication->getInvoice());
            }

            $this->mailer->send($email);

            // Označit jako odesláno
            $communication->setStatus('vykonano');
            $communication->setSentAt(new \DateTime());
            $communication->setErrorMessage(null);

            $this->logger->info('Email byl úspěšně odeslán', [
                'communication_id' => $communication->getId(),
                'email' => $communication->getEmail()
            ]);

            return true;

        } catch (\Exception $e) {
            // Označit jako chybný
            $communication->setStatus('zruseno');
            $communication->setErrorMessage($e->getMessage());

            $this->logger->error('Chyba při odesílání emailu', [
                'communication_id' => $communication->getId(),
                'email' => $communication->getEmail(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Odešle jednoduchý email
     */
    public function sendSimpleEmail(string $to, string $subject, string $message, ?Invoice $invoice = null): bool
    {
        try {
            $email = (new Email())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to($to)
                ->subject($subject)
                ->html($message);

            // Připojit fakturu jako přílohu, pokud existuje
            if ($invoice) {
                $this->attachInvoicePdf($email, $invoice);
            }

            $this->mailer->send($email);

            $this->logger->info('Jednoduchý email byl úspěšně odeslán', [
                'email' => $to,
                'subject' => $subject
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Chyba při odesílání jednoduchého emailu', [
                'email' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Generuje předmět emailu na základě komunikace
     */
    private function generateSubject(Communication $communication): string
    {
        $subject = 'Komunikace';

        if ($communication->getService()) {
            $subject = 'Služba: ' . $communication->getService()->getName();
        } elseif ($communication->getInvoice()) {
            $subject = 'Faktura č. ' . $communication->getInvoice()->getInvoiceNumber();
        } elseif ($communication->getClient()) {
            $subject = 'Komunikace - ' . $communication->getClient()->getName();
        }

        return $subject;
    }

    /**
     * Připojí PDF fakturu jako přílohu
     */
    private function attachInvoicePdf(Email $email, Invoice $invoice): void
    {
        // TODO: Implementovat generování PDF faktury
        // Pro nyní pouze přidáme komentář
        // $pdfContent = $this->generateInvoicePdf($invoice);
        // $email->attach($pdfContent, 'faktura_' . $invoice->getInvoiceNumber() . '.pdf', 'application/pdf');
    }

    /**
     * Testuje připojení k SMTP serveru
     */
    public function testConnection(): bool
    {
        try {
            $email = (new Email())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to($this->fromEmail)
                ->subject('Test připojení')
                ->text('Toto je testovací email pro ověření připojení k SMTP serveru.');

            $this->mailer->send($email);
            return true;

        } catch (\Exception $e) {
            $this->logger->error('Chyba při testování SMTP připojení', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
