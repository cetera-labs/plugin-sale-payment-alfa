<?php
if (class_exists("\Sale\Payment")) {
    try {
        \Sale\Payment::addGateway('\SalePaymentAlfa\Gateway');
    } catch (\Exception $e) {
    }
}