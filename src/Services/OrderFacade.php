<?php

/**
 * Fachada de pedidos.
 *
 * Coordina servicios cohesivos (precio, persistencia, avisos y
 * notificación). NO decide reglas de negocio: solo orquesta.
 *
 * El método `procesarPedidoCompleto()` es corto a propósito: toda la
 * complejidad vive en las clases que coordina.
 */
final class OrderFacade
{
    public function __construct(
        private PriceCalculatorFactory $calculators,
        private OrderRepository $repository,
        private OrderSubject $events,
        private NotificationSender $notifications,
        private string $notificationType = 'email',
        private string $notificationDestination = 'paciente@mail.com'
    ) {}

    /**
     * Procesa un pedido completo.
     *
     * @return float el total ya calculado
     */
    public function procesarPedidoCompleto(Order $order): float
    {
        $calculator = $this->calculators->for($order->patientType);

        $total = $calculator->calculate($order->amount);

        $this->repository->save($order, $total);
        $this->events->notify($order);
        $this->notifications->enviar(
            $this->notificationType,
            $this->notificationDestination,
            "Pedido {$order->id} por $ {$total}"
        );

        return $total;
    }
}
