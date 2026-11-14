<?php

/**
 * Modelo Order.
 *
 * Solo modela el pedido: datos inmutables y validación de sus propios
 * invariantes en el constructor.
 *
 * Lo que NO hace (y antes sí):
 *   - No se persiste: eso vive en OrderRepository.
 *   - No calcula precios: eso vive en las estrategias de src/Pricing.
 */
final class Order
{
    public function __construct(
        public readonly int $id,
        public readonly string $patient,
        public readonly float $amount,
        public readonly string $patientType
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException('El id del pedido debe ser mayor a cero');
        }

        if (trim($patient) === '') {
            throw new InvalidArgumentException('El paciente es obligatorio');
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException('El importe no puede ser negativo');
        }
    }
}
