# Redsys - Integración inSite Completa (Documentación Oficial)

> Fuente: https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-tipos-de-integracion/desarrolladores-insite/

## Descripción General

La integración **inSite** permite integrar los campos de pago directamente en la página web del comercio usando iframes seguros proporcionados por Redsys. El cliente NO abandona la web del comercio. Los datos de tarjeta quedan inaccesibles al servidor del comercio → **sin necesidad de cumplir PCI-DSS**.

## Flujo de Funcionamiento

1. El comercio presenta el formulario de pago al titular con iframes de Redsys.
2. Redsys completa el formulario con campos seguros para captura de datos de tarjeta.
3. El titular introduce datos y acepta el pago.
4. Redsys genera un **ID de operación (idOper)** y lo informa al comercio.
5. El comercio lanza la operación de pago mediante REST usando el `idOper`.

> ⚠️ El `idOper` tiene una validez de **30 minutos** desde su generación.

## Características

- Experiencia de pago totalmente integrada en la web del comercio.
- Mayor control del flujo de checkout.
- Alta seguridad sin PCI-DSS.
- Dos modalidades: **Unificada (todo-en-uno)** y **Elementos independientes**.

## JavaScript de Redsys

### Entorno de Pruebas (Test)
```html
<script src="https://sis-t.redsys.es:25443/sis/NC/sandbox/redsysV3.js"></script>
```

### Entorno de Producción (Real)
```html
<script src="https://sis.redsys.es/sis/NC/redsysV3.js"></script>
```

## Modalidad 1: Integración Unificada (Todo-en-uno)

Un único iframe con formulario de pago completo. Incluye reconocimiento de marca de tarjeta, verificación de formatos, y estilos CSS personalizables.

### HTML Base

```html
<div id="card-form"/>
<input type="hidden" id="token"></input>
<input type="hidden" id="errorCode"></input>
```

### Listener de recepción del idOper

```javascript
function merchantValidationEjemplo(){
    // Insertar validaciones propias…
    return true;
}

window.addEventListener("message", function receiveMessage(event) {
    storeIdOper(event, "token", "errorCode", merchantValidationEjemplo);
});
```

### Carga del iframe

```javascript
getInSiteForm(
    'card-form',      // ID del contenedor
    estiloBoton,      // CSS del botón
    estiloBody,       // CSS del fondo/textos
    estiloCaja,       // CSS de la caja de datos
    estiloInputs,     // CSS de los inputs
    'Texto bot&#243;n pago', // Texto del botón (codificado HTML)
    fuc,              // Número de comercio (FUC)
    terminal,         // Número de terminal
    merchantOrder,    // Número de pedido
    'ES',             // Idioma
    true              // Mostrar logo entidad
);
```

### Estilos personalizables
- `estiloBoton`: Personalización del botón de pago.
- `estiloBody`: Color de fondo y estilo de textos.
- `estiloCaja`: Color de fondo diferenciado para la caja de datos.
- `estiloInputs`: Tipografía y color del texto en inputs.

## Modalidad 2: Integración por Elementos Independientes

Campos separados con control total de posición y diseño.

### HTML Base

```html
<div id="card-number"></div>
<div id="expiration-month"></div>
<div id="expiration-year"></div>
<!-- O usar campo unificado: -->
<div id="card-expiration"></div>
<div id="cvv"></div>
<div id="boton"></div>
<input type="hidden" id="token"></input>
<input type="hidden" id="errorCode"></input>
```

### Carga de iframes (caducidad separada)

```javascript
getCardInput('card-number', estiloCaja, placeholder, estiloInput);
getExpirationMonthInput('expiration-month', estilosCSS, placeholder);
getExpirationYearInput('expiration-year', estilosCSS, placeholder);
getCVVInput('cvv', estilosCSS, placeholder);
getPayButton('boton', estilosCSS, 'Texto bot&#243;n pago', fuc, terminal, merchantOrder);
```

### Carga de iframes (caducidad unificada)

```javascript
getCardInput('card-number', estiloCaja, placeholder, estiloInput);
getExpirationInput('card-expiration', estilosCSS, placeholder);
getCVVInput('cvv', estilosCSS, placeholder);
getPayButton('boton', estilosCSS, 'Texto bot&#243;n pago', fuc, terminal, merchantOrder);
```

## Solicitud de la Operación con idOper

Una vez recibido el `idOper`, enviar petición REST con:

```json
{
  "DS_MERCHANT_ORDER": "pedido123",
  "DS_MERCHANT_MERCHANTCODE": "999008881",
  "DS_MERCHANT_TERMINAL": "1",
  "DS_MERCHANT_TRANSACTIONTYPE": "0",
  "DS_MERCHANT_CURRENCY": "978",
  "DS_MERCHANT_AMOUNT": "1000",
  "DS_MERCHANT_IDOPER": "valor_del_idOper"
}
```

> Usar `DS_MERCHANT_IDOPER` en lugar de `PAN`, `ExpiryDate` y `CVV`.

## Errores Comunes

| Error | Causa | Solución |
|-------|-------|----------|
| `idOper = -1` | Número de pedido repetido | Cambiar el merchantOrder |
| `idOper = "Error"` | Parámetros no son string | Todos los parámetros deben ser cadenas |
| iframe no se dibuja | Dominios inSite no configurados | Configurar dominios en Portal de Administración |

## Configuración de Dominios inSite

En el **Portal de Administración del TPV Virtual**, configurar los dominios donde se implementará inSite para que los iframes se carguen correctamente.

## Codificación de Caracteres

El texto del botón y placeholders **DEBE estar codificado con caracteres HTML**:
- `Botón` → `Bot&#243;n`
- Caracteres cirílicos: `Кнопка` → `&#1050;&#1085;&#1086;&#1087;&#1082;&#1072;`
