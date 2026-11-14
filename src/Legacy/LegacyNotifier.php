<?php

/**
 * ============================================================================
 *  SISTEMA HEREDADO DEL LABORATORIO
 *  Patrón aplicado: ADAPTER (estructural)
 * ============================================================================
 *
 *  CONTEXTO: el laboratorio ya tenia un sistema de avisos hecho por otro
 *  proveedor. No lo podemos modificar: lo usan tambien Recepcion y Facturacion.
 *
 *  ✅ SOLUCIÓN: LegacyNotifier se deja intacto (clase de terceros).
 *     LegacyNotifierAdapter implementa NUESTRO contrato Notification y
 *     traduce la llamada a sendMessage(). La incompatibilidad queda
 *     encerrada en un único archivo.
 * ============================================================================
 */

/**
 * Clase de terceros. No se modifica: es código del proveedor.
 */
class LegacyNotifier
{
    /** Método original del proveedor. No lo podemos cambiar. */
    public function sendMessage(string $text): void
    {
        echo "[LEGACY] {$text}<br>";
    }
}
