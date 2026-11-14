<?php

/**
 * Renderizador de vistas.
 *
 * Recibe datos YA preparados y devuelve el HTML. La vista no consulta la base,
 * no calcula nada y escapa la salida: esa responsabilidad es de la vista y
 * del controlador, no de la lógica de negocio.
 */
final class View
{
    public function __construct(private string $basePath) {}

    /**
     * Renderiza una plantilla con los datos indicados.
     *
     * @param array<string, mixed> $data variables disponibles en la plantilla
     * @throws RuntimeException si la plantilla no existe
     */
    public function render(string $template, array $data = []): string
    {
        $file = $this->basePath . '/' . $template . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("No existe la vista: {$template}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }

    /**
     * Escapa un valor para insertarlo en HTML.
     */
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
