<?php
/**
 * ============================================================================
 *  VISTA: pedido creado
 * ============================================================================
 *
 *  Solo presenta: recibe los datos ya calculados y los muestra escapados.
 * ============================================================================
 */
?>
<h1>Pedido creado</h1>
<p>Paciente: <?= htmlspecialchars((string) $pedido['patient'], ENT_QUOTES, 'UTF-8') ?></p>
<p>Total: $ <?= (float) $pedido['total'] ?></p>
<p><?= htmlspecialchars((string) $reporte, ENT_QUOTES, 'UTF-8') ?></p>
