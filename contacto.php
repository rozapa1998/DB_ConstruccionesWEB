<?php
// Envía las consultas del formulario de la web a Info@construccionesdb.com
// Responde JSON: {"ok":true} solo si el mail salió. Si no, la página abre el correo del visitante.
$DESTINO   = 'Info@construccionesdb.com';
$REMITENTE = 'web@construccionesdb.com'; // casilla del mismo dominio (no hace falta que exista para enviar)

$esAjax = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
function responder($ok, $esAjax) {
  if ($esAjax) { header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok' => $ok]); }
  else { header('Location: ./?' . ($ok ? 'enviado=1' : 'error=1') . '#contacto'); }
  exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); responder(false, $esAjax); }
if (!empty($_POST['bot-field'])) responder(true, $esAjax); // spam: se descarta en silencio

$limpiar = function($v, $max) { $v = trim(str_replace(["\r", "\n"], ' ', (string)$v)); return mb_substr($v, 0, $max); };
$nombre  = $limpiar($_POST['nombre'] ?? '', 120);
$email   = $limpiar($_POST['email'] ?? '', 160);
$mensaje = mb_substr(trim((string)($_POST['mensaje'] ?? '')), 0, 5000);
if ($nombre === '' || $mensaje === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) responder(false, $esAjax);

$asunto = '=?UTF-8?B?' . base64_encode('Consulta desde la web — ' . $nombre) . '?=';
$cuerpo = "Nueva consulta desde construccionesdb.com\n\nNombre: $nombre\nEmail: $email\n\n$mensaje\n";
$headers = "From: Don Bosco Web <$REMITENTE>\r\nReply-To: $nombre <$email>\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n";
$ok = @mail($DESTINO, $asunto, $cuerpo, $headers, '-f' . $REMITENTE);
responder($ok, $esAjax);
