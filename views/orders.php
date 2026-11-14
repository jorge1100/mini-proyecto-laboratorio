<?php
/**
 * ============================================================================
 *  VISTA: listado de pedidos
 * ============================================================================
 *
 *  La vista NO consulta la base de datos, NO calcula totales y escapa toda
 *  la salida. Los datos llegan ya listos desde el controlador.
 * ============================================================================
 */
?>
<h1>Pedidos</h1>

<table border="1">
    <tr><th>ID</th><th>Paciente</th><th>Total</th></tr>

    <?php foreach ($pedidos as $pedido): ?>
        <tr>
            <td><?= (int) $pedido['id'] ?></td>
            <td><?= htmlspecialchars((string) $pedido['patient'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (float) $pedido['total'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>
