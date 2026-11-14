<?php

use PHPUnit\Framework\TestCase;

/**
 * Facade — src/Services/OrderFacade.php.
 *
 * La fachada coordina servicios cohesivos: valida, calcula, guarda, avisa y
 * notifica. Estos tests verifican que hace las cinco cosas y que corta
 * antes de guardar si la validación falla.
 */
final class OrderFacadeTest extends TestCase
{
    public function testReturnsTheTotalCalculatedByTheStrategy(): void
    {
        $facade = $this->facade();

        $total = $facade->procesarPedidoCompleto(
            new Order(1, 'Juan Perez', 15000.0, 'obra_social')
        );

        self::assertSame(10500.0, $total);
    }

    public function testSavesTheOrderWithTheCalculatedTotal(): void
    {
        $repository = new OrderRepository(Connection::obtener());
        $facade = $this->facade($repository);

        $facade->procesarPedidoCompleto(new Order(5, 'Ana Gomez', 22000.0, 'particular'));

        $pedido = $repository->find(5);

        self::assertNotNull($pedido);
        self::assertSame(22000.0, $pedido['total']);
    }

    public function testNotifiesTheObservers(): void
    {
        $observer = new class implements OrderObserver {
            public int $calls = 0;

            public function update(Order $order): void
            {
                $this->calls++;
            }
        };

        $events = new OrderSubject();
        $events->subscribe($observer);

        $this->facade(null, $events)->procesarPedidoCompleto(
            new Order(1, 'Juan Perez', 15000.0, 'obra_social')
        );

        self::assertSame(1, $observer->calls);
    }

    public function testSendsTheNotification(): void
    {
        ob_start();
        $this->facade()->procesarPedidoCompleto(
            new Order(1, 'Juan Perez', 15000.0, 'obra_social')
        );
        $output = ob_get_clean();

        self::assertStringContainsString(
            '[EMAIL] para paciente@mail.com: Pedido 1 por $ 10500',
            $output
        );
    }

    public function testRejectsAnUnsupportedTypeAndDoesNotSaveTheOrder(): void
    {
        $repository = new OrderRepository(Connection::obtener());
        $facade = $this->facade($repository);

        try {
            $facade->procesarPedidoCompleto(new Order(1, 'Juan Perez', 15000.0, 'xyz'));
            self::fail('Se esperaba InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            self::assertStringContainsString('no soportado', $e->getMessage());
        }

        self::assertSame([], $repository->all(), 'No debe guardar un pedido invalido');
    }

    public function testPropagatesTheErrorOfAFailingObserver(): void
    {
        $observer = new class implements OrderObserver {
            public function update(Order $order): void
            {
                throw new RuntimeException('El aviso cayo');
            }
        };

        $events = new OrderSubject();
        $events->subscribe($observer);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El aviso cayo');

        $this->facade(null, $events)->procesarPedidoCompleto(
            new Order(1, 'Juan Perez', 15000.0, 'obra_social')
        );
    }

    private function facade(
        ?OrderRepository $repository = null,
        ?OrderSubject $events = null
    ): OrderFacade {
        return new OrderFacade(
            new PriceCalculatorFactory(),
            $repository ?? new OrderRepository(Connection::obtener()),
            $events ?? new OrderSubject(),
            new NotificationSender(),
            'email',
            'paciente@mail.com'
        );
    }
}
