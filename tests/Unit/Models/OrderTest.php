<?php

use PHPUnit\Framework\TestCase;

/**
 * Modelo Order — src/Models/Order.php.
 *
 * El modelo solo modela: datos inmutables y validación de sus invariantes.
 * No persiste y no calcula precios.
 */
final class OrderTest extends TestCase
{
    public function testKeepsTheOrderData(): void
    {
        $order = new Order(1, 'Juan Perez', 15000.0, 'obra_social');

        self::assertSame(1, $order->id);
        self::assertSame('Juan Perez', $order->patient);
        self::assertSame(15000.0, $order->amount);
        self::assertSame('obra_social', $order->patientType);
    }

    public function testRejectsNonPositiveId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('id del pedido');

        new Order(0, 'Juan Perez', 15000.0, 'particular');
    }

    public function testRejectsBlankPatient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('paciente es obligatorio');

        new Order(1, '   ', 15000.0, 'particular');
    }

    public function testRejectsNonPositiveAmount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('importe');

        new Order(1, 'Juan Perez', -15000.0, 'particular');
    }

    public function testRejectsZeroAmount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Order(1, 'Juan Perez', 0.0, 'particular');
    }
}
