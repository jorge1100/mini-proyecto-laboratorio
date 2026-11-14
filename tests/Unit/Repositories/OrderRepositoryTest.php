<?php

use PHPUnit\Framework\TestCase;

/**
 * Repository — src/Repositories/OrderRepository.php.
 *
 * El repositorio es el único que conoce el almacenamiento: guarda el pedido
 * y lo devuelve ya con el total calculado.
 */
final class OrderRepositoryTest extends TestCase
{
    public function testSavesAndReturnsTheOrderWithItsTotal(): void
    {
        $repository = new OrderRepository(Connection::obtener());

        $repository->save(new Order(5, 'Ana Gomez', 22000.0, 'particular'), 22000.0);

        $pedido = $repository->find(5);

        self::assertNotNull($pedido);
        self::assertSame('Ana Gomez', $pedido['patient']);
        self::assertSame(22000.0, $pedido['total']);
        self::assertSame('particular', $pedido['patientType']);
    }

    public function testAllReturnsEverySavedOrder(): void
    {
        $repository = new OrderRepository(Connection::obtener());

        $repository->save(new Order(1, 'Juan Perez', 15000.0, 'obra_social'), 10500.0);
        $repository->save(new Order(2, 'Ana Gomez', 22000.0, 'particular'), 22000.0);

        self::assertCount(2, $repository->all());
        self::assertSame(10500.0, $repository->all()[0]['total']);
    }

    public function testReturnsNullForAnUnknownId(): void
    {
        $repository = new OrderRepository(Connection::obtener());

        self::assertNull($repository->find(999));
    }

    public function testReturnsEmptyListWhenNothingWasSaved(): void
    {
        $repository = new OrderRepository(Connection::obtener());

        self::assertSame([], $repository->all());
    }

    public function testEscapesQuotesInThePatientName(): void
    {
        $connection = Connection::obtener();
        $repository = new OrderRepository($connection);

        $repository->save(new Order(9, "O'Neil", 1000.0, 'particular'), 1000.0);

        $consultas = $connection->consultas();
        $ultima = end($consultas);

        self::assertIsString($ultima);
        self::assertStringContainsString("'O\\'Neil'", $ultima, 'La comilla debe ir escapada');
        self::assertStringNotContainsString("'O'Neil'", $ultima);
    }
}
