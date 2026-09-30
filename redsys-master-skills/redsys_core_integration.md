---
name: redsys_core_integration
description: Guía fundamental para la integración básica de Redsys mediante redirección y firma HMAC-SHA512.
---

# Redsys Core Integration (Redirect Mode)

Esta skill cubre los fundamentos para integrar la pasarela de pago Redsys en PrestaShop utilizando el método de **Redirección**, que es el más sencillo y seguro para pequeños comercios (PCI-DSS SAQ-A).

## 1. Configuración de Credenciales
Para operar con Redsys necesitas:
- **FUC (Código de Comercio)**: Identificador numérico de 9 dígitos.
- **Número de Terminal**: Normalmente `001`, `01` o `1`.
- **Clave Secreta de Encriptación**: Clave suministrada por el banco (V2 SHA-512).

## 2. Proceso de Firma (V2 SHA-512)
Redsys utiliza un sistema de firma robusto para evitar manipulaciones.
1. **MerchantParameters**: Se crea un JSON con los datos del pago y se codifica en **Base64 URL-safe**.
2. **Derivación de Clave**: La clave secreta del comercio se cifra con el número de pedido (`Ds_Merchant_Order`) usando **3DES-CBC** con IV de ceros.
3. **Firma**: Se calcula el **HMAC-SHA512** del `MerchantParameters` codificado usando la clave derivada.

### Parámetros Obligatorios:
- `Ds_Merchant_Amount`: Importe en céntimos (ej: 10€ -> `1000`).
- `Ds_Merchant_Order`: ID de pedido (12 caracteres, inicia con 4 dígitos).
- `Ds_Merchant_MerchantCode`: FUC.
- `Ds_Merchant_Currency`: `978` (Euro).
- `Ds_Merchant_TransactionType`: `0` (Autorización).
- `Ds_Merchant_Terminal`: ID Terminal.
- `Ds_Merchant_MerchantURL`: URL para la notificación HTTP POST (IPN).
- `Ds_Merchant_UrlOK`: Redirección en caso de éxito.
- `Ds_Merchant_UrlKO`: Redirección en caso de fallo.

## 3. Endpoints oficiales (2025)
- **Entorno de Pruebas**: `https://sis-t.redsys.es:25443/sis/realizarPago`
- **Entorno Real**: `https://sis.redsys.es/sis/realizarPago`

## 4. Mejores Prácticas en PrestaShop
- **Notificación HTTP POST**: Validar siempre el pago en la notificación online, nunca en el retorno del usuario (`UrlOK`).
- **Estado del Pedido**: Crear el pedido en PrestaShop como "Pago Aceptado" solo si el código de respuesta (`Ds_Response`) está entre `0000` y `0099`.
- **Detección de Mobile**: Redsys adapta la interfaz automáticamente, pero se puede forzar con parámetros CSS.

## 5. Parámetros de Selección de Pago
- `Ds_Merchant_PayMethods`: 
    - `T`: Solo Tarjeta (por defecto).
    - `z`: Bizum.
    - `p`: PayPal.
    - `R`: Pago Aplazado.
