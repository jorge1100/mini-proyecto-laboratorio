<?php
/**
 * ============================================================================
 *  VISTA: error
 * ============================================================================
 *
 *  Muestra el mensaje de error que devuelve la fachada, escapado.
 * ============================================================================
 */
?>
<h1>Pedido rechazado</h1>
<p><?= htmlspecialchars((string) $mensaje, ENT_QUOTES, 'UTF-8') ?></p>
