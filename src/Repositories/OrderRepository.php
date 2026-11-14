<?php

/**
 * Repositorio de pedidos.
 *
 * Es el ÚNICO lugar que conoce el almacenamiento. El modelo no sabe SQL:
 * si cambia el motor de base de datos, cambia este archivo y no el modelo.
 */
final class OrderRepository
{
    /** @var list<array{id: int, patient: string, total: float, patientType: string}> */
    private array $pedidos = [];

    public function __construct(private Connection $connection) {}

    /**
     * Guarda el pedido con el total ya calculado.
     */
    public function save(Order $order, float $total): void
    {
        $this->pedidos[] = [
            'id' => $order->id,
            'patient' => $order->patient,
            'total' => $total,
            'patientType' => $order->patientType,
        ];

        $this->connection->ejecutar(sprintf(
            "INSERT INTO orders (id, patient, amount) VALUES (%d, '%s', %.2f)",
            $order->id,
            addslashes($order->patient),
            $total
        ));
    }

    /**
     * @return list<array{id: int, patient: string, total: float, patientType: string}>
     */
    public function all(): array
    {
        return $this->pedidos;
    }

    /**
     * @return array{id: int, patient: string, total: float, patientType: string}|null
     */
    public function find(int $id): ?array
    {
        foreach ($this->pedidos as $pedido) {
            if ($pedido['id'] === $id) {
                return $pedido;
            }
        }

        return null;
    }
}
