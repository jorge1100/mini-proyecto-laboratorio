<?php

/**
 * Cargador de datos de demostración.
 *
 * La demo necesita dos pedidos de ejemplo. Se crean por el MISMO camino que
 * un pedido real (estrategia + repositorio), así el total sale de la
 * estrategia y no de un `* 0.7` escrito a mano en la vista.
 */
final class DemoOrderSeeder
{
    public function __construct(
        private OrderRepository $repository,
        private PriceCalculatorFactory $calculators
    ) {}

    public function seed(): void
    {
        $datos = [
            [1, 'Juan Perez', 15000.0, 'obra_social'],
            [2, 'Ana Gomez', 22000.0, 'particular'],
        ];

        foreach ($datos as [$id, $patient, $amount, $patientType]) {
            $order = new Order($id, $patient, $amount, $patientType);
            $total = $this->calculators->for($patientType)->calculate($amount);

            $this->repository->save($order, $total);
        }
    }
}
