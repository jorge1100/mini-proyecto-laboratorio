<?php

/**
 * Adapter: hace que el sistema heredado cumpla nuestro contrato Notification.
 *
 * Vive en su propio archivo (PSR-1: una clase por archivo) para que el
 * autoload por nombre de clase lo encuentre.
 */
final class LegacyNotifierAdapter implements Notification
{
    public function __construct(private LegacyNotifier $legacy) {}

    public function send(string $message): void
    {
        $this->legacy->sendMessage($message);
    }
}
