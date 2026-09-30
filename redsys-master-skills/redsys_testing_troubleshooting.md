---
name: redsys_testing_troubleshooting
description: Entorno de pruebas, tarjetas de test, depuración de errores de firma y códigos de respuesta del TPV.
---

# Redsys Testing & Troubleshooting

Guía para validar la integración y resolver los problemas más comunes durante el desarrollo.

## 1. Tarjetas de Prueba (V2)
Utiliza estas tarjetas solo en el entorno de **SIS-T** (Sandbox):
- **VISA (Frictionless)**: `4918019160034602`, Caducidad: Cualquiera, CVV2: Cualquiera excepto 999.
- **VISA (Challenge)**: `4918019199883839`, Pide firma MSISDN SMS.
- **MASTERCARD (DCC)**: `5424180805648190`, Prueba pagos en otra divisa.
- **Soft Decline (0195)**: `4548817212493017`, Simula la denegación para pedir SCA.

## 2. Depuración del error de firma (SIS0042)
Es el error más común. Verifica:
1. **Clave de Encriptación**: ¿Es la V2 (SHA512)? Muchas veces se intenta usar la antigua V1.
2. **Espacios en blanco**: Las librerías cURL a veces transforman `+` en espacios. Codifica siempre el HMAC en Base64 final.
3. **Puntualizaciones JSON**: Asegúrate de que el JSON no contenga caracteres de escape innecesarios o saltos de línea.
4. **Base64 URL-safe**: Los caracteres `+`, `/` y `=` deben ser sustituidos por `-`, `_` y nada (o mantener `=` si la librería lo requiere).

## 3. Códigos de Respuesta Importantes (`Ds_Response`)
- `0000` a `0099`: **Aceptada**.
- `0101`: Tarjeta caducada.
- `0102`: Tarjeta en lista negra.
- `0180`: Tarjeta no permitida.
- `0184`: Error en la autenticación del titular (fallo de firma del usuario).
- `0191`: Fecha de caducidad errónea.
- `0195`: Requiere SCA (Soft Decline).
- `0900`: Devolución aceptada.
- `9912`: Emisor no disponible (Timeout).

## 4. IPN (Notificación Online)
Si el TPV marca "Correcta" pero PrestaShop no crea el pedido:
- Revisa los logs de acceso de tu servidor.
- ¿La URL es accesible? (Sin `maintenance mode`, sin bloqueos de firewall).
- ¿Respondes con un HTTP 200 OK?
- ¿La firma de la notificación coincide con la calculada?

## 5. Timeouts
Si Redsys no responde, espera al menos **40-50 segundos** antes de cerrar la conexión en tu servidor (cURL timeout).
