<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Service\Invoice\Renderer;

use App\Crm\Application\Export\Base\DispositionInlineInterface;
use App\Crm\Application\Model\InvoiceDocument;
use App\Crm\Application\Service\Invoice\InvoiceFilename;
use App\Crm\Application\Service\Invoice\InvoiceModel;
use App\Crm\Application\Service\Pdf\HtmlToPdfConverter;
use App\Crm\Application\Service\Pdf\PdfContext;
use App\Crm\Application\Service\Pdf\PdfRendererTrait;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class PdfRenderer extends AbstractTwigRenderer implements DispositionInlineInterface
{
    use PDFRendererTrait;

    public function __construct(Environment $twig, private HtmlToPdfConverter $converter)
    {
        parent::__construct($twig);
    }

    public function supports(InvoiceDocument $document): bool
    {
        return stripos($document->getFilename(), '.pdf.twig') !== false;
    }

    public function render(InvoiceDocument $document, InvoiceModel $model): Response
    {
        $filename = new InvoiceFilename($model);

        $context = new PdfContext();
        $context->setOption('filename', $filename->getFilename());
        $context->setOption('setAutoTopMargin', 'pad');
        $context->setOption('setAutoBottomMargin', 'pad');
        $context->setOption('margin_top', '12');
        $context->setOption('margin_bottom', '8');

        $content = $this->renderTwigTemplate($document, $model, ['pdfContext' => $context]);
        $content = $this->converter->convertToPdf($content, $context->getOptions());

        return $this->createPdfResponse($content, $context);
    }
}
