<?php

/**
 * Observer que avisa por email.
 *
 * EJERCICIO 5: se agregó esta clase y el registro en la composición del
 * sujeto. NO se tocó OrderSubject ni ningún otro aviso.
 */
final class EmailObserver implements OrderObserver
{
    public function __construct(private string $destino = 'paciente@mail.com') {}

    public function update(Order $order): void
    {
        echo "[OBSERVER-EMAIL] Pedido {$order->id} creado<br>";
    }
}
