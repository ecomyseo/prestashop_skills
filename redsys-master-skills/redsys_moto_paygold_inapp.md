# Redsys - Operativa MO/TO, PayGold e InApp (Documentación Oficial)

> Fuentes:
> - https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-tipos-de-integracion/desarrolladores-moto/
> - https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-tipos-de-integracion/desarrolladores-paygold/
> - https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-tipos-de-integracion/desarrolladores-inapp/

---

## MO/TO: Pago Manual (Mail Order / Telephone Order)

### Descripción
Pago orientado a **callcenters**. El cliente NO está presente. El comercio introduce los datos de tarjeta facilitados por teléfono o email. **Sin autenticación** (operación no segura).

### Operaciones Disponibles
- **Autorización**: `Ds_Merchant_TransactionType = "0"` + `Ds_Merchant_DirectPayment = "MOTO"`
- **Preautorización**: `Ds_Merchant_TransactionType = "1"` + `Ds_Merchant_DirectPayment = "MOTO"`

### Formas de Ejecución

#### 1. Portal de Administración del TPV Virtual
Menú lateral → "Operación Manual" → Seleccionar terminal, tarjeta, importe.

#### 2. Vía REST

```json
{
  "DS_MERCHANT_AMOUNT": "1000",
  "DS_MERCHANT_CURRENCY": "978",
  "DS_MERCHANT_CVV2": "123",
  "DS_MERCHANT_DIRECTPAYMENT": "moto",
  "DS_MERCHANT_EXPIRYDATE": "XXXX",
  "DS_MERCHANT_MERCHANTCODE": "999008881",
  "DS_MERCHANT_ORDER": "1524494336",
  "DS_MERCHANT_PAN": "XXXXXXXXXXXXXXXXXX",
  "DS_MERCHANT_TERMINAL": "872",
  "DS_MERCHANT_TRANSACTIONTYPE": "0"
}
```

**Respuesta exitosa:**
```json
{
  "Ds_Response": "0000",
  "Ds_AuthorisationCode": "088767",
  "Ds_SecurePayment": "0"
}
```

#### 3. Por Redirección

Igual que redirección estándar pero con `DS_MERCHANT_DIRECTPAYMENT: "MOTO"`.

```json
{
  "DS_MERCHANT_ORDER": "1552565870",
  "DS_MERCHANT_MERCHANTCODE": "999008881",
  "DS_MERCHANT_TERMINAL": "999",
  "DS_MERCHANT_CURRENCY": "978",
  "DS_MERCHANT_DIRECTPAYMENT": "MOTO",
  "DS_MERCHANT_TRANSACTIONTYPE": "0",
  "DS_MERCHANT_AMOUNT": "1000",
  "DS_MERCHANT_MERCHANTURL": "http://www.prueba.com/urlNotificacion.php",
  "DS_MERCHANT_URLOK": "http://www.prueba.com/urlOK.php",
  "DS_MERCHANT_URLKO": "http://www.prueba.com/urlKO.php"
}
```

---

## PayGold: Pagos vía Email, SMS, WhatsApp o QR

### Descripción
Envía un **enlace de pago único** al cliente vía email, SMS, WhatsApp o código QR. El cliente paga en la pasarela de redirección de Redsys.

### Flujo
1. Comercio solicita pago al TPV Virtual con teléfono/email del cliente.
2. TPV Virtual envía SMS/email con enlace.
3. Cliente pulsa enlace → pasarela de pago por redirección.
4. Se solicita autenticación del titular.
5. Se notifica resultado al comercio (opcional, si configurado).

### Características
- Enlace con caducidad configurable (por defecto 24h).
- Se puede solicitar generación de referencia/token (para suscripciones).
- Disponible desde el Portal de Administración o vía REST.
- Soporte para WhatsApp y código QR.

### Datos necesarios (Portal)
- Código de pedido
- Número de comercio y terminal
- Importe (con decimales usando punto)
- Divisa
- Descripción
- Caducidad del enlace
- URL de notificación online (opcional)
- Nombre y datos del comprador
- Email y/o teléfono del destinatario

### PayGold + Tokenización
Si se marca "Almacenar de forma segura la tarjeta del cliente para pagos posteriores", el resultado incluirá la referencia generada en la notificación online.

---

## InApp: Pago dentro de Aplicación Móvil

### Descripción
Bibliotecas y SDKs para integrar pagos directamente dentro de aplicaciones móviles **sin que el cliente abandone la app**.

### Modalidades
- **Pago directo**: Campos de pago integrados con estilo personalizado. Requiere gestionar captura de datos y PCI-DSS.
- **Pago por webview**: Pasarela de redirección dentro del navegador incrustado de la app. Sin necesidad de gestionar datos de tarjeta.

### Plataformas
- [Integración para Android](https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-operativa/integracion-inapp-para-dispositivos-android/)
- [Integración para iOS](https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-operativa/integracion-inapp-para-dispositivos-ios/)

### SDKs
Disponibles en el [Área de Descargas de Redsys](https://pagosonline.redsys.es/desarrolladores-inicio/integrate-con-nosotros/area-de-descargas-y-documentacion/).
