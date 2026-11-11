<?php

use PHPUnit\Framework\TestCase;

/**
 * Adapter — src/Legacy/LegacyNotifier.php.
 *
 * La clase de terceros (LegacyNotifier) no se modifica. El adapter la hace
 * cumplir nuestro contrato Notification. Los tests verifican la delegacion,
 * el contrato y la propagacion de errores.
 */
final class LegacyNotifierAdapterTest extends TestCase
{
    public function testAdapterImplementsNotificationContract(): void
    {
        $adapter = new LegacyNotifierAdapter(new LegacyNotifier());

        self::assertInstanceOf(Notification::class, $adapter);
    }

    public function testAdapterDelegatesToLegacySendMessage(): void
    {
        $legacy = new class extends LegacyNotifier {
            /** @var list<string> */
            public array $messages = [];

            public function sendMessage(string $text): void
            {
                $this->messages[] = $text;
            }
        };

        $adapter = new LegacyNotifierAdapter($legacy);
        $adapter->send('Pedido 1 creado');

        self::assertSame(['Pedido 1 creado'], $legacy->messages);
    }

    public function testAdapterOutputsLegacyFormat(): void
    {
        $adapter = new LegacyNotifierAdapter(new LegacyNotifier());

        ob_start();
        $adapter->send('Pedido 1 creado');
        $output = ob_get_clean();

        self::assertSame('[LEGACY] Pedido 1 creado<br>', $output);
    }

    public function testAdapterPropagatesLegacyException(): void
    {
        $legacy = new class extends LegacyNotifier {
            public function sendMessage(string $text): void
            {
                throw new RuntimeException('El proveedor esta caido');
            }
        };

        $adapter = new LegacyNotifierAdapter($legacy);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El proveedor esta caido');

        $adapter->send('Pedido 1 creado');
    }

    public function testAdapterRejectsDependencyThatIsNotLegacyNotifier(): void
    {
        $this->expectException(TypeError::class);

        new LegacyNotifierAdapter(new stdClass());
    }
}
