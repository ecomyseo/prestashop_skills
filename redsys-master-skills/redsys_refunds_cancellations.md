# Redsys - Devoluciones y Anulaciones (Documentación Oficial)

> Fuente: https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-operativa/devolver-o-anular-un-pago/

---

## Devoluciones

Las devoluciones se ejecutan sobre operaciones **previamente autorizadas y completadas**. Pueden ser totales o parciales.

### Vía REST — Petición de Devolución

**Endpoint**: `trataPeticionREST`  
**TransactionType**: `3`

```json
{
  "DS_MERCHANT_ORDER": "1680071984",
  "DS_MERCHANT_MERCHANTCODE": "999008881",
  "DS_MERCHANT_TERMINAL": "1",
  "DS_MERCHANT_TRANSACTIONTYPE": "3",
  "DS_MERCHANT_CURRENCY": "978",
  "DS_MERCHANT_AMOUNT": "0000002000"
}
```

### Respuesta Exitosa

```json
{
  "Ds_Amount": "2000",
  "Ds_Currency": "978",
  "Ds_Order": "1680071984",
  "Ds_MerchantCode": "999008881",
  "Ds_Terminal": "1",
  "Ds_Response": "0900",
  "Ds_AuthorisationCode": "439764",
  "Ds_TransactionType": "2"
}
```

> **`Ds_Response = 0900`** → Devolución ejecutada correctamente.

### Notas Importantes
- Usar el **mismo número de pedido** de la operación original.
- Se pueden hacer devoluciones **parciales** (importe menor al original).
- Se pueden hacer **múltiples devoluciones parciales** hasta agotar el importe.

---

## Anulaciones

Las anulaciones se usan para corregir errores de forma inmediata (ej: BD no responde, producto no disponible).

### Tipos de Anulación y TransactionType

| Operación a Anular | TransactionType Anulación |
|---------------------|--------------------------|
| Autorización | `9` |
| Preautorización | `44` ó `45` |
| Devolución | `45` |
| Confirmación separada | `45` |

### Vía REST — Petición de Anulación

```json
{
  "DS_MERCHANT_ORDER": "7230",
  "DS_MERCHANT_MERCHANTCODE": "999008881",
  "DS_MERCHANT_TERMINAL": "249",
  "DS_MERCHANT_TRANSACTIONTYPE": "45",
  "DS_MERCHANT_CURRENCY": "978",
  "DS_MERCHANT_AMOUNT": "0000002000"
}
```

### Respuesta Exitosa

```json
{
  "Ds_Response": "0400",
  "Ds_AuthorisationCode": "103858",
  "Ds_TransactionType": "45"
}
```

> **`Ds_Response = 0400`** → Anulación correcta.

### Detalle por Tipo de Operación

| Tipo | Comportamiento |
|------|---------------|
| **Anulación de autorización** | Restituye importe cargado. Movimientos pueden no reflejarse en extracto. |
| **Anulación de preautorización** | Libera cantidad retenida. Movimientos pueden no reflejarse. |
| **Anulación de devolución** | Debe hacerse en **menos de 48h**. Después, irreversible. |
| **Anulación de confirmación/autenticación** | Sin rastro en extracto bancario del titular. |
