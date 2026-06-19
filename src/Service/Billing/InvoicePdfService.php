<?php

namespace App\Service\Billing;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

final readonly class InvoicePdfService
{
    public function __construct(
        private InvoiceRepository $invoices,
        private EntityManagerInterface $em,
        private Environment $twig,
    ) {
    }

    public function generatePdf(string $invoiceId): string
    {
        $invoice = $this->invoices->find($invoiceId);
        if (!$invoice instanceof Invoice) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Invoice not found');
        }

        $html = $this->renderHtml($invoice);
        $pdfPath = $this->getPdfPath($invoice);

        $dir = dirname($pdfPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->output($pdfPath, \Dompdf\Dompdf::OUTPUT_FILE);

        $invoice->setPdfPath($pdfPath);
        $this->em->flush();

        return $pdfPath;
    }

    public function getPdfPath(Invoice $invoice): string
    {
        $slug = $invoice->getBoutique()->getSlug();
        $number = $invoice->getInvoiceNumber();

        return sprintf(
            '%s/var/invoices/%s/%s.pdf',
            $this->getProjectDir(),
            $slug,
            $number,
        );
    }

    public function getPdfWebPath(Invoice $invoice): ?string
    {
        $path = $invoice->getPdfPath();
        if (null === $path) {
            return null;
        }

        $projectDir = $this->getProjectDir();
        $relativePath = str_replace($projectDir.'/public/', '', $path);

        return '/'.$relativePath;
    }

    private function getProjectDir(): string
    {
        return dirname(__DIR__, 3);
    }

    private function renderHtml(Invoice $invoice): string
    {
        $items = [];
        foreach ($invoice->getItems() as $item) {
            $items[] = [
                'description' => $item->getDescription(),
                'quantity' => $item->getQuantity(),
                'unitPrice' => number_format($item->getUnitPrice() / 100, 2, ',', '.'),
                'total' => number_format($item->getTotal() / 100, 2, ',', '.'),
            ];
        }

        $subtotalFormatted = number_format($invoice->getSubtotal() / 100, 2, ',', '.');
        $discountFormatted = number_format($invoice->getDiscountTotal() / 100, 2, ',', '.');
        $taxFormatted = number_format($invoice->getTaxTotal() / 100, 2, ',', '.');
        $shippingFormatted = number_format($invoice->getShippingTotal() / 100, 2, ',', '.');
        $totalFormatted = number_format($invoice->getTotal() / 100, 2, ',', '.');
        $issuedAt = $invoice->getIssuedAt()->format('d/m/Y');
        $dueDate = $invoice->getDueDate()?->format('d/m/Y') ?? 'N/A';
        $paidAt = $invoice->getPaidAt()?->format('d/m/Y') ?? 'N/A';
        $statusValue = $invoice->getStatus()->value;
        $statusBadge = 'PAID' === $statusValue ? 'paid' : 'pending';
        $paidInfo = 'PAID' === $statusValue ? " - Payee le {$paidAt}" : '';

        return $this->twig->render('invoice/show.html.twig', [
            'invoice' => $invoice,
            'items' => $items,
            'subtotal' => $subtotalFormatted,
            'discount' => $discountFormatted,
            'tax' => $taxFormatted,
            'shipping' => $shippingFormatted,
            'total' => $totalFormatted,
            'issuedAt' => $issuedAt,
            'dueDate' => $dueDate,
            'statusBadge' => $statusBadge,
            'statusValue' => $statusValue,
            'paidInfo' => $paidInfo,
        ]);
    }
}
