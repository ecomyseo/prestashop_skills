---
name: redsys_insite_javascript
description: Integración de formularios de tarjeta embebidos (iFrames) con personalización CSS y gestión de idOper.
---

# Redsys InSite (Embedded Forms)

InSite permite incrustar el formulario de tarjeta de Redsys dentro de PrestaShop mediante iFrames, manteniendo el cumplimiento PCI-DSS SAQ-A.

## 1. Carga de la Librería
Incluir el script oficial en la cabecera o antes del checkout:
- **Sandbox**: `https://sis-t.redsys.es:25443/sis/NC/sandbox/redsysV3.js`
- **Producción**: `https://sis.redsys.es/sis/NC/redsysV3.js`

## 2. Modalidades de Integración
### A. Unificada (Todo en uno)
Genera un único bloque compacto con todos los campos.
```javascript
getInSiteFormJSON({
    id: "contenedor-id",
    fuc: "999008881",
    terminal: "1",
    order: "123456ABC",
    estiloInsite: "inline" // o "twoRows"
});
```

### B. Independiente
Permite colocar cada campo (Número, Fecha, CVV) en diferentes partes del layout para un diseño 100% nativo.
```javascript
getCardInput('card-number', styleCaja, placeholder, styleInput);
getExpirationInput('card-expiration', styles, placeholder);
getCVVInput('cvv', styles, placeholder);
```

## 3. Gestión de Tokens Temporales (idOper)
Al pulsar "Pagar", Redsys valida los datos y genera un `idOper`. Debes escucharlo mediante un listener:
```javascript
window.addEventListener("message", function(event) {
    // Almacena el result en un input hidden para enviarlo a tu controller PrestaShop
    storeIdOper(event, "token_input_id", "error_message_id", validationFunction);
});
```

## 4. Finalización del Pago
El `idOper` obtenido caduca pronto. Debes enviarlo por AJAX a un controller de PrestaShop que llame a la API REST de Redsys (`trataPeticionREST`) usando el parámetro `DS_MERCHANT_IDOPER`.

## 5. Personalización (Oculto/Avanzado)
- **CSS Avanzado**: Se pueden pasar objetos de estilo completos para que el iFrame use la misma tipografía y colores que la tienda.
- **Dominios Permitidos**: **CRÍTICO**. Debes registrar el dominio de tu tienda en el Portal de Administración de Redsys, de lo contrario el iFrame lanzará un error de inicialización.
- **Idiomas**: Soporta más de 20 idiomas (ES: 1, EN: 2, etc.). Se puede usar el código ISO 639-1.
