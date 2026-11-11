<?php

use PHPUnit\Framework\TestCase;

/**
 * Strategy — src/Pricing.
 *
 * El calculador no decide el algoritmo: delega en la estrategia inyectada.
 * Estos tests verifican que cada estrategia aplica el descuento correcto y
 * que PriceCalculator respeta el contrato (delegación, IVA y propagación
 * de errores).
 */
final class PriceCalculatorTest extends TestCase
{
    public function testPrivatePatientPaysFullAmount(): void
    {
        $calculator = new PriceCalculator(new PrivatePatientStrategy());

        self::assertSame(15000.0, $calculator->calculate(15000.0));
    }

    public function testInsuranceAppliesDefault30PercentDiscount(): void
    {
        $calculator = new PriceCalculator(new InsuranceStrategy());

        self::assertSame(10500.0, $calculator->calculate(15000.0));
    }

    public function testInsuranceAcceptsCustomDiscount(): void
    {
        $calculator = new PriceCalculator(new InsuranceStrategy(0.25));

        self::assertSame(11250.0, $calculator->calculate(15000.0));
    }

    public function testRetiredAppliesDefault50PercentDiscount(): void
    {
        $calculator = new PriceCalculator(new RetiredStrategy());

        self::assertSame(7500.0, $calculator->calculate(15000.0));
    }

    public function testPrepaidAppliesDefault15PercentDiscount(): void
    {
        $calculator = new PriceCalculator(new PrepaidStrategy());

        self::assertSame(12750.0, $calculator->calculate(15000.0));
    }

    public function testPriceCalculatorDelegatesToInjectedStrategy(): void
    {
        $strategy = new class implements PricingStrategy {
            public bool $called = false;
            public float $amountReceived = 0.0;

            public function calculate(float $amount): float
            {
                $this->called = true;
                $this->amountReceived = $amount;

                return 42.0;
            }
        };

        $calculator = new PriceCalculator($strategy);
        $result = $calculator->calculate(999.0);

        self::assertTrue($strategy->called, 'La estrategia inyectada debe recibir el calculo');
        self::assertSame(999.0, $strategy->amountReceived);
        self::assertSame(42.0, $result);
    }

    public function testCalculateWithVatAppliesDefaultVatOverDiscountedTotal(): void
    {
        $calculator = new PriceCalculator(new InsuranceStrategy());

        // 15000 - 30% = 10500; 10500 * 1.21 = 12705
        self::assertSame(12705.0, $calculator->calculateWithVat(15000.0));
    }

    public function testCalculateWithVatAcceptsCustomRate(): void
    {
        $calculator = new PriceCalculator(new PrivatePatientStrategy());

        // 15000 * 1.10 = 16500
        self::assertSame(16500.0, $calculator->calculateWithVat(15000.0, 1.10));
    }

    public function testZeroAmountReturnsZero(): void
    {
        $calculator = new PriceCalculator(new InsuranceStrategy());

        self::assertSame(0.0, $calculator->calculate(0.0));
    }

    public function testCalculateWithVatPropagatesStrategyException(): void
    {
        $strategy = new class implements PricingStrategy {
            public function calculate(float $amount): float
            {
                throw new RuntimeException('La estrategia fallo');
            }
        };

        $calculator = new PriceCalculator($strategy);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('La estrategia fallo');

        $calculator->calculateWithVat(1000.0);
    }

    public function testCalculatePropagatesStrategyException(): void
    {
        $strategy = new class implements PricingStrategy {
            public function calculate(float $amount): float
            {
                throw new RuntimeException('La estrategia fallo');
            }
        };

        $calculator = new PriceCalculator($strategy);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('La estrategia fallo');

        $calculator->calculate(1000.0);
    }
}
