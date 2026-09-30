---
name: redsys_installments_financing
description: Integración de pagos fraccionados, cuotas y sistemas de financiación (Oney, Klarna, SeQura) vía Redsys.
---

# Redsys Installments & Financing

Redsys permite ofrecer financiación y pagos aplazados tanto a través de acuerdos bancarios directos como mediante proveedores integrados (Oney, Klarna, SeQura).

## 1. Pago Fraccionado (Directo)
Utilizado cuando el banco adquirente permite al cliente elegir el número de cuotas en la pantalla del TPV.
- **`Ds_Merchant_Installments`**: Número de cuotas (ej: `3`, `6`, `12`).
- **`Ds_Merchant_SumTotal`**: Importe total de la operación (incluyendo intereses gestionados por el comercio).
- **`Ds_Merchant_PayMethods = R`**: A menudo activa el sistema de cuotas o pagos recurrentes.

## 2. Proveedores de Financiación (APMs)
Redsys actúa como pasarela para proveedores externos. Para activarlos por separado en PrestaShop:

- **Oney (`Ds_Merchant_PayMethods = O`)**: Muy común para financiación en 3x o 4x sin intereses. Requiere datos de scoring (dirección completa, DNI del cliente si es posible).
- **Klarna / SeQura**: Se integran a través del Hub de Métodos Alternativos. Si se deja `PayMethods` vacío o como `all`, el banco mostrará las opciones disponibles según el importe del carrito.

### Datos de Scoring para Financiación:
Para aprobaciones automáticas de préstamos (Oney/Klarna), envía estos campos en `Ds_Merchant_Emv3ds` o parámetros extendidos:
- `shipAddrLine1`, `shipAddrCity`, `shipAddrPostCode`: Dirección de envío real.
- `mobilePhone`: Teléfono móvil validado.
- `email`: Correo del cliente.
- `DS_MERCHANT_PRODUCTDESCRIPTION`: Descripción clara de los bienes (ayuda al scoring de riesgo).

## 3. Flujo Técnico REST
1. **Consulta inicial**: Usar `iniciaPeticionREST` para ver si el terminal soporta cuotas para ese importe.
2. **Autorización**: Enviar los parámetros `Installments` y `SumTotal` en el bloque `operation` del JSON.

## 4. Gestión de Devoluciones
**ATENCIÓN**: Las devoluciones de pagos financiados (especialmente Oney) suelen requerir la anulación del préstamo completo. No siempre se permiten devoluciones parciales asíncronas desde PrestaShop; a veces es obligatorio gestionarlas desde el portal Canales de Redsys.

## 5. Estrategia de Conversión
- **Visualización**: No esperes al TPV para informar de las cuotas. Usa el simulador de Oney/Klarna en la ficha de producto de PrestaShop y luego pasa los parámetros correctos a Redsys.
- **Filtro de Importe**: Evita mostrar financiación para importes muy bajos (ej: < 50€) para no confundir al cliente, ajustando el parámetro `PayMethods` dinámicamente.
