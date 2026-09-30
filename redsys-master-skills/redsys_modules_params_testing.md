# Redsys - Módulos de Pago, Parámetros, Entornos y Tarjetas de Prueba

> Fuentes:
> - https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-tipos-de-integracion/modulos-pago/
> - https://pagosonline.redsys.es/desarrolladores-inicio/integrate-con-nosotros/parametros-de-entrada-y-salida/
> - https://pagosonline.redsys.es/desarrolladores-inicio/integrate-con-nosotros/tarjetas-y-entornos-de-prueba/

---

## Módulos de Pago Oficiales

### Pasarela Unificada de Redsys (v2.0)
Incorpora Redirección, inSite y Bizum. Funcionalidades avanzadas:
- Autorización, preautorización, autenticación.
- Devoluciones desde backoffice.
- EMV3DS completo.
- Anulación en caso de error.

**Plataformas soportadas:**
| Plataforma | Versión | Descarga |
|-----------|---------|----------|
| PrestaShop | 2.0.0 | [Descargar](https://pagosonline.redsys.es/download/1714/) |
| WooCommerce | 2.0.0 | [Descargar](https://pagosonline.redsys.es/download/1717/) |
| Adobe Commerce/Magento 2 | 1.5.0 | [Descargar](https://pagosonline.redsys.es/download/1720/) |

### Pasarela Estándar (otras plataformas)
Solo Redirección + Bizum. Actualizaciones limitadas a seguridad.

| Plataforma | Versión |
|-----------|---------|
| OpenCart 4 | 3.1.1 |
| VirtueMart / Joomla 4 | 3.3.0 |
| osCommerce 4 | 3.2.0 |
| ZenCart | 3.1.1 |

---

## URLs de Entornos

### Sandbox (Pruebas)
| Servicio | URL |
|----------|-----|
| Redirección | `https://sis-t.redsys.es:25443/sis/realizarPago` |
| iniciaPeticion REST | `https://sis-t.redsys.es:25443/sis/rest/iniciaPeticionREST` |
| trataPeticion REST | `https://sis-t.redsys.es:25443/sis/rest/trataPeticionREST` |
| SOAP | `https://sis-t.redsys.es:25443/sis/services/SerClsWSEntradaV2` |
| Portal Administración | `https://sis-t.redsys.es:25443/admincanales-web/` |

### Producción (Real)
| Servicio | URL |
|----------|-----|
| Redirección | `https://sis.redsys.es/sis/realizarPago` |
| iniciaPeticion REST | `https://sis.redsys.es/sis/rest/iniciaPeticionREST` |
| trataPeticion REST | `https://sis.redsys.es/sis/rest/trataPeticionREST` |

---

## Datos Genéricos de Prueba

| Dato | Valor |
|------|-------|
| FUC (Código de comercio) | `999008881` |
| Terminal | `1` (o según operativa) |
| Clave de firma | `sq7HjrUOBfKmC576ILgskD5srU870gJ7` |
| Ds_SignatureVersion | `HMAC_SHA512_V2` |

---

## Tarjetas de Prueba

### Tarjetas Genéricas
| Marca | PAN | CVV | Caducidad | EMV3DS |
|-------|-----|-----|-----------|--------|
| **VISA** (genérica) | `4548810000000003` | `123` | Cualquier futura | v2.2 |
| **Mastercard** | `5576570000000004` | `123` | Cualquier futura | v2.1 |

### Pruebas con Bizum
- Importe máximo: **10€**
- PIN: `1234`
- Código SMS: `123456`

### Simulación de Errores con CVV
| CVV | Resultado |
|-----|-----------|
| `123` | Operación correcta |
| `999` | Error / Denegación |

### Simulación de Errores con Importe
| Importe (céntimos) | Resultado |
|---------------------|-----------|
| `X96` (ej. 196, 296...) | Denegación |
| `X72` | Denegación |
| `X73` | Denegación |
| `X74` | Denegación |

---

## Parámetros Principales de la Petición

| Parámetro | Descripción | Obligatorio |
|-----------|-------------|-------------|
| `Ds_Merchant_MerchantCode` | Código FUC del comercio | ✅ |
| `Ds_Merchant_Terminal` | Número de terminal | ✅ |
| `Ds_Merchant_Order` | ID del pedido (4-12 chars, 4 primeros numéricos) | ✅ |
| `Ds_Merchant_Amount` | Importe en céntimos (sin decimales) | ✅ |
| `Ds_Merchant_Currency` | Código ISO 4217 (978 = EUR) | ✅ |
| `Ds_Merchant_TransactionType` | Tipo de operación | ✅ |
| `Ds_Merchant_MerchantURL` | URL notificación HTTP POST | Recomendado |
| `Ds_Merchant_UrlOK` | URL retorno OK | Opcional |
| `Ds_Merchant_UrlKO` | URL retorno KO | Opcional |
| `Ds_Merchant_ConsumerLanguage` | Código idioma pasarela | Opcional |
| `Ds_Merchant_PayMethods` | Método de pago: `C` (tarjeta), `z` (Bizum), `xpay` | Opcional |
| `Ds_Merchant_MerchantData` | Datos auxiliares del comercio | Opcional |
| `Ds_Merchant_DirectPayment` | `true` o `MOTO` | Según operativa |
| `Ds_Merchant_Identifier` | Token/referencia COF | Para tokenización |
| `Ds_Merchant_Cof_Ini` | `S` (primera COF) o `N` (sucesiva) | Para tokenización |
| `Ds_Merchant_Cof_Type` | Tipo operativa COF (R, I, etc.) | Para tokenización |
| `Ds_Merchant_Excep_SCA` | Exención SCA (MIT, TRA, LWV...) | Según operativa |
| `Ds_Merchant_EMV3DS` | Objeto JSON con datos EMV3DS | En REST |
| `Ds_Merchant_PAN` | Número de tarjeta | En REST (sin inSite) |
| `Ds_Merchant_ExpiryDate` | Caducidad AAMM | En REST (sin inSite) |
| `Ds_Merchant_CVV2` | CVV | En REST (sin inSite) |
| `Ds_Merchant_IdOper` | ID de operación inSite | En inSite |
| `Ds_Merchant_PersoCode` | Código de personalización | Opcional |

## Tipos de Transacción (Ds_Merchant_TransactionType)

| Valor | Operación |
|-------|-----------|
| `0` | Autorización |
| `1` | Preautorización |
| `2` | Confirmación de preautorización |
| `3` | Devolución |
| `7` | Autenticación |
| `9` | Anulación de autorización |
| `44` | Anulación de preautorización |
| `45` | Anulación parcial / confirmación |

## Parámetros de Respuesta Clave

| Parámetro | Descripción |
|-----------|-------------|
| `Ds_Response` | Código resultado (`0000` = OK, `0900` = devolución OK, `0400` = anulación OK) |
| `Ds_AuthorisationCode` | Código de autorización |
| `Ds_Order` | Número de pedido |
| `Ds_Amount` | Importe |
| `Ds_Currency` | Divisa |
| `Ds_SecurePayment` | `0` (no seguro), `1` (3DS v1), `2` (3DS v2) |
| `Ds_Card_Brand` | Marca: `1` (Visa), `2` (MC), etc. |
| `Ds_Card_Country` | País emisor de la tarjeta |
| `Ds_Card_Type` | `C` (crédito), `D` (débito) |
| `Ds_MerchantCode` | FUC del comercio |
| `Ds_Terminal` | Terminal |
| `Ds_TransactionType` | Tipo de transacción |
| `Ds_Merchant_Identifier` | Token/referencia generada |

## Contacto de Soporte
- **Email**: soportevirtual@redsys.es
- **Teléfono**: 91 728 23 23
