<?php

/**
 * Controlador de pedidos.
 *
 * Recibe la entrada, se la delega en la fachada y elige qué vista renderizar.
 * NO contiene SQL, ni reglas de negocio, ni HTML.
 */
final class OrderController
{
    public function __construct(
        private OrderFacade $facade,
        private OrderRepository $repository,
        private View $view,
        private ReportBuilder $reports
    ) {}

    /**
     * Crea un pedido a partir de los parámetros de la URL.
     */
    public function create(): string
    {
        try {
            $order = $this->orderFromRequest();
            $total = $this->facade->procesarPedidoCompleto($order);
        } catch (InvalidArgumentException $e) {
            return $this->view->render('error', ['mensaje' => $e->getMessage()]);
        }

        return $this->view->render('pedido-creado', [
            'pedido' => ['id' => $order->id, 'patient' => $order->patient, 'total' => $total],
            'reporte' => $this->reports->build("Pedido {$order->id}")->generate(),
        ]);
    }

    /**
     * Lista los pedidos.
     */
    public function index(): string
    {
        return $this->view->render('orders', ['pedidos' => $this->repository->all()]);
    }

    /**
     * Genera el reporte del día combinando decoradores.
     */
    public function report(): string
    {
        return $this->reports->build('Pedidos del dia')->generate();
    }

    /**
     * Arma el pedido con los parámetros de la URL.
     */
    private function orderFromRequest(): Order
    {
        return new Order(
            (int) ($_GET['id'] ?? 1),
            (string) ($_GET['paciente'] ?? 'Juan Perez'),
            (float) ($_GET['monto'] ?? 15000),
            (string) ($_GET['tipo'] ?? 'obra_social')
        );
    }
}
