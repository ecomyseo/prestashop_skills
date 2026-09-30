# Proceso de compra: cambios y verificación

El checkout es la parte del tema que **no se puede romper**. Un fallo de
maquetación se ve y se corrige; un fallo de checkout no se ve y se factura en
pedidos perdidos.

Buena noticia: entre 1.7.0 y 1.7.8.11 el checkout apenas cambia. Toda la ruptura
está concentrada en **8.0**, y luego en 9.1 con el multi-envío.

## 🔴 8.0 — El `id` del formulario de pago

El cambio más peligroso de toda la migración.

```smarty
{* ≤ 1.7.8.11 — checkout/_partials/steps/payment.tpl *}
<form id="payment-form" method="POST" action="{$option.action nofilter}">

{* ≥ 8.0 *}
<form id="payment-{$option.id}-form" method="POST" action="{$option.action nofilter}">
```

**Por qué importa:** con un solo método de pago activo el `id` era único y todo
funcionaba por casualidad. Con varios, todos los formularios compartían el mismo
`id`, y `document.getElementById('payment-form')` devolvía siempre el primero.
El cliente elegía transferencia y se le enviaba el formulario de tarjeta.

**Al migrar hacia 8.0+:** aplicar el cambio y revisar además los módulos de pago.
Cualquier módulo que haga `$('#payment-form').submit()` o
`document.getElementById('payment-form')` deja de funcionar. Los módulos
oficiales ya están adaptados; los de terceros y los hechos a medida, no
necesariamente.

**Al migrar un tema hacia atrás** (raro, pero pasa con temas comprados para 8.x
que se instalan en 1.7.8): mantener el `id` dinámico es inofensivo salvo que un
módulo antiguo dependa del `id` fijo.

Lo que **no** ha cambiado nunca y por tanto es seguro conservar:

```
#payment-option-{$option.id}-container      contenedor de cada opción
#payment-option-{$option.id}                el radio
.js-payment-option-form                     formulario asociado
.additional-information                     información extra de la opción
{hook h='displayPaymentByBinaries'}         botones de pago externos
#conditions-to-approve / .js-terms          aceptación de condiciones
```

## 🔴 8.0 — `$customer` → `$order_customer` en la confirmación

```smarty
{* ≤ 1.7.8.11 — checkout/order-confirmation.tpl *}
{l s='An email has been sent to your mail address %email%.' … sprintf=['%email%' => $customer.email]}

{* ≥ 8.0 *}
… sprintf=['%email%' => $order_customer.email]
```

Con `PS_DEBUG_MODE` activo es error fatal en la página de confirmación —
justo después de cobrar. Sin debug, sale el mensaje con el email vacío. En ambos
casos el pedido ya está creado: no se pierde la venta, pero da muy mala imagen.

## 🟠 8.0 — Transformación de invitado en cliente

```smarty
{* ≤ 1.7.8.11 *}
{block name='customer_registration_form'}
  {if $customer.is_guest}
    <div id="registration-form" class="card">
      <h4 class="h4">{l s='Save time on your next order, sign up now' d='Shop.Theme.Checkout'}</h4>
      {render file='customer/_partials/customer-form.tpl' ui=$register_form}
    </div>
  {/if}
{/block}

{* ≥ 8.0 *}
{if !$registered_customer_exists}
  {block name='account_transformation_form'}
    <div class="card">
      {include file='customer/_partials/account-transformation-form.tpl'}
    </div>
  {/block}
{/if}
```

Cambian la variable de control (`$customer.is_guest` → `$registered_customer_exists`,
con la lógica **invertida**), el nombre del bloque y el mecanismo (`{render}` con
`$register_form` → `{include}` de un parcial nuevo). Copiar solo una parte deja
el formulario visible pero sin enviar.

## 🟠 8.0 — Etiqueta de impuestos

```smarty
{* ≤ 1.7.8.11 *}  {if $configuration.taxes_enabled}{$cart.labels.tax_short}{/if}
{* ≥ 8.0 *}       {if $configuration.display_taxes_label && $configuration.taxes_enabled}{$cart.labels.tax_short}{/if}
```

En `cart-summary-totals.tpl` y `order-confirmation-table.tpl`. Sin la condición
nueva, las tiendas que han desactivado la etiqueta la siguen viendo. Es un
requisito legal en algunos países, así que no es cosmético.

## 🟠 8.0 — Venta cruzada cambia de sitio

`{hook h='displayCrossSellingShoppingCart'}` sale de `checkout/cart-empty.tpl` y
entra en `checkout/cart.tpl`. Si el tema sobreescribe ambos ficheros y solo se
migra uno, el bloque aparece en el carrito vacío (donde no sirve de nada) y
desaparece del carrito con productos (donde vendía).

## 🟠 8.0 — Otros

- Nuevo hook `{hook h='displayCheckoutBeforeConfirmation'}` al final de
  `steps/payment.tpl`. Varios módulos de pago y de RGPD lo usan; si el tema
  sobreescribe `payment.tpl` y no lo incluye, esos módulos no pintan nada.
- `steps/addresses.tpl`: nuevo atributo `data-id-address="{$id_address}"`, que
  el JS usa para asociar la dirección al bloque.
- `<p class="cart-payment-step-not-needed-info">` en el aviso de "no se requiere
  pago" (pedidos a coste cero).
- Embalaje reciclable: `$is_recyclable_packaging` en `order-final-summary.tpl` y
  `$order.details.recyclable` en `order-confirmation.tpl`.
- En `cart.tpl` se limpian `col-xs-12` redundantes:
  `cart-grid-body col-xs-12 col-lg-8` → `cart-grid-body col-lg-8`. En Bootstrap 4
  `col-xs-12` es el comportamiento por defecto, así que no cambia nada visual.

## 🟡 8.2 — Contenido extra del transportista

```smarty
{* 8.1 *}
<div class="row carrier-extra-content js-carrier-extra-content"{if $delivery_option != $carrier_id} style="display:none;"{/if}>

{* 8.2 *}
<div class="carrier-extra-content js-carrier-extra-content"{if ($delivery_option != $carrier_id) || ($delivery_option == $carrier_id && empty($carrier.extraContent))} style="display:none;"{/if}>

{* 9.0 — vuelve `row`, se revierte la condición *}
<div class="row carrier-extra-content js-carrier-extra-content"{if $delivery_option != $carrier_id} style="display:none;"{/if}>
```

La clase `row` va y viene entre 8.1, 8.2 y 9.0. Sin ella, los transportistas se
desalinean; con ella donde no toca, se descuadra el paso de envío. Usar la
variante de la versión destino y comprobarlo visualmente con dos o más
transportistas activos.

Módulos de puntos de recogida (Correos, SEUR, Packlink…) inyectan su mapa en
`carrier-extra-content`. Si el tema sobreescribe `shipping.tpl` y pierde la clase
`js-carrier-extra-content`, el mapa no se muestra ni se puede elegir punto: el
cliente no puede completar el pedido.

## 🟠 9.1 — Multi-envío

Ver `breaking-changes.md` § 9.1. Resumen operativo: mientras el *feature flag* de
multi-envío esté desactivado, un tema de 9.0 funciona sin cambios. Si se activa,
hay que implementar las plantillas `*-multishipment.tpl` o el cliente verá un
único transportista para un pedido que se envía en varios paquetes.

Bifurcar siempre con la variable protegida, para no romper 9.0:

```smarty
{if isset($is_multishipment_enabled) && $is_multishipment_enabled}
```

## 🔴 9.2 — One Page Checkout (`ps_onepagecheckout`)

⚠️ A 24-07-2026 **no existe 9.2 estable**: esto sale de `9.2.0-beta.1` y de la
doc oficial, y hay que revalidarlo cuando se publique.

Es el cambio de checkout más grande desde 8.0. Módulo **nativo empaquetado**, no
de terceros. Doc:
<https://devdocs.prestashop-project.org/9/modules/checkout/theme-developers/>

### Cómo se activa (y por qué puede pillarte por sorpresa)

No sustituye plantillas del tema por copia: se engancha a
`hookDisplayOverrideTemplate` e **intercepta** `template_file === 'checkout/checkout'`
cuando el controlador es `OrderController`, devolviendo
`module:ps_onepagecheckout/views/templates/front/checkout/checkout.tpl`.

Consecuencia práctica: **con el módulo activo, tu `checkout/checkout.tpl` deja de
pintarse**. Si el tema tenía ahí personalizaciones, desaparecen sin dar ningún
error. Es exactamente el mismo síntoma desconcertante que el de los maquetadores
descrito en el SKILL: subes el cambio, vacías caché, y el HTML sigue igual.

### La variable de guarda

```smarty
{if isset($is_one_page_checkout_enabled) && $is_one_page_checkout_enabled}
```

🔴 `$is_one_page_checkout_enabled` **solo está asignada en la página de
checkout**. Usarla sin `isset()` en cabecera, pie o cualquier otra plantilla da
aviso de Smarty con `PS_DEBUG_MODE` activo.

### Contrato DOM: esto es API, no decoración

Si se sobreescriben las plantillas del OPC, estos selectores **deben
conservarse**. Quitar uno rompe el pago sin dar ningún error visible — la misma
categoría de fallo que el `id` del formulario de pago de 8.0:

```
.one-page-checkout                        wrapper que activa el JS
#opc-form
#opc-delivery-address-content-list        #opc-billing-address-content-list
#opc-delivery-methods                     #opc-payment-methods
#opc-pay-button                           #opc-pay-amount
#pay-with-{paymentOptionId}-form
input[name^="conditions_to_approve["][required]
#opc-template-loader  #opc-template-carriers-error  #opc-template-payment-error
#modal-delivery       #modal-invoice
```

Los tres `#opc-template-*` son elementos `<template>`: si el tema los elimina por
"limpiar HTML que no se ve", el checkout pierde el indicador de carga y los
mensajes de error de transportista y de pago.

### Variables y eventos nuevos

Variables del contexto OPC: `$formFields`, `$contactFields`,
`$additionalCustomerFields`, `$useSameAddressField`, `$deliveryFields`,
`$invoiceFields`, `$invoiceMetaFields`, `$delivery_options`,
`$selected_delivery_option`, `$payment_options`, `$selected_payment_module`,
`$selected_payment_selection_key`, `$conditions_to_approve`,
`$validation_errors`, `$validation_error_messages`, `$opc_urls`,
`$hookDisplayBeforeCarrier`, `$hookDisplayAfterCarrier`.

Eventos nuevos del bus `prestashop.on()`: familias `opcCarriers*`,
`opcPaymentMethods*`, `opcDeliveryAddressSelected`, `opcBillingAddressSelected`,
`updatedOpcAddressForm`, `opcGuestInit*`, `opcFinalSubmitStarted`,
`opcFormValidated`, `opcSubmitFailed`, `opcCartSummaryUpdated`.

Objeto runtime `window.ps_onepagecheckout` (`enabled`, `urls`, `messages`).
**Prohibido hardcodear endpoints**: leerlos siempre de ahí o de `$opc_urls`.

Hook nuevo para módulos: `actionCheckoutBuildProcess`.
CSS base: `views/public/one-page-checkout.css` (handle
`module-ps-onepagecheckout`, prioridad 200).

### 🔴 El aviso que decide el presupuesto

Verbatim de la doc oficial:

> *"If your theme was created from Classic or provides a custom theme structure,
> overrides of the One Page Checkout views will be broader"*

Es decir: el OPC funciona out-of-the-box sobre **Hummingbird**; sobre un tema
derivado de **classic** exige overrides bastante más amplios. Si el cliente
quiere checkout de una página en 9.2, **ese coste va en la propuesta y se estima
aparte**, no se asume dentro del "subir de versión".

Y como todo lo que toca el checkout: es lo último que se toca y lo primero que se
verifica, con el checklist completo de abajo, de principio a fin.

## Checklist de verificación

**Obligatorio antes de dar por buena una migración.** No vale con que la página
cargue: hay que llegar hasta el pedido confirmado.

### Carrito
- [ ] Añadir producto simple, con combinación y pack.
- [ ] Añadir producto personalizable (texto y fichero).
- [ ] Cambiar cantidades con los botones `+` / `−` (touchspin) y escribiéndolas.
- [ ] Eliminar línea. Comprobar que los totales se recalculan sin recargar.
- [ ] Aplicar y quitar un cupón.
- [ ] Carrito vacío: se ve el mensaje correcto.

### Checkout
- [ ] Como invitado, como cliente nuevo y como cliente ya registrado.
- [ ] Registro: el medidor de fuerza de contraseña aparece y valida (8.0+).
- [ ] Alta y edición de dirección; dirección de facturación distinta de la de envío.
- [ ] **Elegir entre al menos dos transportistas**, y con uno que tenga contenido
      extra (punto de recogida).
- [ ] Aceptar condiciones: sin marcarlas, el botón de pedido no debe dejar seguir.
- [ ] **Elegir entre al menos dos métodos de pago** y confirmar que se envía el
      formulario correcto. Es la comprobación crítica de 8.0+.
- [ ] Pedido de importe cero, si la tienda los permite.

### Checkout de una página (solo 9.2, si `ps_onepagecheckout` está activo)
- [ ] Confirmar **quién pinta el checkout**: con el módulo activo, tu
      `checkout/checkout.tpl` no se usa. Verificar contra el HTML servido.
- [ ] Cambiar de dirección de envío y de facturación **sin recargar**, y que el
      resumen del carrito se actualiza.
- [ ] Marcar y desmarcar "usar la misma dirección para facturación".
- [ ] Cambiar de transportista y comprobar que **el total y los gastos de envío
      se recalculan** en `#opc-pay-amount`.
- [ ] Cambiar de método de pago y confirmar que se muestra el
      `#pay-with-{id}-form` correcto.
- [ ] Sin aceptar condiciones, `#opc-pay-button` no debe permitir pagar.
- [ ] Forzar un error de transportista y otro de pago: deben salir los mensajes
      de `#opc-template-carriers-error` y `#opc-template-payment-error`.
- [ ] Comprobar que el indicador de carga (`#opc-template-loader`) aparece.
- [ ] Los modales `#modal-delivery` y `#modal-invoice` abren y cierran.
- [ ] Repetirlo todo como invitado y como cliente registrado.

### Confirmación
- [ ] La página de confirmación muestra el email del cliente (no un hueco vacío).
- [ ] Referencia del pedido, método de pago y totales correctos.
- [ ] Enlace de descarga de factura, si está habilitada.
- [ ] Invitado: aparece el formulario de transformación a cuenta y **funciona**.
- [ ] El pedido consta en el back office con el estado correcto.

### Después de la compra
- [ ] Historial de pedidos y detalle del pedido.
- [ ] Solicitud de devolución (y que los productos virtuales no sean devolvibles, 8.0+).
- [ ] Descarga de producto virtual.

### Regresión general (fuera del checkout)

El checkout es lo crítico, pero una migración toca más superficie. Repaso corto:

- [ ] Home: bloques de destacados, slider, newsletter.
- [ ] Listado de categoría: paginación, orden, y **filtros facetados** — filtrar,
      quitar filtro y comprobar que el listado se recarga por AJAX sin perder la
      maquetación.
- [ ] Buscador: con resultados y sin resultados.
- [ ] Ficha: cambiar de combinación y verificar que se actualizan **precio,
      stock, referencia e imagen**. Es lo primero que rompe un tema mal migrado.
- [ ] Ficha: producto sin stock, producto virtual, pack, producto personalizable.
- [ ] Vista rápida (*quickview*) desde el listado.
- [ ] Cuenta de cliente: registro, login, recuperar contraseña, direcciones.
- [ ] Página de marcas y de proveedores (rehechas en 9.0).
- [ ] CMS, contacto y mapa del sitio.
- [ ] Una página 404.

### Entorno
- [ ] Repetir el pedido en móvil, no solo en escritorio.
- [ ] Con `PS_DEBUG_MODE` activado, sin ningún aviso de Smarty en toda la ruta.
- [ ] Con la caché de Smarty y el CCC (combinar/comprimir) **activados**, que es
      como estará en producción. Bastantes fallos solo salen con CCC activo.
