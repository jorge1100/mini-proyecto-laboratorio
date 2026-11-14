<?php

/**
 * Observer que avisa por SMS.
 *
 * EJERCICIO 5: se agregó esta clase SIN tocar el sujeto. Con el patrón
 * Observer, el aviso nuevo no obliga a modificar OrderSubject ni a los
 * observadores que ya existían.
 */
final class SmsObserver implements OrderObserver
{
    public function __construct(private string $telefono = '3704000000') {}

    public function update(Order $order): void
    {
        echo "[OBSERVER-SMS] Pedido {$order->id} creado<br>";
    }
}
