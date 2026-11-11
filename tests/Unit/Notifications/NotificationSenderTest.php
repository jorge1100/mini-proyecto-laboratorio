<?php

use PHPUnit\Framework\TestCase;

/**
 * Factory Method — src/Notifications/NotificationSender.php.
 *
 * El sender no conoce clases concretas: delega en la fabrica y en la
 * abstraccion Notification. Los tests verifican el formato de salida de cada
 * canal y que un tipo no soportado no se traga en silencio.
 */
final class NotificationSenderTest extends TestCase
{
    public function testSendsEmailThroughFactory(): void
    {
        $sender = new NotificationSender();

        ob_start();
        $sender->enviar('email', 'paciente@mail.com', 'Pedido 1 creado');
        $output = ob_get_clean();

        self::assertSame('[EMAIL] para paciente@mail.com: Pedido 1 creado<br>', $output);
    }

    public function testSendsSmsThroughFactory(): void
    {
        $sender = new NotificationSender();

        ob_start();
        $sender->enviar('sms', '3704000000', 'Pedido 1 creado');
        $output = ob_get_clean();

        self::assertSame('[SMS] a 3704000000: Pedido 1 creado<br>', $output);
    }

    public function testSendsWhatsAppThroughFactory(): void
    {
        $sender = new NotificationSender();

        ob_start();
        $sender->enviar('whatsapp', '3704000000', 'Pedido 1 creado');
        $output = ob_get_clean();

        self::assertSame('[WHATSAPP] a 3704000000: Pedido 1 creado<br>', $output);
    }

    public function testPropagatesFactoryExceptionForUnsupportedType(): void
    {
        $sender = new NotificationSender();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no soportado');

        $sender->enviar('telegram', 'destino', 'Pedido 1 creado');
    }
}
