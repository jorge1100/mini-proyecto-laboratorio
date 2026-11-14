<?php

use PHPUnit\Framework\TestCase;

/**
 * Observer — src/Events/*Observer.php.
 *
 * Cada observador produce su propio aviso. Agregar uno nuevo no obliga a
 * modificar los demás (ejercicio 5 del TP).
 */
final class OrderObserverOutputTest extends TestCase
{
    public function testEmailObserverWritesItsNotice(): void
    {
        ob_start();
        (new EmailObserver())->update(new Order(3, 'Ana Gomez', 22000, 'particular'));
        $output = ob_get_clean();

        self::assertStringContainsString('[OBSERVER-EMAIL] Pedido 3 creado', $output);
    }

    public function testSmsObserverWritesItsNotice(): void
    {
        ob_start();
        (new SmsObserver())->update(new Order(4, 'Luis Diaz', 9000, 'jubilado'));
        $output = ob_get_clean();

        self::assertStringContainsString('[OBSERVER-SMS] Pedido 4 creado', $output);
    }

    public function testDashboardObserverWritesItsNotice(): void
    {
        ob_start();
        (new DashboardObserver())->update(new Order(5, 'Maria Lopez', 12000, 'prepaga'));
        $output = ob_get_clean();

        self::assertStringContainsString('[DASHBOARD] actualizado para el pedido 5', $output);
    }
}
