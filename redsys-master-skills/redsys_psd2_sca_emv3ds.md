# Redsys - PSD2, SCA y EMV 3-D Secure (Documentación Oficial)

> Fuente: https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-operativa/autenticacion-reforzada-sca-y-normativa-psd2/

## PSD2 y Autenticación Reforzada (SCA)

La **SCA** (Strong Customer Authentication) es obligatoria para la mayoría de transacciones online en el EEE. Requiere al menos **2 factores** de autenticación:

1. **Algo que sabe** (contraseña, PIN)
2. **Algo que tiene** (tarjeta, token, móvil)
3. **Algo que es** (huella dactilar, reconocimiento facial)

## Exenciones a la SCA

### TRA — Transacciones de Bajo Riesgo
Exención basada en umbrales de fraude del PSP:
- `< 100€` si ratio de fraude < 0.13%
- `< 250€` si ratio de fraude < 0.06%
- `< 500€` si ratio de fraude < 0.01%

**Parámetro**: `DS_MERCHANT_EXCEP_SCA = "TRA"`

### LWV — Transacciones de Bajo Valor (< 30€)
Exentas de SCA, pero el banco puede requerir autenticación si:
- Se ha utilizado más de 5 veces desde la última autenticación.
- La suma de importes exentos supera los 100€.

**Parámetro**: `DS_MERCHANT_EXCEP_SCA = "LWV"`

### MIT — Transacciones Iniciadas por el Comercio
Operaciones sin titular presente. Requiere:
- Token/referencia generada con autenticación previa.
- Mandato del titular (autorización expresa).

**Parámetro**: `DS_MERCHANT_EXCEP_SCA = "MIT"`

### COR — Pagos Recurrentes / Suscripciones
Exentos tras la primera transacción autenticada, si importe y frecuencia son constantes.

### ATD — Beneficiarios de Confianza
Clientes que designan al comercio como "de confianza" en su banco.

### Otras Exenciones
- **MOTO**: Ventas telefónicas fuera del alcance de SCA.
- **Tarjetas corporativas**: Canales no al alcance del consumidor medio.

## Protocolo EMV 3-D Secure

Protocolo de autenticación desarrollado por EMVCo (AMEX, Discover, JCB, Mastercard, UnionPay, VISA).

### Flujo en Integración REST

1. **iniciaPeticion**: Obtener datos de la tarjeta y capacidad de autenticación.
2. **3DSMethod**: Captura de información del dispositivo por el emisor.
3. **Primer trataPeticion**: Envío de la operación con datos del navegador.
4. **Respuesta**:
   - **Frictionless**: Operación autorizada directamente sin challenge.
   - **ChallengeRequest**: Se requiere autenticación del titular.
5. **Autenticación (Challenge)**: El titular verifica identidad.
6. **Segundo trataPeticion**: Envío del resultado de autenticación.

### Campos EMV3DS del Navegador

```json
{
  "threeDSInfo": "AuthenticationData",
  "protocolVersion": "2.2.0",
  "browserJavascriptEnabled": "true",
  "browserAcceptHeader": "text/html,...",
  "browserUserAgent": "Mozilla/5.0...",
  "threeDSServerTransID": "uuid...",
  "browserJavaEnabled": "false",
  "browserLanguage": "ES-es",
  "browserColorDepth": "24",
  "browserScreenHeight": "1250",
  "browserScreenWidth": "1320",
  "browserTZ": "52",
  "threeDSCompInd": "Y",
  "notificationURL": "https://..."
}
```

### En Integración por Redirección
El proceso de autenticación es **transparente** para el comercio. Redsys gestiona todo internamente.

## Consultar Exenciones Disponibles

En `iniciaPeticion`, enviar `DS_MERCHANT_EXCEP_SCA: "Y"`. La respuesta incluirá el campo `Ds_Excep_SCA` con las exenciones soportadas.

Ejemplo: `"Ds_Excep_SCA": "LWV;TRA[30.0];COR;MIT;ATD"`
