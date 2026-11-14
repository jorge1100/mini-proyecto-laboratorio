<?php

/**
 * Fábrica de calculadores por tipo de paciente.
 *
 * Es el ÚNICO lugar que sabe qué tipos de paciente existen y qué descuento
 * aplica a cada uno. El modelo no lo sabe, la fachada tampoco.
 *
 * Agregar un tipo nuevo = agregar una estrategia (Strategy) y UNA línea acá.
 * No hay que tocar el modelo, la fachada ni los tests de los otros tipos.
 */
final class PriceCalculatorFactory
{
    public function for(string $patientType): PriceCalculator
    {
        return new PriceCalculator(match ($patientType) {
            'particular'  => new PrivatePatientStrategy(),
            'obra_social' => new InsuranceStrategy(),
            'jubilado'    => new RetiredStrategy(),
            'prepaga'     => new PrepaidStrategy(),
            default       => throw new InvalidArgumentException(
                "Tipo de paciente no soportado: {$patientType}"
            ),
        });
    }
}
