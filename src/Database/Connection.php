<?php

/**
 * ============================================================================
 *  CONEXION A LA BASE DE DATOS
 *  Patrón aplicado: SINGLETON (creacional)
 * ============================================================================
 *
 *  El constructor es PRIVADO a propósito: nadie puede hacer `new Connection()`
 *  desde afuera y romper la garantía de instancia única. Para obtener la
 *  conexión se usa `Connection::getInstance()`.
 *
 *  La configuración se inyecta, no se lee de constantes globales, y si una
 *  consulta falla, el error se propaga: fallar rápido y fuerte.
 *
 *  ⚠️ El Singleton sirve para conexión, logger y configuración. NO para
 *     guardar el pedido actual ni el usuario logueado: ahí deja de ser un
 *     patrón y pasa a ser una variable global disfrazada.
 */
final class Connection
{
    private static ?Connection $instance = null;

    /** @var list<string> consultas ejecutadas en esta conexión */
    private array $consultas = [];

    /**
     * Constructor privado: la instancia se crea desde getInstance().
     */
    private function __construct() {}

    /**
     * Devuelve la única instancia de la conexión.
     */
    public static function getInstance(): Connection
    {
        return self::$instance ??= new Connection();
    }

    /**
     * Ejecuta una consulta.
     *
     * @throws InvalidArgumentException si la consulta viene vacía
     */
    public function ejecutar(string $sql): void
    {
        if (trim($sql) === '') {
            throw new InvalidArgumentException('La consulta no puede estar vacia');
        }

        $this->consultas[] = $sql;
    }

    /**
     * @return list<string>
     */
    public function consultas(): array
    {
        return $this->consultas;
    }

    /**
     * Descarta la instancia única.
     *
     * Existe para las pruebas: el estado de un Singleton es global y cada
     * test necesita empezar limpio. En la aplicación no debería usarse.
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
