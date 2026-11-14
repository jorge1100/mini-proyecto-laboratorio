<?php

use PHPUnit\Framework\TestCase;

/**
 * Datos de demostración — src/Repositories/DemoOrderSeeder.php.
 *
 * Los pedidos de ejemplo se crean por el mismo camino que uno real: el total
 * sale de la estrategia, no de un número escrito a mano.
 */
final class DemoOrderSeederTest extends TestCase
{
    public function testSeedsTwoOrdersWithTheTotalsFromTheStrategies(): void
    {
        $repository = new OrderRepository(Connection::obtener());

        (new DemoOrderSeeder($repository, new PriceCalculatorFactory()))->seed();

        $pedidos = $repository->all();

        self::assertCount(2, $pedidos);
        self::assertSame('Juan Perez', $pedidos[0]['patient']);
        self::assertSame(10500.0, $pedidos[0]['total'], 'Obra social: 15000 - 30%');
        self::assertSame('Ana Gomez', $pedidos[1]['patient']);
        self::assertSame(22000.0, $pedidos[1]['total'], 'Particular: sin descuento');
    }
}
