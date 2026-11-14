<?php

/**
 * Contrato de los observers de pedidos.
 *
 * El sujeto (OrderSubject) conoce esta interfaz, no las clases concretas:
 * agregar un aviso nuevo NO obliga a modificar al sujeto.
 */
interface OrderObserver
{
    public function update(Order $order): void;
}
