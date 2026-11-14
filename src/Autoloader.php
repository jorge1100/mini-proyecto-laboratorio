<?php

/**
 * Autoload del proyecto.
 *
 * Las clases no tienen namespace: se busca <Clase>.php en cualquier
 * subdirectorio de src/.
 *
 * Lo usan el punto de entrada (public/index.php) y el bootstrap de las
 * pruebas (tests/bootstrap.php), para que no existan dos implementaciones
 * del mismo recorrido.
 */
final class Autoloader
{
    /**
     * Registra un autoloader que resuelve las clases por nombre de archivo.
     *
     * @param string $baseDir directorio raíz de las clases (src/)
     */
    public static function register(string $baseDir): void
    {
        spl_autoload_register(static function (string $class) use ($baseDir): void {
            static $map = null;

            if ($map === null) {
                $map = [];
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getExtension() === 'php') {
                        $map[$file->getBasename('.php')] = $file->getPathname();
                    }
                }
            }

            if (isset($map[$class]) && is_file($map[$class])) {
                require_once $map[$class];
            }
        });
    }
}
