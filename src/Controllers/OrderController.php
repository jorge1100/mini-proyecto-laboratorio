<?php

/**
 * Controlador de pedidos.
 *
 * Recibe la entrada, la delega en la fachada y muestra el resultado.
 * La lógica de negocio vive en la fachada y sus servicios.
 */
class OrderController
{
    public function __construct(private OrderFacade $facade) {}

    /**
     * Crea un pedido a partir de los parámetros de la URL.
     */
    public function create(): void
    {
        try {
            $order = new Order(
                (int) ($_GET['id'] ?? 1),
                (string) ($_GET['paciente'] ?? 'Juan Perez'),
                (float) ($_GET['monto'] ?? 15000),
                (string) ($_GET['tipo'] ?? 'obra_social')
            );

            $total = $this->facade->procesarPedidoCompleto($order);
        } catch (InvalidArgumentException $e) {
            echo "Pedido rechazado: {$e->getMessage()}";
            return;
        }

        echo "<h1>Pedido creado</h1>";
        echo "<p>Paciente: {$order->patient}</p>";
        echo "<p>Total: $ {$total}</p>";
    }

    /**
     * Lista los pedidos.
     */
    public function index(): void
    {
        require __DIR__ . '/../../views/orders.php';
    }

    /**
     * Genera el reporte del día combinando decoradores.
     */
    public function report(): void
    {
        $reporte = new BasicReport('Pedidos del dia');
        $reporte = new DigitalSignatureDecorator($reporte);
        $reporte = new PdfReportDecorator($reporte);
        $reporte = new WatermarkDecorator($reporte);

        echo $reporte->generate();
    }
}
