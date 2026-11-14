<?php

use PHPUnit\Framework\TestCase;

/**
 * MVC — src/Controllers/OrderController.php.
 *
 * El controlador recibe la entrada, la delega en la fachada y devuelve el
 * HTML de la vista. No tiene SQL, ni reglas de negocio, ni echo.
 */
final class OrderControllerTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $getOriginal;

    protected function setUp(): void
    {
        $this->getOriginal = $_GET;
    }

    protected function tearDown(): void
    {
        $_GET = $this->getOriginal;
    }

    public function testCreateReturnsTheCreatedOrder(): void
    {
        $_GET = ['id' => '5', 'paciente' => 'Ana Gomez', 'monto' => '22000', 'tipo' => 'particular'];

        ob_start();
        $html = $this->controller()->create();
        ob_get_clean();

        self::assertStringContainsString('<h1>Pedido creado</h1>', $html);
        self::assertStringContainsString('Ana Gomez', $html);
        self::assertStringContainsString('22000', $html);
    }

    public function testCreateReturnsTheErrorViewForAnInvalidAmount(): void
    {
        $_GET = ['id' => '1', 'paciente' => 'Juan Perez', 'monto' => '-5', 'tipo' => 'particular'];

        $html = $this->controller()->create();

        self::assertStringContainsString('<h1>Pedido rechazado</h1>', $html);
        self::assertStringContainsString('importe', $html);
    }

    public function testCreateReturnsTheErrorViewForAnUnsupportedType(): void
    {
        $_GET = ['id' => '1', 'paciente' => 'Juan Perez', 'monto' => '15000', 'tipo' => 'xyz'];

        $html = $this->controller()->create();

        self::assertStringContainsString('<h1>Pedido rechazado</h1>', $html);
        self::assertStringContainsString('no soportado', $html);
    }

    public function testIndexReturnsTheOrdersTable(): void
    {
        $repository = new OrderRepository(Connection::getInstance());
        (new DemoOrderSeeder($repository, new PriceCalculatorFactory()))->seed();

        $html = $this->controller($repository)->index();

        self::assertStringContainsString('<h1>Pedidos</h1>', $html);
        self::assertStringContainsString('Juan Perez', $html);
        self::assertStringContainsString('10500', $html);
    }

    public function testReportReturnsTheDecoratedReport(): void
    {
        $html = $this->controller()->report();

        self::assertSame(
            'Reporte: Pedidos del dia + firma digital + PDF + marca de agua',
            $html
        );
    }

    private function controller(?OrderRepository $repository = null): OrderController
    {
        $repository ??= new OrderRepository(Connection::getInstance());

        return new OrderController(
            new OrderFacade(
                new PriceCalculatorFactory(),
                $repository,
                new OrderSubject(),
                new NotificationSender()
            ),
            $repository,
            new View(__DIR__ . '/../../../views'),
            new ReportBuilder()
        );
    }
}
