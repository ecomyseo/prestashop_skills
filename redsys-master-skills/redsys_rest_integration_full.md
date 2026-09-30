# Redsys - Integración REST Completa (Documentación Oficial)

> Fuente: https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-tipos-de-integracion/desarrolladores-rest/

## Descripción General

La integración vía **REST** proporciona la máxima libertad y control total sobre el flujo de pago. El comercio se ocupa de recoger datos de tarjeta, gestionar la autenticación EMV3DS y procesar las respuestas.

## Características

- **Control total**: El comercio gestiona campos, recogida de datos y envío a servidores Redsys.
- **Requiere PCI-DSS** si se manejan datos de tarjeta directamente.
- **No requiere PCI-DSS** si se usa `idOper` de inSite, o para operaciones sin datos de tarjeta (devoluciones, confirmaciones).
- **Ideal para**: Operaciones MIT, devoluciones, anulaciones, confirmaciones, PayGold vía REST.

## URLs / Endpoints

### Entorno de Pruebas (Sandbox)
```
iniciaPeticion: https://sis-t.redsys.es:25443/sis/rest/iniciaPeticionREST
trataPeticion:  https://sis-t.redsys.es:25443/sis/rest/trataPeticionREST
```

### Entorno de Producción (Real)
```
iniciaPeticion: https://sis.redsys.es/sis/rest/iniciaPeticionREST
trataPeticion:  https://sis.redsys.es/sis/rest/trataPeticionREST
```

## Flujo Completo de una Autorización REST con EMV3DS v2

### Paso 1: iniciaPeticion — Solicitar datos de tarjeta

Enviar petición REST para obtener información sobre capacidad de autenticación de la tarjeta.

```json
{
  "DS_MERCHANT_ORDER": "1675861721",
  "DS_MERCHANT_MERCHANTCODE": "999008881",
  "DS_MERCHANT_TERMINAL": "1",
  "DS_MERCHANT_TRANSACTIONTYPE": "0",
  "DS_MERCHANT_PAN": "4548810000000003",
  "DS_MERCHANT_EXPIRYDATE": "4912",
  "DS_MERCHANT_CVV2": "123",
  "DS_MERCHANT_CURRENCY": "978",
  "DS_MERCHANT_AMOUNT": "0000002000",
  "DS_MERCHANT_EMV3DS": {
    "threeDSInfo": "CardData"
  },
  "DS_MERCHANT_EXCEP_SCA": "Y"
}
```

**Respuesta del SIS:**

```json
{
  "Ds_Order": "1675861721",
  "Ds_MerchantCode": "999008881",
  "Ds_Terminal": "1",
  "Ds_TransactionType": "0",
  "Ds_EMV3DS": {
    "protocolVersion": "2.2.0",
    "threeDSServerTransID": "d7433c2d-f44d-48da-a3a3-e883f6a82fe9",
    "threeDSMethodURL": "https://sis-d.redsys.es:25443/sis-simulador-web/threeDsMethod.jsp",
    "threeDSInfo": "CardConfiguration"
  },
  "Ds_Excep_SCA": "LWV;TRA[30.0];COR;MIT;ATD",
  "Ds_Card_PSD2": "Y"
}
```

**Campos relevantes de la respuesta:**
- `protocolVersion`: Versión de 3DSecure de la tarjeta.
- `threeDSServerTransID`: ID único de la transacción 3DS.
- `threeDSMethodURL`: URL para ejecución del 3DSMethod.
- `Ds_Excep_SCA`: Exenciones soportadas por la tarjeta.
- `Ds_Card_PSD2`: Si la tarjeta está enrolada en PSD2.

### Paso 2: Ejecución del 3DSMethod

Ejecutar el `threeDSMethodURL` en un iframe oculto para captura de información del dispositivo.

### Paso 3: Primer trataPeticion — Envío de la operación

```json
{
  "DS_MERCHANT_ORDER": "1675861721",
  "DS_MERCHANT_MERCHANTCODE": "999008881",
  "DS_MERCHANT_TERMINAL": "1",
  "DS_MERCHANT_TRANSACTIONTYPE": "0",
  "DS_MERCHANT_CURRENCY": "978",
  "DS_MERCHANT_PAN": "4548810000000003",
  "DS_MERCHANT_EXPIRYDATE": "4912",
  "DS_MERCHANT_AMOUNT": "2000",
  "DS_MERCHANT_CVV2": "123",
  "DS_MERCHANT_EMV3DS": {
    "threeDSInfo": "AuthenticationData",
    "protocolVersion": "2.2.0",
    "browserJavascriptEnabled": "true",
    "browserAcceptHeader": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "browserUserAgent": "Mozilla/5.0...",
    "threeDSServerTransID": "d7433c2d-f44d-48da-a3a3-e883f6a82fe9",
    "browserJavaEnabled": "false",
    "browserLanguage": "ES-es",
    "browserColorDepth": "24",
    "browserScreenHeight": "1250",
    "browserScreenWidth": "1320",
    "browserTZ": "52",
    "threeDSCompInd": "Y",
    "notificationURL": "https://mi-tienda.com/redsys-notification"
  }
}
```

**Respuesta posible — ChallengeRequest:**

```json
{
  "Ds_EMV3DS": {
    "threeDSInfo": "ChallengeRequest",
    "protocolVersion": "2.2.0",
    "acsURL": "https://..../authenticationRequest.jsp",
    "creq": "eyJ0aHJlZURTU2VydmVyVHJhbnNJRC..."
  }
}
```

**Respuesta posible — Frictionless (sin challenge):**
- Directamente `Ds_Response: 0000` con código de autorización.

### Paso 4: Challenge (si se requiere)

Enviar el `creq` al `acsURL` en un iframe/popup para que el titular se autentique.

### Paso 5: Segundo trataPeticion

Recibir resultado del challenge y enviar al TPV Virtual para completar la operación.

## Operaciones MIT (Merchant-Initiated Transactions)

Para suscripciones y pagos sin titular presente:

```json
{
  "DS_MERCHANT_ORDER": "00014338z744",
  "DS_MERCHANT_MERCHANTCODE": "999008881",
  "DS_MERCHANT_TERMINAL": "001",
  "DS_MERCHANT_CURRENCY": "978",
  "DS_MERCHANT_TRANSACTIONTYPE": "0",
  "DS_MERCHANT_AMOUNT": "249",
  "DS_MERCHANT_IDENTIFIER": "d20712480feef6b4ec3480fa091af91d1b45ba90",
  "DS_MERCHANT_COF_INI": "N",
  "DS_MERCHANT_DIRECTPAYMENT": "true",
  "DS_MERCHANT_EXCEP_SCA": "MIT"
}
```

**Requisitos MIT:**
- Token/referencia de tarjeta previamente generado con autenticación.
- `DS_MERCHANT_COF_INI`: `N` (no es primera operación COF).
- `DS_MERCHANT_DIRECTPAYMENT`: `true`.
- `DS_MERCHANT_EXCEP_SCA`: `MIT`.

## Uso de idOper (desde inSite)

Si se usa `idOper` generado por inSite, sustituir `PAN`, `ExpiryDate` y `CVV` por `Ds_Merchant_idOper`. No requiere PCI-DSS.

## Cabecera HTTP

Todas las peticiones REST deben incluir:
```
Content-Type: application/json
```
