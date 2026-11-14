<?php

use PHPUnit\Framework\TestCase;

/**
 * Observer — src/Events/OrderSubject.php.
 *
 * El sujeto mantiene una colección de INTERFACES y avisa a todos los
 * observadores. Estos tests verifican el aviso, el aislamiento de errores y
 * que agregar un observador no obliga a tocar el sujeto.
 */
final class OrderSubjectTest extends TestCase
{
    public function testNotifiesSubscribedObserverWithTheOrder(): void
    {
        $observer = $this->spy();

        $subject = new OrderSubject();
        $subject->subscribe($observer);

        $order = new Order(1, 'Juan Perez', 15000, 'obra_social');
        $subject->notify($order);

        self::assertSame($order, $observer->received);
    }

    public function testNotifiesAllObserversInSubscriptionOrder(): void
    {
        $primero = $this->spy();
        $segundo = $this->spy();

        $subject = new OrderSubject();
        $subject->subscribe($primero);
        $subject->subscribe($segundo);

        $order = new Order(1, 'Juan Perez', 15000, 'obra_social');
        $subject->notify($order);

        self::assertSame($order, $primero->received);
        self::assertSame($order, $segundo->received);
    }

    public function testDoesNotNotifyObserversThatWereNotSubscribed(): void
    {
        $observer = $this->spy();

        $subject = new OrderSubject();
        $subject->notify(new Order(1, 'Juan Perez', 15000, 'obra_social'));

        self::assertNull($observer->received);
    }

    public function testSmsObserverIsAddedWithoutTouchingTheSubject(): void
    {
        $subject = new OrderSubject();
        $subject->subscribe(new SmsObserver());

        ob_start();
        $subject->notify(new Order(7, 'Ana Gomez', 22000, 'particular'));
        $output = ob_get_clean();

        self::assertStringContainsString('[OBSERVER-SMS] Pedido 7 creado', $output);
    }

    public function testFailingObserverDoesNotStopTheOthersAndTheErrorIsRethrown(): void
    {
        $fallido = new class implements OrderObserver {
            public function update(Order $order): void
            {
                throw new RuntimeException('El aviso cayo');
            }
        };

        $sano = $this->spy();

        $subject = new OrderSubject();
        $subject->subscribe($fallido);
        $subject->subscribe($sano);

        $order = new Order(1, 'Juan Perez', 15000, 'obra_social');

        try {
            $subject->notify($order);
            self::fail('Se esperaba que se propagara el error del observador');
        } catch (RuntimeException $e) {
            self::assertSame('El aviso cayo', $e->getMessage());
        }

        self::assertSame($order, $sano->received, 'El segundo observador tambien debe enterarse');
    }

    public function testSameObserverSubscribedTwiceReceivesTwoNotices(): void
    {
        $observer = $this->spy();

        $subject = new OrderSubject();
        $subject->subscribe($observer);
        $subject->subscribe($observer);

        $subject->notify(new Order(1, 'Juan Perez', 15000, 'obra_social'));

        self::assertSame(2, $observer->calls, 'El sujeto no deduplica observadores');
    }

    /**
     * @return OrderObserver&object{received: ?Order, calls: int}
     */
    private function spy(): OrderObserver
    {
        return new class implements OrderObserver {
            public ?Order $received = null;
            public int $calls = 0;

            public function update(Order $order): void
            {
                $this->received = $order;
                $this->calls++;
            }
        };
    }
}
