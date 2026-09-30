---
name: redsys_bizum_bizum
description: Integración específica para pagos con Bizum y consulta de compatibilidad Request To Pay (RTP).
---

# Redsys Bizum Integration

Bizum es el método de pago preferido en España y requiere parámetros específicos en Redsys.

## 1. Activación
A diferencia de las tarjetas, Bizum puede requerir una activación adicional en tu terminal por parte de la entidad financiera.

## 2. Parámetros Clave
- `Ds_Merchant_PayMethods`: Debe ser igual a `z` (minúscula).
- `Ds_Merchant_Bizum_MobileNumber`: (Opcional) Envía el teléfono del cliente con prefijo (ej: `+34600000000`) para que aparezca precargado en la interfaz de Bizum.

## 3. Request To Pay (RTP) vía REST
RTP permite al usuario recibir una notificación instantánea en su app bancaria para autorizar el pago.
### Consulta de disponibilidad:
Antes de mostrar Bizum como opción "Express", puedes consultar si el usuario soporta RTP:
- Endpoint: `https://sis-t.redsys.es:25443/sis/rest/RTP/checkRtpUsuario`
- Si `Ds_RtpStatus = OK`, puedes proceder al pago directo vía REST sin redirigir.

## 4. Tipos de Transacción Bizum
Aparte de la autorización normal (`0`), Bizum soporta:
- `7`: Solo autenticación (Doble flujo, Fase 1).
- `8`: Solo autorización (Doble flujo, Fase 2).
- `3`: Devolución.

## 5. Límites y Restricciones
- El tiempo máximo para completar una solicitud RTP suele ser de **30 minutos**.
- Bizum tiene sus propios límites internos de importes (mínimo 0.50€, máximo variable por banco).
- Se recomienda usar el logo oficial suministrado por Redsys para mejorar la confianza.
