<?php

use PHPUnit\Framework\TestCase;

/**
 * Singleton — src/Database/Connection.php.
 *
 * Una única instancia por proceso. El constructor privado es lo que
 * garantiza que nadie pueda crear una segunda conexión.
 */
final class ConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        Connection::reset();
    }

    public function testAlwaysReturnsTheSameInstance(): void
    {
        $primera = Connection::getInstance();
        $segunda = Connection::getInstance();

        self::assertSame($primera, $segunda);
    }

    public function testKeepsTheExecutedQueries(): void
    {
        $connection = Connection::getInstance();

        $connection->ejecutar('SELECT 1');
        $connection->ejecutar('SELECT 2');

        self::assertSame(['SELECT 1', 'SELECT 2'], $connection->consultas());
    }

    public function testConstructorIsPrivate(): void
    {
        $constructor = (new ReflectionClass(Connection::class))->getConstructor();

        self::assertNotNull($constructor);
        self::assertTrue(
            $constructor->isPrivate(),
            'El constructor privado es lo que garantiza la instancia unica'
        );
    }

    public function testRejectsAnEmptyQuery(): void
    {
        $connection = Connection::getInstance();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('La consulta no puede estar vacia');

        $connection->ejecutar('   ');
    }

    public function testResetDiscardsTheInstance(): void
    {
        $primera = Connection::getInstance();

        Connection::reset();

        self::assertNotSame($primera, Connection::getInstance());
    }
}
