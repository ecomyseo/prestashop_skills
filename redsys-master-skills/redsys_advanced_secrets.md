---
name: redsys_advanced_secrets
description: Lista maestra de parámetros avanzados, "ocultos" y configuraciones de bajo nivel para expertos.
---

# Redsys Advanced Secrets & Parameters

Esta skill recopila parámetros y configuraciones que no suelen aparecer en las guías básicas pero que son cruciales para desarrollos de alto nivel.

## 1. Parámetros "Ocultos" y Poco Documentados
- `Ds_Merchant_DirectPayment`: `true` (bool). Fuerza al TPV a procesar la tarjeta inmediatamente si se envían los datos via REST, saltándose pasos de confirmación.
- `Ds_Merchant_Group`: Permite agrupar operaciones si tienes varios FUCs o terminales vinculados.
- `Ds_Merchant_SumTotal`: Utilizado en operaciones de pago fraccionado para indicar el total del pedido.
- `Ds_Merchant_Identifier`: No solo acepta `REQUIRED`, sino que si se envía un valor de token erróneo o caducado, el TPV puede responder con errores específicos de gestión de tokens (`8` o `B`).
- `Ds_Merchant_Terminal`: Aunque suele ser `001`, algunos bancos usan terminales espejo para Bizum (`999`) o MOTO.

## 2. Operativas Especiales
- **MOTO (Mail Order / Telephone Order)**: Para pagos donde el comercio introduce los datos (requiere terminal especial). Parámetro `Ds_Merchant_TransactionType = 0` con indicador MOTO en el portal.
- **Doble Flujo Bizum**: Permite separar autenticación (`Type 7`) de autorización (`Type 8`). El comercio tiene hasta **30 días** para confirmar el cobro tras la autenticación del usuario.

## 3. Arquitectura del Sistema de Notificación
- **Reintentos**: Redsys reintenta la notificación HTTP POST si el servidor del comercio falla (HTTP 500 o Timeout). Los reintentos son progresivos (cada 5m, 15m, 1h, etc.) hasta un máximo de 24h.
- **Orden de Ejecución**: La notificación (`MerchantURL`) y la redirección del usuario (`UrlOK`) ocurren casi simultáneamente, pero la notificación suele llegar antes. **Nunca** esperes al usuario para confirmar el pago.

## 4. Gestión de Errores Críticos
- **Error 913**: Pedido repetido. Ocurre si intentas usar un `Ds_Merchant_Order` que ya fue autorizado O denegado en el mismo día. Solución: Añadir sufijo temporal al ID de pedido o solicitar al banco "reutilización de pedidos".
- **Error 9912**: El emisor del cliente no responde. Muy común en tarjetas extranjeras. Se recomienda reintentar pasados unos minutos.

## 5. Parámetros EMV 3DS de Alto Impacto
- `acctID`: ID de cuenta del cliente en la tienda. Ayuda al banco a reconocer al cliente.
- `addrMatch`: `Y` o `N`. Indica si la dirección de envío y facturación coinciden.
- `threeDSRequestorAuthenticationInd`: `01` (Pago normal), `02` (Recurrente), `03` (Instalment).
