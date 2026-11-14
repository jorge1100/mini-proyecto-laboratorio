<?php

use PHPUnit\Framework\TestCase;

/**
 * Vista — src/Views/View.php.
 *
 * El renderizador recibe datos ya preparados y devuelve HTML. La plantilla
 * real (views/orders.php) se usa para verificar que la salida va escapada.
 */
final class ViewTest extends TestCase
{
    public function testRendersTheTemplateWithTheGivenData(): void
    {
        $view = new View(__DIR__ . '/../../../views');

        $html = $view->render('pedido-creado', [
            'pedido' => ['id' => 1, 'patient' => 'Juan Perez', 'total' => 10500.0],
            'reporte' => 'Reporte: Pedido 1',
        ]);

        self::assertStringContainsString('<h1>Pedido creado</h1>', $html);
        self::assertStringContainsString('Juan Perez', $html);
        self::assertStringContainsString('10500', $html);
        self::assertStringContainsString('Reporte: Pedido 1', $html);
    }

    public function testEscapeEscapesHtmlSpecialCharacters(): void
    {
        $escaped = View::escape('<script>alert("xss")</script>');

        self::assertStringNotContainsString('<script>', $escaped);
        self::assertStringContainsString('&lt;script&gt;', $escaped);
    }

    public function testThrowsWhenTheTemplateDoesNotExist(): void
    {
        $view = new View(__DIR__ . '/../../../views');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No existe la vista: inexistente');

        $view->render('inexistente');
    }

    public function testOrdersViewEscapesThePatientName(): void
    {
        $view = new View(__DIR__ . '/../../../views');

        $html = $view->render('orders', [
            'pedidos' => [
                ['id' => 1, 'patient' => '<script>alert("xss")</script>', 'total' => 10500.0],
            ],
        ]);

        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testOrdersViewShowsTheTotalsItReceives(): void
    {
        $view = new View(__DIR__ . '/../../../views');

        $html = $view->render('orders', [
            'pedidos' => [['id' => 1, 'patient' => 'Juan Perez', 'total' => 10500.0]],
        ]);

        self::assertStringContainsString('10500', $html);
        self::assertStringContainsString('Juan Perez', $html);
    }
}
