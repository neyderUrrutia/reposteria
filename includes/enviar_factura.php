<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarFactura($correo, $nombre, $pedido_id, $total)
{

    $mail = new PHPMailer(true);

    try {

        // CONFIGURACIÓN SMTP
        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';

        $mail->SMTPAuth = true;

        // TU CORREO
        $mail->Username = 'urrutianeyder002@gmail.com';

        // TU CLAVE DE APLICACIÓN
        $mail->Password = 'vwkc kpms ikyq kllf';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;

        // REMITENTE
        $mail->setFrom(
            'urrutianeyder002@gmail.com',
            'Atrato Dulce'
        );

        // DESTINATARIO
        $mail->addAddress(
            $correo,
            $nombre
        );

        // UTF8
        $mail->CharSet = 'UTF-8';

        // HTML
        $mail->isHTML(true);

        // ASUNTO
        $mail->Subject = 'Factura de tu pedido - Atrato Dulce';

        // MENSAJE
        $mail->Body = '

        <div style="font-family:Arial;padding:20px">

            <h2 style="color:#c0703a;">
                Gracias por tu compra 🍰
            </h2>

            <p>
                Hola <strong>' . $nombre . '</strong>,
            </p>

            <p>
                Hemos recibido tu pedido correctamente.
            </p>

            <hr>

            <p>
                <strong>ID Pedido:</strong>
                #' . $pedido_id . '
            </p>

            <p>
                <strong>Total:</strong>
                $' . number_format($total, 0, ",", ".") . '
            </p>

            <p>
                <strong>Método de pago:</strong>
                Efectivo
            </p>

            <hr>

            <p style="color:#666;">
                Atrato Dulce 🧁
            </p>

        </div>
        ';

        // ENVIAR
        $mail->send();

    } catch (Exception $e) {

        error_log(
            "Error enviando factura: " . $mail->ErrorInfo
        );
    }
}