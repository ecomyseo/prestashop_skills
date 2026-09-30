---
name: redsys_ui_customization
description: Herramientas de personalización de la interfaz de pago, A/B Testing con PersoCode y estilos CSS.
---

# Redsys UI Customization

Redsys permite adaptar la apariencia de la pasarela de pago para que coincida con la imagen corporativa de la tienda PrestaShop.

## 1. Niveles de Personalización
En el Portal de Administración (Canales), puedes crear diferentes perfiles de diseño:
- **Nivel 1**: Cambio de colores básicos, botones y mostrar/ocultar logos.
- **Nivel 2**: Carga de Logotipo personalizado (Máximo 30KB).
- **Nivel 3**: Carga de fichero **CSS propio** para una personalización total del layout.

## 2. A/B Testing y Selección Dinámica
Un parámetro "oculto" muy útil es `Ds_Merchant_PersoCode`.
- Permite forzar el uso de una personalización específica enviando su código identificador.
- **Caso de uso**: Puedes enviar un diseño diferente si el cliente viene de un móvil o si es un cliente VIP.

## 3. Parámetros de Interfaz
- `Ds_Merchant_ConsumerLanguage`: Define el idioma inicial (ver códigos en la skill de testing).
- `Ds_Merchant_ProductDescription`: Aparece en la cabecera del TPV para informar al cliente de qué está pagando.
- `Ds_Merchant_ShippingAddressPymt`: Si se envía con valor `S`, el TPV pedirá al usuario la dirección de envío (útil si no se ha capturado en PrestaShop).

## 4. InSite Customization
Al usar InSite, la personalización se hace vía Javascript pasando un objeto JSON con estilos:
- `styleButton`: Estilos CSS para el botón de pago.
- `styleBody`: Estilos para el fondo del formulario.
- `styleBox`: Estilos para el contenedor de los inputs.
- `estiloInsite`: Valores `inline` o `twoRows`.

## 5. Recomendación de Diseño
Para maximizar la conversión, se recomienda:
1. Usar un diseño **limpio y minimalista**.
2. Asegurarse de que el botón de "Pagar" sea **claro y visible**.
3. Mantener la coherencia de colores con el checkout de la tienda para reducir el sentimiento de "abandono" del sitio.
