<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Factory Method — src/Notifications/NotificationFactory.php.
 *
 * La fabrica es el unico lugar que conoce las clases concretas. Los tests
 * verifican que devuelve la abstraccion correcta y que rechaza tipos no
 * soportados.
 */
final class NotificationFactoryTest extends TestCase
{
    public function testCreatesEmailNotification(): void
    {
        $notification = NotificationFactory::create('email', 'paciente@mail.com');

        self::assertInstanceOf(EmailNotification::class, $notification);
    }

    public function testCreatesSmsNotification(): void
    {
        $notification = NotificationFactory::create('sms', '3704000000');

        self::assertInstanceOf(SmsNotification::class, $notification);
    }

    public function testCreatesWhatsAppNotification(): void
    {
        $notification = NotificationFactory::create('whatsapp', '3704000000');

        self::assertInstanceOf(WhatsAppNotification::class, $notification);
    }

    #[DataProvider('unsupportedTypes')]
    public function testThrowsOnUnsupportedType(string $type): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no soportado');

        NotificationFactory::create($type, 'destino@mail.com');
    }

    public static function unsupportedTypes(): array
    {
        return [
            'tipo inexistente' => ['telegram'],
            'tipo vacio' => [''],
            'tipo en mayusculas' => ['Email'],
        ];
    }
}
