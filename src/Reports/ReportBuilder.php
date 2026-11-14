<?php

/**
 * Constructor de reportes.
 *
 * Ensambla el reporte del laboratorio con los decoradores estándar.
 * El controlador no necesita conocer los decoradores concretos.
 */
final class ReportBuilder
{
    public function build(string $contenido): Report
    {
        $report = new BasicReport($contenido);
        $report = new DigitalSignatureDecorator($report);
        $report = new PdfReportDecorator($report);
        $report = new WatermarkDecorator($report);

        return $report;
    }
}
