<?php

/**
 * Sujeto del patrón Observer.
 *
 * Mantiene la colección de observadores y les avisa cuando se crea un pedido.
 * El sujeto conoce la INTERFAZ, no las clases concretas.
 *
 * Si un observador falla, el resto se entera igual y el error se relanza al
 * final: ni se corta la cadena en silencio (estado inconsistente) ni se oculta
 * el problema.
 */
final class OrderSubject
{
    /** @var list<OrderObserver> */
    private array $observers = [];

    public function subscribe(OrderObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    /**
     * @throws Throwable el primer error encontrado, después de avisar a todos
     */
    public function notify(Order $order): void
    {
        $error = null;

        foreach ($this->observers as $observer) {
            try {
                $observer->update($order);
            } catch (Throwable $e) {
                $error ??= $e;
            }
        }

        if ($error !== null) {
            throw $error;
        }
    }
}
