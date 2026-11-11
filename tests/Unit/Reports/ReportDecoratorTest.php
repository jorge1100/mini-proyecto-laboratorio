<?php

use PHPUnit\Framework\TestCase;

/**
 * Decorator — src/Reports.
 *
 * El reporte base y cada decorador implementan el mismo contrato Report.
 * Los tests verifican que los decoradores se pueden encadenar en cualquier
 * orden y que el orden del encadenado define el resultado.
 */
final class ReportDecoratorTest extends TestCase
{
    public function testBasicReportGeneratesBaseContent(): void
    {
        $report = new BasicReport('Pedidos del dia');

        self::assertSame('Reporte: Pedidos del dia', $report->generate());
    }

    public function testDigitalSignatureAppendsSignature(): void
    {
        $report = new DigitalSignatureDecorator(new BasicReport('Pedidos del dia'));

        self::assertSame('Reporte: Pedidos del dia + firma digital', $report->generate());
    }

    public function testPdfAppendsPdfMark(): void
    {
        $report = new PdfReportDecorator(new BasicReport('Pedidos del dia'));

        self::assertSame('Reporte: Pedidos del dia + PDF', $report->generate());
    }

    public function testWatermarkAppendsWatermark(): void
    {
        $report = new WatermarkDecorator(new BasicReport('Pedidos del dia'));

        self::assertSame('Reporte: Pedidos del dia + marca de agua', $report->generate());
    }

    public function testDecoratorsCanBeCombined(): void
    {
        $report = new WatermarkDecorator(
            new PdfReportDecorator(
                new DigitalSignatureDecorator(
                    new BasicReport('Pedidos del dia')
                )
            )
        );

        self::assertSame(
            'Reporte: Pedidos del dia + firma digital + PDF + marca de agua',
            $report->generate()
        );
    }

    public function testDecoratorOrderChangesResult(): void
    {
        $report = new PdfReportDecorator(
            new WatermarkDecorator(new BasicReport('Pedidos del dia'))
        );

        self::assertSame('Reporte: Pedidos del dia + marca de agua + PDF', $report->generate());
    }

    public function testDecoratorRejectsDependencyThatIsNotAReport(): void
    {
        $this->expectException(TypeError::class);

        new DigitalSignatureDecorator(new stdClass());
    }

    public function testDecoratorPropagatesInnerReportException(): void
    {
        $inner = new class implements Report {
            public function generate(): string
            {
                throw new RuntimeException('El reporte interno fallo');
            }
        };

        $report = new DigitalSignatureDecorator($inner);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El reporte interno fallo');

        $report->generate();
    }
}
