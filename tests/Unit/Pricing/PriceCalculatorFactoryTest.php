<?php

use PHPUnit\Framework\TestCase;

/**
 * Factory — src/Pricing/PriceCalculatorFactory.php.
 *
 * La fábrica es el único lugar que sabe qué tipos de paciente existen y qué
 * descuento aplica a cada uno.
 */
final class PriceCalculatorFactoryTest extends TestCase
{
    public function testParticularPaysTheFullAmount(): void
    {
        $calculator = (new PriceCalculatorFactory())->for('particular');

        self::assertSame(22000.0, $calculator->calculate(22000.0));
    }

    public function testObraSocialGetsTheInsuranceStrategy(): void
    {
        $calculator = (new PriceCalculatorFactory())->for('obra_social');

        self::assertSame(10500.0, $calculator->calculate(15000.0));
    }

    public function testJubiladoGetsTheRetiredStrategy(): void
    {
        $calculator = (new PriceCalculatorFactory())->for('jubilado');

        self::assertSame(7500.0, $calculator->calculate(15000.0));
    }

    public function testPrepagaGetsThePrepaidStrategy(): void
    {
        $calculator = (new PriceCalculatorFactory())->for('prepaga');

        self::assertSame(12750.0, $calculator->calculate(15000.0));
    }

    public function testRejectsAnUnsupportedPatientType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de paciente no soportado: xyz');

        (new PriceCalculatorFactory())->for('xyz');
    }

    public function testPatientTypeIsCaseSensitive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new PriceCalculatorFactory())->for('Obra_Social');
    }
}
