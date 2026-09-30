---
name: redsys_wallets_alternative_methods
description: Integración de Google Pay, Apple Pay, Amazon Pay, PayPal y otros métodos alternativos a través de Redsys.
---

# Redsys Wallets & Alternative Payment Methods

Redsys no solo procesa tarjetas; permite centralizar múltiples métodos de pago mediante el parámetro `Ds_Merchant_PayMethods` o integración nativa InSite.

## 1. Códigos de `Ds_Merchant_PayMethods`
Añade estos valores a la petición de redirección o REST para filtrar o forzar métodos:
- **`T`**: Tarjeta (VISA/Mastercard). Valor por defecto.
- **`z`**: **Bizum**. Imprescindible para el mercado español.
- **`p`**: **PayPal**. Requiere configuración previa en el portal Canales.
- **`y`**: **Google Pay**. Activa el botón de Google Pay directamente.
- **`w`**: **Apple Pay**. Activa el botón de Apple Pay (requiere entorno compatible).
- **`M`**: **Amazon Pay**. Integración delegada (redirige a la interfaz de Amazon).
- **`O`**: **Masterpass**. Monedero digital de Mastercard.
- **`R`**: Pago Aplazado / Fraccionado (depende del banco adquirente).

## 2. Google Pay & Apple Pay (Modo InSite)
Es la forma más profesional para PrestaShop (One-Page Checkout).
1. **Configuración JS**: En el objeto JSON de `getInSiteFormJSON`, habilita los flags:
   ```javascript
   {
       // ... parámetros básicos ...
       googlePay: true,
       applePay: true,
       styleButtonGoogle: { ... }, // Opcional: Personalizar botón Google
       styleButtonApple: { ... }   // Opcional: Personalizar botón Apple
   }
   ```
2. **Flujo Técnico**: 
   - El cliente no abandona tu checkout. 
   - El dispositivo autentica (Huella/FaceID). 
   - Redsys genera un `idOper` especial que ya incluye la autenticación.
   - Envías este `idOper` por REST con `TransactionType = 0`.

## 3. Requisito Crítico: Apple Pay Domain Verification
Para que Apple Pay funcione, debes:
1. Descargar el archivo de validación desde el portal Canales de Redsys.
2. Subirlo a tu servidor PrestaShop en la ruta: `/.well-known/apple-developer-merchantid-domain-association`.
3. Validar el dominio en el portal Canales. Sin esto, el botón no aparecerá nunca.

## 4. Amazon Pay con Redsys
- Funciona como un método de redirección delegada.
- El usuario se identifica con su cuenta de Amazon.
- Redsys recibe una confirmación de pago garantizada.
- **Nota**: El ID de pedido de Amazon Pay puede ser consultado en el portal de Redsys para conciliaciones contables.

## 5. Ventajas de Conversión
- **Frictionless nativo**: Al usar Wallets, el usuario ya está autenticado en su dispositivo. Redsys marca la transacción como **SCA Compliant** automáticamente. No hay "Soft Decline" ni SMS de confirmación.
- **Confianza**: Mostrar los logos de Amazon o Google en el checkout reduce el abandono del carrito en un 20-30%.
