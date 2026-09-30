---
name: redsys_rest_api
description: Guía avanzada para el uso de la API REST de Redsys, gestión de tokens, devoluciones y confirmaciones server-to-server.
---

# Redsys Advanced REST API

La API REST permite realizar operaciones complejas de servidor a servidor sin intervención del usuario o utilizando datos previamente capturados.

## 1. Operaciones Disponibles
Se definen mediante el campo `Ds_Merchant_TransactionType`:
- `0`: **Pago directo** (requiere datos de tarjeta o IDOPER).
- `1`: **Pre-autorización**. Bloquea el saldo en la tarjeta del cliente durante 7 días.
- `2`: **Confirmación de Pre-autorización**. Convierte el bloqueo en cargo real.
- `3`: **Devolución (Refund)**. Abona el dinero al cliente. Requiere el número de pedido original.
- `9`: **Anulación (Cancellation)**. Cancela una operación autorizada si no se ha cerrado el lote diario.

## 2. Tokenización (Credentials on File - COF)
Permite almacenar la tarjeta para "pagos en un clic" o suscripciones recurrentes.

### Almacenamiento inicial (CIT - Customer Initiated Transaction):
- Enviar `DS_MERCHANT_IDENTIFIER = REQUIRED`.
- Definir `DS_MERCHANT_COF_TYPE`:
    - `R`: Recurrente (Suscripciones).
    - `I`: Fraccionado (Cuotas).
    - `H`: Reautorización.
    - `E`: Resubmission.
    - `D`: Delayed.
    - `M`: Incremental.
    - `N`: No Show.
    - `C`: Otras.
- Enviar `DS_MERCHANT_COF_INI = S`.

### Pago con Token (MIT o CIT posterior):
- Enviar el token recibido previamente en `DS_MERCHANT_IDENTIFIER`.
- Enviar `DS_MERCHANT_COF_INI = N`.
- Si el usuario no está presente (pago recurrente automático), se debe usar `DS_MERCHANT_DIRECTPAYMENT = true` y solicitar exención `MIT`.

## 3. Endpoints REST (2025)
- **Tratamiento de Petición**: `https://sis-t.redsys.es:25443/sis/rest/trataPeticionREST` (Pruebas).
- **Inicio de Petición (3DS)**: `https://sis-t.redsys.es:25443/sis/rest/iniciaPeticionREST` (Pruebas).

## 4. Estructura de la Petición
Se envía un JSON por POST con:
- `Ds_SignatureVersion`: `HMAC_SHA512_V1`.
- `Ds_MerchantParameters`: Datos en Base64.
- `Ds_Signature`: Firma HMAC.
- Header: `Content-Type: application/json`.

## 5. Implementación en PrestaShop
- **Cron Jobs**: Ideal para capturar confirmaciones de pre-autorizaciones automáticamente al enviar el pedido.
- **Backoffice**: Añadir botones de "Reembolso" en el detalle del pedido que llamen a la API REST.
- **Seguridad**: El servidor del comercio debe manejar los tokens con cuidado, aunque no vea los números de tarjeta reales. (PCI-DSS SAQ-D si se maneja el PAN directamente, SAQ-A si es via InSite).
