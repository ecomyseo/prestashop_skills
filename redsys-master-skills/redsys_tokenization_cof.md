# Redsys - Tokenización y Operativa COF (Documentación Oficial)

> Fuente: https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-funcionalidades-avanzadas/tokenizacion/

## ¿Qué es la Tokenización?

La tokenización permite almacenar datos de tarjeta de forma segura. Redsys genera un **token/referencia** único asociado a la tarjeta que reemplaza los datos reales en operaciones sucesivas.

## Credential on File (COF)

Transacciones donde el comercio usa datos de tarjeta del titular con autorización explícita.

### Dos Opciones

1. **Redsys gestiona la referencia**: Sin PCI-DSS. El token se guarda en servidores de Redsys.
2. **El comercio gestiona los datos**: Requiere PCI-DSS. El comercio almacena PAN, caducidad, etc.

## Proceso de Generación del Token

1. **Solicitud**: El cliente introduce datos de tarjeta normalmente.
2. **Generación**: Redsys crea un token único asociado.
3. **Almacenamiento**: El token se devuelve junto con el resultado de la operación.
4. **Uso posterior**: El token sustituye los datos de tarjeta en futuras transacciones.

> El token solo funciona con el **mismo comercio y terminal** que lo generó.

## Tipos de Operativas COF

### Operativas Principales

| Tipo | Descripción | DS_MERCHANT_COF_TYPE |
|------|-------------|---------------------|
| **Installments** | Pago aplazado: importe fijo, intervalo definido | `I` |
| **Recurring** | Pago recurrente: importe variable, intervalo definido | `R` |

### Operativas Especiales

| Tipo | Descripción | DS_MERCHANT_COF_TYPE |
|------|-------------|---------------------|
| **Reauthorisation** | Envíos parciales o ampliación de servicio | `H` |
| **Resubmission** | Reintentar operación denegada por saldo | `E` |
| **Delayed** | Cargos posteriores (minibar hotel, etc.) | `D` |
| **Incremental** | Gastos adicionales no contemplados | `M` |
| **No Show** | Cobro por no presentarse a servicio contratado | `N` |

## Parámetros Clave

### Primera Operación (generación de token)

```json
{
  "DS_MERCHANT_IDENTIFIER": "REQUIRED",
  "DS_MERCHANT_COF_INI": "S",
  "DS_MERCHANT_COF_TYPE": "R"
}
```

- `DS_MERCHANT_IDENTIFIER = "REQUIRED"`: Solicitar generación de referencia.
- `DS_MERCHANT_COF_INI = "S"`: Primera operación COF.
- `DS_MERCHANT_COF_TYPE`: Tipo de operativa (R, I, etc.).

### Operaciones Sucesivas (uso del token)

```json
{
  "DS_MERCHANT_IDENTIFIER": "token_generado_previamente",
  "DS_MERCHANT_COF_INI": "N",
  "DS_MERCHANT_COF_TYPE": "R",
  "DS_MERCHANT_COF_TXNID": "id_transaccion_original"
}
```

- `DS_MERCHANT_COF_INI = "N"`: No es primera operación COF.
- `DS_MERCHANT_COF_TXNID`: ID de la transacción original que generó el token.

## Operaciones CIT (Iniciadas por el Cliente)

- El titular está **presente** y activamente involucrado.
- Se crea el token o se usa uno existente.
- Ejemplo: **Pago en 1-clic**.

## Operaciones MIT (Iniciadas por el Comercio)

- El titular **NO está presente**.
- Requiere token **generado previamente** con autenticación.
- Ejemplo: Cobro de **suscripciones**.

```json
{
  "DS_MERCHANT_IDENTIFIER": "token",
  "DS_MERCHANT_COF_INI": "N",
  "DS_MERCHANT_DIRECTPAYMENT": "true",
  "DS_MERCHANT_EXCEP_SCA": "MIT"
}
```

## Consideraciones

- Los tokens están **ligados al comercio y terminal** que los generó.
- La primera operación que genera el token **siempre debe ser autenticada**.
- El titular debe haber dado **autorización explícita** (mandato).
- Es crucial para cumplimiento de **PSD2** identificar correctamente operaciones MIT.
