# Redsys - Firma de Operaciones HMAC SHA-512 (Documentación Oficial)

> Fuente: https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-operativa/firmar-una-operacion/

## Algoritmo: HMAC SHA-512

`Ds_SignatureVersion` = `HMAC_SHA512_V2`

## Proceso de Firma

### Paso 1: Generar la clave específica de la operación

1. Tomar la **clave de comercio** (proporcionada por la entidad bancaria).
2. **Pre-procesar la clave** a exactamente 16 caracteres:
   - Si tiene **más de 16**: tomar los primeros 16.
   - Si tiene **menos de 16**: rellenar por la derecha con ceros.
   - Ejemplo: `sq7HjrUOBfKmC576ILgskD5srU870gJ7` → `sq7HjrUOBfKmC576`
3. **Cifrar AES CBC** del **número de pedido** con la clave pre-procesada.
   - Vector de inicialización (IV): **vector de ceros**.
4. **Codificar** el resultado en **Base64**.

**Ejemplo resultado**: `RWt3/IPTzYRMXsQtkiGRKg==`

### Paso 2: Calcular HMAC SHA-512

Calcular el **HMAC SHA-512** del valor de `Ds_MerchantParameters` (cadena en Base64URL) usando la clave específica obtenida en el Paso 1.

### Paso 3: Obtener Ds_Signature

**Codificar** el resultado del Paso 2 en **Base64URL**.

**Ejemplo resultado**: `Vjo02eSWq249IeZZp3R-ArFnGLhKY0OuzDDlx1BuVtZDC2yhczA7_11uZhsYzLZBCMFAz8u8uzGDX3AErHKmmw`

## Petición Final

```json
{
  "Ds_MerchantParameters": "BASE64URL_ENCODED_JSON",
  "Ds_Signature": "HMAC_SHA512_BASE64URL",
  "Ds_SignatureVersion": "HMAC_SHA512_V2"
}
```

## Verificar la Respuesta del TPV Virtual

Al recibir la notificación del TPV Virtual:

1. **Decodificar** `Ds_MerchantParameters` desde Base64 para obtener `Ds_Order`.
2. **Repetir el proceso de firma** (Pasos 1-3) con los parámetros recibidos (sin decodificar el Base64).
3. **Comparar** la firma calculada con la `Ds_Signature` recibida.
4. **Si coinciden** → el mensaje no ha sido alterado → proceder con la operación.

> ⚠️ **SIEMPRE verificar las firmas** antes de realizar cualquier acción (confirmar pedido, enviar producto, etc.).

## Ejemplo Completo en PHP

```php
// Clave de prueba
$clave = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7';

// Pre-procesar clave a 16 caracteres
$clave16 = substr($clave, 0, 16);

// Número de pedido
$order = '1234567890';

// Paso 1: AES CBC con IV de ceros
$iv = str_repeat("\0", 16);
$clave_operacion = openssl_encrypt($order, 'AES-128-CBC', $clave16, OPENSSL_RAW_DATA, $iv);

// Paso 2: HMAC SHA-512
$hmac = hash_hmac('sha512', $ds_merchant_parameters_base64, $clave_operacion, true);

// Paso 3: Base64URL
$firma = rtrim(strtr(base64_encode($hmac), '+/', '-_'), '=');
```

## Bibliotecas Oficiales

Disponibles en PHP y Java en el [Área de Descargas](https://pagosonline.redsys.es/desarrolladores-inicio/integrate-con-nosotros/area-de-descargas-y-documentacion/).
