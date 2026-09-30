# Redsys - Apple Pay, Google Pay y Bizum (Documentación Oficial)

> Fuentes:
> - https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-otros-metodos-de-pago/otros-metodos-de-pago-apple-pay/
> - https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-otros-metodos-de-pago/otros-metodos-de-pago-google-pay/
> - https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-otros-metodos-de-pago/desarrolladores-bizum-1/

---

## Bizum

### Descripción
Pago instantáneo, fácil y seguro con el móvil. El cliente introduce su número de teléfono y clave Bizum.

### Integración
Se redirige al cliente a la pasarela de Redsys con `Ds_Merchant_Paymethods = "z"` (z minúscula).

**URLs**: Las mismas que en redirección.

### Pruebas
- Importe máximo en test: **10€**.
- PIN para pruebas: `1234`
- Código SMS para pruebas: `123456`

### Materiales
Descargar [paquete de materiales de Bizum](https://pagosonline.redsys.es/wp-content/uploads/2023/06/mats-bizum.zip).

---

## Apple Pay

### Modalidad 1: Por Redirección (sin desarrollo)
Solicitar alta del método de pago a la entidad bancaria. Aparecerá automáticamente en la pantalla de pago. Se muestra solo en dispositivos/navegadores compatibles (Safari en macOS).

Para forzar X-PAY: `Ds_Merchant_PayMethods = "xpay"`.

### Modalidad 2: Integración Directa

#### Requisitos de Enrolamiento
1. Crear **MerchantID** en la consola de Apple.
2. Crear **Merchant Identity Certificate** con OpenSSL:
   ```bash
   openssl req -new -newkey rsa:2048 -nodes -keyout merchant_id.key -out merchant_id.csr -subj '/O=NombreComercio /C=ES'
   ```
3. Generar **Certificate Signing Request (CSR)** con curva elíptica.
4. **Registrar dominios** en Apple Developer y verificar con archivo descargado.

#### Mostrar Botón Apple Pay

```html
<script src="https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js"></script>
<apple-pay-button buttonstyle="black" type="buy" locale="es-ES" onclick="buttonClicked()">
```

#### Verificar Compatibilidad

```javascript
if (window.ApplePaySession) {
    var promise = ApplePaySession.canMakePaymentsWithActiveCard(merchantIdentifier);
    promise.then(function(canMakePayments) {
        document.getElementById("box").style.display = "block";
    });
}
```

#### Crear Sesión de Pago

```javascript
const request = {
    "countryCode": "ES",
    "currencyCode": "EUR",
    "merchantCapabilities": ["supports3DS"],
    "supportedNetworks": ["visa", "masterCard", "amex", "discover"],
    "total": { "label": "Nombre Comercio", "type": "final", "amount": "4.99" }
};
const session = new ApplePaySession(3, request);
session.begin();
```

#### Enviar Token a Redsys

```json
{
  "DS_MERCHANT_AMOUNT": "145",
  "DS_MERCHANT_CURRENCY": "978",
  "DS_MERCHANT_MERCHANTCODE": "999008881",
  "DS_MERCHANT_ORDER": "1446068581",
  "DS_MERCHANT_TERMINAL": "1",
  "DS_MERCHANT_TRANSACTIONTYPE": "0",
  "DS_XPAYDATA": "TOKEN_APPLE_PAY_EN_HEX",
  "DS_XPAYTYPE": "Apple",
  "DS_XPAYORIGEN": "InApp"
}
```

#### Con Datos Descifrados

```json
{
  "DS_XPAYDECODEDDATA": {
    "cryptogram": "AgAAAAAABk4DWZ4C28yUQAAAAAA=",
    "eciInd": "05",
    "expirationDate": "2612",
    "token": "489537XXXXXX3478"
  },
  "DS_XPAYTYPE": "Apple",
  "DS_XPAYORIGEN": "InApp"
}
```

---

## Google Pay

### Modalidad 1: Por Redirección (sin desarrollo)
Solicitar alta a la entidad bancaria. Botón aparece automáticamente en pasarela.

Para forzar X-PAY: `Ds_Merchant_PayMethods = "xpay"`.

### Modalidad 2: Integración Directa

#### Configuración JavaScript

```javascript
const tokenizationSpecification = {
    type: 'PAYMENT_GATEWAY',
    parameters: {
        'gateway': 'redsys',
        'gatewayMerchantId': 'TU_FUC'
    }
};

const allowedCardNetworks = ["AMEX", "DISCOVER", "JCB", "MASTERCARD", "VISA"];
const allowedCardAuthMethods = ["PAN_ONLY", "CRYPTOGRAM_3DS"];
```

#### Enviar Token a Redsys

```json
{
  "DS_MERCHANT_AMOUNT": "145",
  "DS_MERCHANT_CURRENCY": "978",
  "DS_MERCHANT_MERCHANTCODE": "999008881",
  "DS_MERCHANT_ORDER": "1446068581",
  "DS_MERCHANT_TERMINAL": "1",
  "DS_MERCHANT_TRANSACTIONTYPE": "0",
  "DS_XPAYDATA": "TOKEN_GOOGLE_PAY_EN_BASE64",
  "DS_XPAYTYPE": "Google",
  "DS_XPAYORIGEN": "InApp"
}
```

#### Con Datos Descifrados

```json
{
  "DS_XPAYDECODEDDATA": {
    "cryptogram": "AgAAAAAABk4DWZ4C28yUQAAAAAA=",
    "eciInd": "05",
    "expirationDate": "2612",
    "token": "489537******3478",
    "paymentMethod": "TOKENIZED_CARD"
  },
  "DS_XPAYTYPE": "Google",
  "DS_XPAYORIGEN": "InApp"
}
```

> Si `paymentMethod = "CARD"` → Se requiere autenticación → Añadir `DS_MERCHANT_EMV3DS` sin `protocolVersion` ni `threeDSServerTransID`.

### Errores Comunes Google Pay

| Error | Causa | Solución |
|-------|-------|----------|
| SIS0499 (9499) | Gateway mal indicado / No en producción / Base64 incorrecto | Verificar gateway='redsys', environment='PRODUCTION', y formato base64 |
| SIS0754 / 9998 | Autenticación requerida con PAN_ONLY | Enviar a redirección o añadir `DS_MERCHANT_EMV3DS` |

### Puesta en Producción
1. Solicitar acceso a producción desde [portal de Google](https://developers.google.com/pay/api/web/guides/test-and-deploy/request-prod-access).
2. Google revisa y aprueba la integración.
