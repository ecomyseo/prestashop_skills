---
name: redsys_psd2_exemptions
description: Manejo avanzado de exenciones de Autenticación Reforzada (SCA) y optimización de conversión con EMV 3DS.
---

# Redsys PSD2 & SCA Exemptions

Para maximizar la conversión en PrestaShop, es vital reducir el número de veces que el usuario debe firmar el pago mediante SMS o App (SCA).

## 1. El Parámetro `DS_MERCHANT_EXCEP_SCA`
Puedes proponer al emisor (el banco del cliente) que no pida validación si se cumple alguna de estas condiciones:

- **LWV (Low Value Transaction)**: Pagos de menos de **30€**. La exención se aplica hasta un máximo de 5 operaciones o 100€ acumulados.
- **TRA (Transaction Risk Analysis)**: Análisis de riesgo por parte del comercio. Si el cliente es recurrente y fiable, puedes proponer TRA.
- **MIT (Merchant Initiated Transaction)**: Pagos automáticos posteriores (suscripciones) donde el cliente no está presente. Requiere CIT previo con SCA.
- **COR (Secure Corporate Payment)**: Pagos realizados con tarjetas B2B de empresa que usan canales seguros fuera de PSD2.
- **ATD (Delegated Authentication)**: Delegar la autenticación de forma técnica (poco común para comercios estándar).

## 2. Implementación en la Petición
Es preferible usar **Petición con datos EMV3DS** al solicitar exenciones. Si el banco rechaza la exención, Redsys lanzará el "Challenge" (SMS/Firma) automáticamente en lugar de denegar el pago.

## 3. Campos Recomendados para Frictionless
Para que un pago sea "Frictionless" (sin firma), envía el máximo de datos posible en `Ds_Merchant_Emv3ds`:
- `email`: Correo electrónico del cliente.
- `mobilePhone`: Móvil de contacto.
- `shipAddrCity`, `shipAddrCountry`, `shipAddrLine1`: Dirección de envío.
- `billAddrLine1`: Dirección de facturación.

## 4. Respuesta Soft Decline (Código 0195)
Si el emisor devuelve este código, significa que la transacción ha sido denegada porque requiere SCA obligatoriamente. Debes reiniciar el flujo de pago sin exención.

## 5. Cuándo solicitar Exenciones
- **Always LWV**: Automatizar la petición de exención LWV para carritos de < 30€ en PrestaShop.
- **White Listing**: Redsys también permite "almacenar" que el cliente ha marcado tu tienda como de confianza (Whitelisting), lo que facilita futuros pagos sin SCA.
