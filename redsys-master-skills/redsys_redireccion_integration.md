# Redsys - Integración por Redirección (Documentación Oficial Completa)

> Fuente: https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-tipos-de-integracion/desarrolladores-redireccion/

## Descripción General

La integración por **Redirección** es la más sencilla de implementar. El comercio redirige al cliente a la pasarela de pago de Redsys, donde introduce los datos de su tarjeta en un entorno seguro. Redsys gestiona tanto la autorización como la autenticación.

## Flujo de Funcionamiento

1. El titular selecciona los productos en el comercio.
2. El comercio redirige la sesión del navegador del cliente a la URL de entrada de Redsys con los parámetros identificativos de la operación.
3. El TPV Virtual informa al comercio del resultado vía **notificación HTTP POST** (a `Ds_Merchant_MerchantURL`).
4. Redsys devuelve la sesión del navegador del cliente al comercio (a `Ds_Merchant_URLOK` o `Ds_Merchant_URLKO`).

## Características

- **Fácil implementación**: Sólo preparar unos parámetros básicos y enviarlos.
- **Experiencia conocida**: Misma pasarela usada por miles de comercios en España.
- **Sin PCI-DSS**: Redsys se ocupa de todo el tratamiento de datos de tarjeta.
- **Todos los métodos de pago**: En la pantalla de pago se muestran todos los métodos activos en el TPV (tarjeta, Bizum, Apple Pay, Google Pay...).

## URLs de Envío

### Entorno de Pruebas (Test/Sandbox)
```
https://sis-t.redsys.es:25443/sis/realizarPago
```

### Entorno de Producción (Real)
```
https://sis.redsys.es/sis/realizarPago
```

## Petición - Parámetros del Formulario POST

El formulario POST contiene **3 campos**:

| Campo | Descripción |
|-------|-------------|
| `Ds_SignatureVersion` | Versión del algoritmo de firma. Valor: `HMAC_SHA512_V2` |
| `Ds_MerchantParameters` | JSON codificado en Base64URL con los datos de la operación |
| `Ds_Signature` | Firma HMAC SHA-512 de `Ds_MerchantParameters` |

### JSON de Ds_MerchantParameters (antes de Base64)

```json
{
  "DS_MERCHANT_ORDER": "00014338z744",
  "DS_MERCHANT_MERCHANTCODE": "999008881",
  "DS_MERCHANT_TERMINAL": "001",
  "DS_MERCHANT_CURRENCY": "978",
  "DS_MERCHANT_TRANSACTIONTYPE": "0",
  "DS_MERCHANT_AMOUNT": "249",
  "DS_MERCHANT_MERCHANTURL": "http://www.prueba.com/urlNotificacion.php",
  "DS_MERCHANT_URLOK": "http://www.prueba.com/urlOK.php",
  "DS_MERCHANT_URLKO": "http://www.prueba.com/urlKO.php"
}
```

### Formulario HTML de ejemplo

```html
<form name="from" action="https://sis-t.redsys.es:25443/sis/realizarPago" method="POST">
  <input type="hidden" name="Ds_SignatureVersion" value="HMAC_SHA512_V2" />
  <input type="hidden" name="Ds_MerchantParameters" value="BASE64URL_ENCODED_JSON" />
  <input type="hidden" name="Ds_Signature" value="FIRMA_HMAC_SHA512" />
</form>
```

## Respuesta del TPV Virtual

### Notificación HTTP POST (a Ds_Merchant_MerchantURL)

Redsys envía un POST con los mismos 3 campos: `Ds_MerchantParameters`, `Ds_Signature`, `Ds_SignatureVersion`.

**Pasos obligatorios para procesar la notificación:**
1. **Verificar la firma**: Firmar los parámetros recibidos y comparar con `Ds_Signature` recibida.
2. **Decodificar** `Ds_MerchantParameters` desde Base64.
3. **Evaluar `Ds_Response`**: Si es `0000`, la operación fue correcta.

### Ejemplo de respuesta decodificada

```json
{
  "Ds_Date": "10%2F12%2F2019",
  "Ds_Hour": "09%3A41",
  "Ds_SecurePayment": "0",
  "Ds_Card_Type": "D",
  "Ds_Card_Country": "724",
  "Ds_Amount": "1000",
  "Ds_Currency": "978",
  "Ds_Order": "1575967259",
  "Ds_MerchantCode": "999008881",
  "Ds_Terminal": "1",
  "Ds_Response": "0000",
  "Ds_TransactionType": "0",
  "Ds_ConsumerLanguage": "1",
  "Ds_AuthorisationCode": "372663",
  "Ds_Card_Brand": "2"
}
```

### Retorno de Navegación (HTTP GET a URLOK/URLKO)

- **NUNCA** usar los datos del GET para validar pedidos.
- Usar SOLO la notificación HTTP POST para confirmar operaciones.
- Si no se establecen URLOK/URLKO, la ventana se cerrará al pulsar "Continuar".

## Personalización de la Pasarela

Se gestiona desde el **Portal de Administración del TPV Virtual**:

### Niveles de personalización
- **Nivel 1**: Colores principales, botones y elementos de la página.
- **Nivel 2**: Logo del comercio (máx. 30 kB).
- **Nivel 3**: CSS personalizado completo.

### Personalización múltiple
- **Asignación por porcentaje**: Dos personalizaciones activas con porcentajes configurables.
- **Asignación directa**: Parámetro `Ds_Merchant_PersoCode` en cada operación individual.

### Idioma
Se controla con el parámetro `Ds_Merchant_ConsumerLanguage`.

## Notas Importantes para PrestaShop

- El `Ds_Merchant_Order` debe ser **único** por operación, entre 4-12 caracteres alfanuméricos (los 4 primeros numéricos).
- El `Ds_Merchant_Amount` se envía en **céntimos** (sin decimales): 10.50€ = `1050`.
- La `Ds_Merchant_Currency` para EUR es `978`.
- Los tipos de transacción: `0` (Autorización), `1` (Preautorización), `7` (Autenticación).
