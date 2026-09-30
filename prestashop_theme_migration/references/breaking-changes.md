# Breaking changes del tema classic, versión a versión

Cada entrada indica su gravedad:

- 🔴 **Bloqueante** — la página no renderiza o el error es fatal.
- 🟠 **Funcional** — renderiza, pero algo deja de funcionar.
- 🟡 **Cosmético / mejora** — se puede posponer.

---

## 1.7.1 → 1.7.7 (cambios menores)

Rama muy estable en lo que respecta al tema. Altas de plantillas:

- **1.7.1**: `catalog/_partials/product-additional-info.tpl`.
  `cms/_partials/sitemap-tree-branch.tpl` → `sitemap-nested-list.tpl` (renombrado).
- **1.7.5**: `catalog/_partials/category-header.tpl` (extrae la cabecera de
  categoría de `listing/category.tpl`).
- **1.7.6**: `catalog/_partials/product-flags.tpl` y
  `checkout/_partials/cart-summary-subtotals.tpl`.
- **1.7.7**: `catalog/_partials/productlist.tpl`.

🟡 Si el tema es de 1.7.0–1.7.4 y no tiene estos parciales, los hereda de
classic sin problema. Solo hay que crearlos si el tema sobreescribe el fichero
padre que ahora hace `{include}` de ellos.

Bootstrap pasa de `4.0.0-alpha.2` a `4.0.0-alpha.5` en 1.7.1 y ahí se queda para
siempre.

---

## 1.7.7 → 1.7.8  🔴 Ruptura grande

La versión 1.7.8 es el primer corte real de la rama 1.7. Un tema de 1.7.6 puesto
tal cual en 1.7.8 renderiza, pero la ficha de producto deja de actualizarse al
cambiar de combinación.

### 🔴 Microdatos inline → JSON-LD

Se elimina todo el marcado `itemscope` / `itemtype` / `itemprop` de las
plantillas y se sustituye por tres parciales nuevos:

```
_partials/microdata/head-jsonld.tpl
_partials/microdata/product-jsonld.tpl
_partials/microdata/product-list-jsonld.tpl
```

En `catalog/product.tpl`:

```smarty
{* ≤ 1.7.7 *}
<section id="main" itemscope itemtype="https://schema.org/Product">
  <meta itemprop="url" content="{$product.url}">
  <h1 class="h1" itemprop="name">{$product.name}</h1>
  <div id="product-description-short-{$product.id}" itemprop="description">…</div>

{* ≥ 1.7.8 *}
{block name='head_microdata_special'}
  {include file='_partials/microdata/product-jsonld.tpl'}
{/block}
…
<section id="main">
  <meta content="{$product.url}">
  <h1 class="h1">{$product.name}</h1>
  <div id="product-description-short-{$product.id}" class="product-description">…</div>
```

Si se deja el marcado antiguo **y** se activa el JSON-LD nuevo, Google recibe el
producto dos veces y avisa de datos estructurados duplicados. Hay que elegir uno
de los dos: lo correcto es quitar el inline.

### 🟠 `$product.canonical_url` → `$product.url` en miniaturas

```smarty
{* ≤ 1.7.7 *}  <a href="{$product.canonical_url}" class="thumbnail product-thumbnail">
{* ≥ 1.7.8 *}  <a href="{$product.url}" class="thumbnail product-thumbnail">
```

`canonical_url` apunta al producto sin combinación. Mantenerlo hace que los
enlaces del listado pierdan la combinación seleccionada (color, talla…) y el
cliente aterrice en la variante por defecto.

### 🔴 Clases `js-*`: contrato con el JavaScript

1.7.8 introduce `_dev/js/selectors.js` y mueve todo el JS a selectores `js-*`.
Sin estas clases, el JS no encuentra los nodos y **falla en silencio**: sin error
en consola, simplemente no pasa nada.

Añadidos en 1.7.8 que hay que replicar sí o sí:

| Fichero | Añadir |
|---|---|
| `catalog/product.tpl` | `js-product-container` en `.row`, `js-product-actions` en `.product-actions`, `js-product-customization-id` en el input `id_customization` |
| `catalog/_partials/product-add-to-cart.tpl` | `js-product-add-to-cart`, `js-product-availability`, `js-product-minimal-quantity` |
| `catalog/_partials/miniatures/product.tpl` | wrapper `<div class="js-product product">`, `js-quick-view` |
| `catalog/_partials/product-cover-thumbnails.tpl` | `js-thumb`, `js-thumb-selected`, `js-qv-product-cover` |

El síntoma clásico de haberlo omitido: al cambiar de combinación no se
actualizan precio, stock ni imagen.

La lista completa de selectores está en `_dev/js/selectors.js` del classic de la
versión destino. Es la referencia autoritativa.

### ✅ Matiz importante: el core mantiene compatibilidad con las clases antiguas

**Un tema legacy que nunca tuvo las clases `js-*` no está roto por eso.** El core
declara los selectores **duales** en `themes/_core/js/selectors.js`
(`prestashop.selectors`, distinto del `prestashop.themeSelectors` del tema):

```js
container:  '.product-container, .js-product-container',
availability: '#product-availability, .js-product-availability',
actions:    '.product-actions, .js-product-actions',
addToCart:  '… .row .product-add-to-cart, … .row .js-product-add-to-cart',
conditionsSelector: '#conditions-to-approve input[type="checkbox"], .js-conditions-to-approve',
```

Casi todos aceptan además el ámbito `.row`, que es lo que tenía el marcado de
1.7.6. Verificado en 8.2.7: un tema de 1.7.6 con `<div class="row">` y
`.product-actions` / `.product-prices` **sigue funcionando**.

Consecuencias prácticas:

- **No hay urgencia** en añadir las clases `js-*` a un tema legacy que funciona.
  Es mejora, no arreglo. Bájalo a la capa 3.
- **Sí hay urgencia** si el tema partió de classic 1.7.8+ y alguien **borró** las
  clases al personalizarlo: ahí el marcado nuevo ya no lleva las clases antiguas
  y no hay red de seguridad.
- `selectors.product.container` está **declarado pero no se usa** en el core de
  8.2.7, así que no tener `.product-container` es inocuo.

Antes de dar por rota la ficha de producto de un tema antiguo, comprobar el
`selectors.js` del core de la versión destino en lugar de suponerlo:

```bash
git show <tag>:themes/_core/js/selectors.js
```

### 🟠 Miniatura: nueva estructura

Se envuelve todo en `.js-product.product` y se añade `.thumbnail-top`, que ahora
contiene la imagen **y** el bloque `.highlighted-informations` (quick view +
variantes), antes hermano de la imagen. Los temas con CSS propio sobre
`.thumbnail-container > .highlighted-informations` pierden el estilo: el selector
directo deja de casar.

### 🟡 Otros cambios de 1.7.8

- `loading="lazy"` + `width` / `height` explícitos en todas las imágenes.
- `<span class="sr-only">Precio</span>` → `aria-label` en el propio `<span class="price">`.
- El `<link rel="canonical">` sale de `product.tpl` y se centraliza en `_partials/head.tpl`.
- `og:url` / `og:title` / `og:site_name` / `og:description` salen de `product.tpl`
  y pasan al bloque `head_open_graph` de `head.tpl`. Dejarlos en ambos sitios
  duplica los meta Open Graph.
- Nuevos: `pagination-seo.tpl`, `subcategories.tpl`, `cart-summary-products.tpl`,
  `cart-summary-top.tpl`.
- jQuery 2.x → 3.5.1: desaparecen `$.browser`, `.load()`, `.size()` y los
  atajos `.focus()` / `.blur()` / `.hover()` como emisores.

---

## 1.7.8.11 → 8.0  🔴 Ruptura en checkout

### 🔴 El formulario de pago cambia de `id`

```smarty
{* ≤ 1.7.8.11 *}  <form id="payment-form" method="POST" action="{$option.action nofilter}">
{* ≥ 8.0 *}       <form id="payment-{$option.id}-form" method="POST" action="{$option.action nofilter}">
```

Es **el cambio más peligroso de toda la migración**. Con un único método de pago
puede parecer que sigue funcionando; con varios, el JS de checkout envía el
formulario equivocado o ninguno. Los módulos de pago que hacen
`$('#payment-form').submit()` dejan de funcionar. Ver `checkout.md`.

### 🔴 `$customer.email` → `$order_customer.email`

En `checkout/order-confirmation.tpl`. Con `PS_DEBUG_MODE` activo es un error
fatal; sin él, sale el aviso "se ha enviado un email a" con el hueco vacío.

### 🟠 Transformación de invitado a cliente

```smarty
{* ≤ 1.7.8.11 *}
{block name='customer_registration_form'}
  {if $customer.is_guest}
    <div id="registration-form" class="card">
      <h4 class="h4">{l s='Save time on your next order, sign up now' …}</h4>
      {render file='customer/_partials/customer-form.tpl' ui=$register_form}

{* ≥ 8.0 *}
{if !$registered_customer_exists}
  {block name='account_transformation_form'}
    <div class="card">
      {include file='customer/_partials/account-transformation-form.tpl'}
```

Fichero nuevo: `customer/_partials/account-transformation-form.tpl`.

### 🟡 Política de contraseñas — **no prioritaria**

Es una comodidad de UX, no un fallo: sin ella el registro funciona igual, solo
que sin medidor de fuerza. Dejarla para el final o descartarla.

Y ojo con el coste real: **la lógica vive en el JS del tema, no en el core**
(verificado en 8.2.7: `password-strength` y `js-hint-password` están dentro del
`theme.js` compilado de classic, y no hay nada en `themes/_core/js/`). En un tema
que no pueda reconstruir su bundle hay que portarla a `custom.js`, así que no es
el cambio de plantilla barato que parece.


Nuevo `_partials/password-policy-template.tpl` y, en todo campo de contraseña:

```smarty
{if isset($configuration.password_policy.minimum_length)}data-minlength="{$configuration.password_policy.minimum_length}"{/if}
{if isset($configuration.password_policy.maximum_length)}data-maxlength="{$configuration.password_policy.maximum_length}"{/if}
{if isset($configuration.password_policy.minimum_score)}data-minscore="{$configuration.password_policy.minimum_score}"{/if}
```

Requiere además envolver el campo en `.field-password-policy` y marcar la columna
con `js-input-column`. Sin esto el medidor de fuerza no aparece y el registro
puede rechazar contraseñas sin explicar por qué.

Se elimina el texto fijo "At least 5 characters long", que ya no es cierto.

### 🟠 Etiqueta de impuestos condicionada

```smarty
{* ≤ 1.7.8.11 *}  {if $configuration.taxes_enabled}{$cart.labels.tax_short}{/if}
{* ≥ 8.0 *}       {if $configuration.display_taxes_label && $configuration.taxes_enabled}{$cart.labels.tax_short}{/if}
```

Afecta a `cart-summary-totals.tpl` y `order-confirmation-table.tpl`.

### 🟠 `displayCrossSellingShoppingCart` se muda

Sale de `checkout/cart-empty.tpl` y entra en `checkout/cart.tpl`. Si el tema
sobreescribe ambos y no se traslada, el bloque de venta cruzada desaparece del
carrito con productos y aparece solo en el vacío, que es justo lo contrario.

### 🟠 Enlaces de subcategoría

```smarty
{* ≤ 1.7.8.11 *}  {$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}
{* ≥ 8.0 *}       {$subcategory.url}
```

También se sustituye `class="replace-2x"` por `class="img-fluid"` y se pasa a
`width` / `height` dinámicos en lugar de `141` / `180` fijos.

### 🔴 Descripción adicional de categoría — contenido que no se publica

Campo de core nuevo en 8.0 (`Category::$additional_description`), que se pinta
**debajo del listado de productos**. Plantilla nueva
`catalog/_partials/category-footer.tpl`, incluida desde `listing/category.tpl`:

```smarty
{block name='product_list_footer'}
    {include file='catalog/_partials/category-footer.tpl' listing=$listing category=$category}
{/block}
```

Un tema anterior a 8.0 no la tiene, así que **el texto que el comerciante escriba
en ese campo no aparece nunca en la web**, sin ningún error ni aviso. Afecta a las
páginas de categoría, que suelen ser las que posicionan.

Tratarlo como **capa 2 (error crítico), no como mejora**, si la consulta
siguiente devuelve filas:

```sql
SELECT id_category, id_lang, LEFT(additional_description, 80)
FROM ps_category_lang
WHERE additional_description IS NOT NULL AND additional_description != '';
```

Detalle completo, con la plantilla y sus condiciones, en `seo.md` § 2.

### 🟡 Resto de 8.0
- Nuevo `errors/410.tpl`.
- Ecotasa solo si el producto no es virtual: `{if !$product.is_virtual && $product.ecotax.amount > 0}`.
- Disponibilidad: `{if $product.quantity > 0}` → `{if $product.quantity >= $product.quantity_wanted}`.
- Personalización: extensiones de fichero dinámicas vía
  `{assign var=authExtensions value=' .'|implode:constant('ImageManager::EXTENSIONS_SUPPORTED')}`
  en lugar del literal ".png .jpg .gif".
- Devoluciones: los productos virtuales dejan de ser devolvibles y aparece el
  enlace de descarga.
- Embalaje reciclable (`$order.details.recyclable`, `$is_recyclable_packaging`).
- Nuevo hook `displayCheckoutBeforeConfirmation` al final del paso de pago.
- Se quita `hidden-sm-down` del breadcrumb (pasa a verse en móvil).
- Se limpian `col-xs-12` redundantes en `cart.tpl`.
- `head-jsonld.tpl`: `"@type": "Thing"` → `"@type": "Brand"`.

---

## 8.0 → 8.1  🟡 Todo mejora, nada rompe

Versión cómoda: no se añade ni se elimina ninguna plantilla. Todo el cambio es
la introducción de `<picture>` con AVIF y WebP. Detalle completo en
`images.md`.

Un tema de 8.0 funciona en 8.1 sin tocar nada; simplemente no sirve WebP.

Se corrigen además los atajos de jQuery ya deprecados (`.focusout()`, `.focus()`,
`.hover()` → `.on(...)`) en el JS del tema.

---

## 8.1 → 8.2

⚠️ **8.2.0 no cuenta.** Se queda con el tema 2.1.3, prácticamente idéntico a
8.1.x salvo un retoque en `shipping.tpl`. Todo lo de abajo entra en **8.2.1**
(tema 2.2.0). Al datar una tienda "8.2" hay que mirar el patch.

🟡 Tres cambios:

- `checkout/_partials/steps/shipping.tpl`: se quita la clase `row` de
  `.delivery-option` y de `.carrier-extra-content` (se reañade en 9.0 — ver
  abajo), y el contenido extra del transportista se oculta también cuando está
  seleccionado pero vacío:
  ```smarty
  {if ($delivery_option != $carrier_id) || ($delivery_option == $carrier_id && empty($carrier.extraContent))} style="display:none;"{/if}
  ```
- `contact.tpl`: columnas laterales `col-xs-12 col-sm-4 col-md-3` →
  `col-xs-12 col-md-4 col-lg-3`.
- `customer/password-new.tpl`: se le añade la política de contraseñas que en 8.0
  solo estaba en el registro.

---

## 8.2 → 9.0  🔴🔴 La migración más dura

Dos cambios de fondo simultáneos: el **salto de Smarty** y el **nuevo sistema de
presenters**. Una plantilla de 8.x no arranca en 9.0 sin tocarla.

⚠️ **No es "Smarty 5".** Verificado en el `composer.lock` de cada tag:

| PrestaShop | Smarty instalado |
|---|---|
| 8.2.7 | **v4.3.4** |
| 9.0.0 | **v4.5.5** |
| 9.1.4 | v4.5.5 |
| 9.2.0-beta.1 | v4.5.5 |

Es un salto **dentro de Smarty 4** (4.3 → 4.5), no un cambio de major. El
`composer.json` declara `^4.3.1` en 8.2 y en 9.x por igual; lo que cambia es la
versión resuelta. **PrestaShop no usa Smarty 5 en ninguna versión publicada
hasta hoy.** Importa saberlo: buscar guías de migración "Smarty 5" lleva a
documentación que no aplica, y hace pensar en una reescritura que no existe.

### 🔴 `{else if}` deja de existir

```smarty
{* ≤ 8.2 — válido *}     {else if $layout === 'layouts/layout-right-column.tpl'}
{* ≥ 9.0 — obligatorio *} {elseif $layout === 'layouts/layout-right-column.tpl'}
```

Es un **error de compilación**, no un aviso. Buscar `{else if}` (con espacio) en
todo el tema, incluidos los overrides de módulos en `themes/<tema>/modules/`.
Es el primer grep de cualquier migración a 9.

### 🔴 `constant()` fuera

```smarty
{* ≤ 8.2 *}  {assign var=authExtensions value=' .'|implode:constant('ImageManager::EXTENSIONS_SUPPORTED')}
{* ≥ 9.0 *}  {assign var=authExtensions value=' .'|implode:ImageManager::EXTENSIONS_SUPPORTED}
```

`ImageManager` está registrada como clase accesible desde Smarty en 9.0.

### 🔴 Presenters: el fabricante pasa de objeto a array

El cambio con más superficie de impacto. En `catalog/_partials/product-details.tpl`:

```smarty
{* ≤ 8.2 *}
{if isset($product_manufacturer->id)}
  {if isset($manufacturer_image_url)}
    <a href="{$product_brand_url}">
      <img src="{$manufacturer_image_url}" class="img img-fluid manufacturer-logo" alt="{$product_manufacturer->name}" loading="lazy">
    </a>
  {else}
    <a href="{$product_brand_url}">{$product_manufacturer->name}</a>
  {/if}
{/if}

{* ≥ 9.0 *}
{if !empty($product_manufacturer.id)}
  {assign var="product_manufacturer_image_key" value="`$product_manufacturer.id`-"}
  {if !empty($product_manufacturer.image.small.url) && strpos($product_manufacturer.image.small.url, $product_manufacturer_image_key)}
    <a href="{$product_manufacturer.url}">
      <picture>
        {if !empty($product_manufacturer.image.small.sources.avif)}<source srcset="{$product_manufacturer.image.small.sources.avif}" type="image/avif">{/if}
        {if !empty($product_manufacturer.image.small.sources.webp)}<source srcset="{$product_manufacturer.image.small.sources.webp}" type="image/webp">{/if}
        <img src="{$product_manufacturer.image.small.url}" alt="{if !empty($product_manufacturer.image.legend)}{$product_manufacturer.image.legend}{else}{$product_manufacturer.name}{/if}" class="img img-fluid manufacturer-logo" loading="lazy">
      </picture>
    </a>
  {else}
    <a href="{$product_manufacturer.url}">{$product_manufacturer.name}</a>
  {/if}
{/if}
```

Tabla de equivalencias:

| ≤ 8.2 | ≥ 9.0 |
|---|---|
| `$product_manufacturer->id` | `$product_manufacturer.id` |
| `$product_manufacturer->name` | `$product_manufacturer.name` |
| `$product_brand_url` | `$product_manufacturer.url` |
| `$manufacturer_image_url` | `$product_manufacturer.image.small.url` |
| `$category.image.*` | `$category.cover.*` |
| `$subcategory.image.*` | `$subcategory.thumbnail.*` |
| `$brand.image` (string con la URL) | `$brand.image.bySize.small_default.url` |
| `$brand.nb_products` (número suelto) | mismo dato, pero ya traducido con `{l s='%number% products'}` |

Buscar en todo el tema `->` dentro de plantillas: en 9.0 casi cualquier acceso
con flecha a un presenter es sospechoso.

### 🔴 `catalog/brands.tpl` desaparece

`manufacturers.tpl` y `suppliers.tpl` dejaban de extender `brands.tpl` y pasan a
extender `$layout` directamente. Nuevo `catalog/_partials/miniatures/supplier.tpl`.

Un tema que sobreescriba `catalog/brands.tpl` se queda con un fichero huérfano
que ya nadie incluye: las páginas de marcas y proveedores vuelven al diseño de
classic sin avisar.

### 🟠 Disponibilidad del producto rehecha

`catalog/_partials/product-add-to-cart.tpl` pasa de `<span>` con iconos sueltos a
un `<div class="alert alert-{color}">`, y aparece un estado nuevo, `in_stock`,
más un submensaje:

```smarty
{if $product.availability == 'in_stock'}
  {assign 'availability_icon' 'E5CA'}{assign 'availability_color' 'success'}
{elseif $product.availability == 'available' || $product.availability == 'last_remaining_items'}
  {assign 'availability_icon' 'E002'}{assign 'availability_color' 'warning'}
{else}
  {assign 'availability_icon' 'E14B'}{assign 'availability_color' 'danger'}
{/if}

<div class="alert alert-{$availability_color}" role="alert">
  <div class="alert-content-wrapper">
    <div><i class="material-icons rtl-no-flip">&#x{$availability_icon};</i></div>
    <div>
      <div>{$product.availability_message}</div>
      {if !empty($product.availability_submessage)}<div><small>{$product.availability_submessage}</small></div>{/if}
    </div>
  </div>
</div>
```

Un tema con CSS sobre `#product-availability span` o sobre
`.product-available` / `.product-last-items` / `.product-unavailable` pierde el
estilo. El id `#product-availability` y la clase `js-product-availability` se
mantienen: el JS sigue funcionando, solo cambia el aspecto.

También se quita `clearfix` de `.product-quantity`.

### 🟡 Resto de 9.0

- `<meta name="keywords">` eliminado de `head.tpl`. Google lo ignora desde hace
  años; quitarlo no tiene efecto SEO.
- Subcategorías: se añade el *fallback* a `$urls.no_picture_image` cuando la
  subcategoría no tiene imagen (antes no salía nada).
- `fetchpriority="high"` en la portada de categoría, y sus `width` / `height`
  pasan a ser dinámicos en lugar de `141` / `180`.
- `cms/stores.tpl`: el día de la semana deja de truncarse a 4 caracteres.
- `shipping.tpl`: vuelve la clase `row` en `.delivery-option` y
  `.carrier-extra-content`; y `carrier-extra-content` se oculta solo por
  `{if $delivery_option != $carrier_id}` (se revierte la condición de 8.2).
- Personalización: `maxlength` de 250 → **1024** caracteres, y el texto de ayuda
  correspondiente.
- Detalle de pedido: se simplifica la cantidad, que ya no itera las
  personalizaciones; `{$product.quantity}` directo.
- `{if $product.ean13},"gtin13"…{else if $product.upc}` → `{elseif}` en el JSON-LD.
- jQuery 3.5.1 → 3.7.1; `node-sass` → `sass` (Dart Sass). Si el tema tiene su
  propio `_dev`, hay que migrar el build: Dart Sass no admite `@import` con las
  mismas reglas y avisa de deprecación.

---

## 8.2.1 → 8.2.8 (tema 2.2.1)  🟡 Backport útil

La rama 2.2.x sigue viva y 2.2.1 backportea desde 9.0 tres cosas, todas seguras:

```smarty
{* product-jsonld.tpl y contact.tpl *}
{else if …}  →  {elseif …}

{* miniatures/pack-product.tpl *}
+ width="{$product.default_image.medium.width}"
+ height="{$product.default_image.medium.height}"

{* cms/stores.tpl *}
- <th>{$day.day|truncate:4:'.'}</th>
+ <th>{$day.day}</th>
```

💡 **`{elseif}` es válido en Smarty 3, 4 y 5.** Que classic lo backportee a la
rama 8.2 lo confirma: se puede hacer el reemplazo `{else if}` → `{elseif}` **hoy**
en un tema de 8.x sin ningún riesgo, y así el día que toque migrar a 9.0 ese
frente ya está cubierto. Es la única parte de la migración a 9 que se puede
adelantar gratis, y conviene proponerla cuando el cliente aún no quiere subir.

---

## 9.0.x — las features siguen entrando en los patches

Corrección importante frente a lo que uno esperaría: **buena parte de lo que
parece "de 9.1" ya está en los patches de 9.0**. La rama 3.0.x del tema siguió
recibiendo funcionalidad, no solo correcciones.

| Tema | PrestaShop | Qué entra |
|---|---|---|
| 3.0.3 | **9.0.1** | hook `displayCartExtraProductInfo`; hook `displayCustomerAccountTop`; tipo de campo `textarea`; atributo `minlength` |
| 3.0.4 | **9.0.2** | multi-envío: `order-confirmation-table-multishipment.tpl` y `order-final-summary-table-multishipment.tpl` |
| 3.0.5 | **9.0.3** | nombre del transportista en el historial: `order-carrier.tpl`, `order-shipments.tpl` y los parciales `order-detail-product-line-*` |
| 3.0.6 | (pendiente de release) | backport de `$product.quantity_required` |

Consecuencia práctica: **una tienda 9.0.3 necesita el mismo trabajo de
multi-envío que una 9.1**. No sirve razonar por minor.

### 🟠 Hook nuevo en la cuenta de cliente (9.0.1)

En `customer/my-account.tpl`:

```smarty
{block name='display_customer_account_top'}
    {hook h='displayCustomerAccountTop'}
{/block}
```

Un tema que sobreescriba `my-account.tpl` y no lo añada deja sin sitio a los
módulos que se enganchan ahí.

---

## 9.1  🟠 Multi-envío consolidado

### 🟠 Multi-envío (multishipment)

Introducido en el tema 3.0.4 (**PS 9.0.2**), no en 9.1. Va tras *feature flag*.
Plantillas nuevas, repartidas entre 3.0.4 y 3.0.5:

```
checkout/_partials/order-confirmation-table-multishipment.tpl
checkout/_partials/order-final-summary-table-multishipment.tpl
customer/_partials/order-carrier.tpl
customer/_partials/order-shipments.tpl
customer/_partials/order-detail-no-return-multishipment.tpl
customer/_partials/order-detail-return-multishipment.tpl
customer/_partials/order-detail-product-line-{no-return,return}[-mobile].tpl
```

El patrón que usa classic — y que conviene copiar — es bifurcar con la variable
protegida, para que la plantilla siga siendo válida en 9.0:

```smarty
{if isset($is_multishipment_enabled) && $is_multishipment_enabled}
  {include file='checkout/_partials/order-confirmation-table-multishipment.tpl' products=$order.order_shipments …}
{else}
  {include file='checkout/_partials/order-confirmation-table.tpl' products=$order.products …}
{/if}
```

Variables nuevas: `$is_multishipment_enabled`, `$order.order_shipments`,
`$products_carrier_mapping`.

El bloque de dirección de envío en `order-final-summary.tpl` se oculta en modo
multi-envío:

```smarty
{if !$cart.is_virtual && isset($is_multishipment_enabled) && !$is_multishipment_enabled}
```

Si el tema sobreescribe el detalle de pedido y **no** implementa multi-envío, no
pasa nada mientras el *feature flag* esté desactivado. Con el flag activo, el
cliente ve un único transportista para todo el pedido, que es incorrecto.

### 🟠 Cantidad mínima del producto

```smarty
{* 9.0 *}
{if $product.quantity_wanted}
  value="{$product.quantity_wanted}" min="{$product.minimal_quantity}"
{else}
  value="1" min="1"
{/if}

{* 9.1 *}
value="{if $product.quantity_wanted}{$product.quantity_wanted}{else}1{/if}"
min="{if $product.quantity_required}{$product.quantity_required}{else}1{/if}"
```

Variable nueva `$product.quantity_required`, que sustituye a
`$product.minimal_quantity` como mínimo del input.

### 🟠 `theme.yml`: el placeholder `- ~` en los hooks

Cambio de 3.1.0 fácil de pasar por alto y con consecuencias muy visibles:

```yaml
hooks:
  modules_to_hook:
    displayNav2:
      - ps_languageselector
      - ps_currencyselector
      - ps_customersignin
      - ps_shoppingcart
      # Keep existing hooks and append them after
      - ~
```

Sin el `- ~` final, **instalar el tema borra de ese hook los módulos que no
estén listados**. El cliente instala el tema nuevo y desaparecen los módulos de
terceros que tenía enganchados en la cabecera, el pie o la cuenta de cliente.

El `- ~` se añade en 3.1.0 a los 22 hooks del `theme.yml`. Al escribir el
`theme.yml` de un tema propio para 9.1+, incluirlo siempre.

### 🟡 Resto de 9.1

- Meta Open Graph: se quitan las barras de autocierre (`<meta … />` → `<meta …>`)
  para que el HTML valide. Puramente cosmético.
- Capa de compatibilidad para pedidos antiguos sin envíos asociados, y se deja de
  mostrar el coste de envío en el detalle cuando hay multi-envío.

⚠️ El tipo de campo `textarea`, el `minlength` y el hook
`displayCartExtraProductInfo` **no son de 9.1**: entran en 9.0.1 (tema 3.0.3).
Ver la sección 9.0.x de arriba.

---

## 9.1.2+ (tema 3.1.2)  🟡

### 🟡 Breadcrumb: bloques nuevos

```smarty
{* ≤ 3.1.1 *}
<ol>
  {block name='breadcrumb'}
    {foreach from=$breadcrumb.links item=path name=breadcrumb}
      {block name='breadcrumb_item'}…{/block}
    {/foreach}
  {/block}
</ol>

{* ≥ 3.1.2 *}
{block name='breadcrumb_list'}
  <ol>
    {block name='breadcrumb_items'}
      {foreach from=$breadcrumb.links item=path name=breadcrumb}
        {block name='breadcrumb_item'}…{/block}
      {/foreach}
    {/block}
  </ol>
{/block}
```

Se corrige una declaración duplicada del bloque `breadcrumb` y se abren dos
puntos de extensión nuevos, `breadcrumb_list` y `breadcrumb_items`. **El bloque
`breadcrumb` deja de existir**: un tema hijo que hiciera
`{block name='breadcrumb'}` para sobreescribir la lista deja de tener efecto — en
silencio, sin error. Pasa a `breadcrumb_items`.

### 🟡 Formulario de contacto

En `modules/contactform/views/templates/widget/contactform.tpl`, cuando solo hay
un destinatario configurado se oculta el desplegable "Asunto" y se manda un
`<input type="hidden">`. Además se escapan con `|escape:'htmlall':'UTF-8'` el
`id_contact` y el nombre, que antes iban sin escapar.

---

## 9.1 — cambios del core, no del tema

Estos no salen del diff de classic, pero afectan a temas y módulos. Fuente:
<https://devdocs.prestashop-project.org/9/modules/core-updates/9.1/>

### 🟠 `Theme::getDefaultTheme()` deja de devolver `"classic"`

Ya no devuelve el literal hardcodeado, sino el tema por defecto configurado
(issue [PrestaShop#37480](https://github.com/PrestaShop/PrestaShop/issues/37480),
cerrado, milestone 9.1.0).

Cualquier código —de módulo o de tarea de despliegue— que asumiera el literal
`"classic"` deja de ser válido.

### ✅ Ninguna tienda actualizada cambia de tema

Verbatim: *"Existing installations upgrading from 9.0.x keep their current theme.
Hummingbird is only set as default for fresh installations."*

Es decir: **el cambio de tema por defecto no rompe nada al actualizar**. La
ruptura solo aparece en instalaciones nuevas, o si alguien activa Hummingbird a
mano. No hay que hacer nada al subir a 9.1 por este motivo.

### 🔴 Si —y solo si— se activa Hummingbird: Bootstrap alpha.5 → 5.3.3

Verbatim: *"Hummingbird introduces compatibility breaks compared to the Classic
theme. If module developers wish to ensure their modules remain compatible, we
invite them to review the changes listed below."*

Renombrados que cita la propia doc: `.no-gutters`→`.g-0`,
`.custom-select`→`.form-select`, `.close`→`.btn-close`,
`.ml-*`/`.mr-*`→`.ms-*`/`.me-*`, `.sr-only`→`.visually-hidden`,
`data-toggle`→`data-bs-toggle`.

Tablas exhaustivas en `hummingbird.md` § 4.

### 🟠 Hooks que cambian en Hummingbird

| Hook | Cambio |
|---|---|
| `displaySearch` | **eliminado** |
| `$HOOK_DISPLAYORDERDETAIL` (variable-hook) | **sustituido** por el hook `displayOrderDetail` |
| `displayModalContent` | **nuevo** |

Mapeo completo en `/9/themes/hummingbird/hooks/`.

⚠️ **No verificado**: el listado íntegro de hooks eliminados o renombrados. Esa
página se renderiza por JavaScript y no se ha podido extraer. **No afirmar nada
más allá de estos tres.**

### 🟡 jQuery "deprecado" en Hummingbird — con matiz

Verbatim: *"JQuery is now deprecated and will be removed in the next major
Hummingbird [version]"*.

Matiz verificado en código: jQuery 3.7.1 + jquery-migrate 3.4.0 **se siguen
cargando hoy** vía `themes/core.js`, porque Hummingbird no declara
`theme_settings.core_scripts: false`. **Un módulo con jQuery no se rompe hoy.**
Es deuda técnica aplazada, no resuelta. Ver `hummingbird.md` § 6.1.

---

## 9.2  🟠 Sobre todo el checkout de una página

⚠️ **A 24-07-2026 no existe 9.2 estable**: todo esto sale de `9.2.0-beta.1`
(2026-07-22) y de la doc oficial, y hay que revalidarlo cuando salga la estable.

Fuentes:
- <https://devdocs.prestashop-project.org/9/modules/core-updates/9.2/>
- <https://devdocs.prestashop-project.org/9/modules/checkout/theme-developers/>

Declaración oficial de intención: *"As a minor version, PrestaShop 9.2 strives to
maintain backward compatibility with 9.1.x. Breaking changes are only introduced
when absolutely necessary."*

**La doc oficial no documenta ninguna plantilla eliminada, ninguna plantilla nueva
del core, ningún cambio en variables del presenter ni ningún hook de front
eliminado.** Eso no prueba que no los haya: prueba que no están documentados.

### 🔴 One Page Checkout (`ps_onepagecheckout`) — el cambio grande

Módulo nativo empaquetado. Es lo que más afecta a plantillas en 9.2.

**Mecanismo**: se activa vía `hookDisplayOverrideTemplate`, interceptando
`template_file === 'checkout/checkout'` cuando el controlador es
`OrderController`, y devolviendo
`module:ps_onepagecheckout/views/templates/front/checkout/checkout.tpl`.

**Variable nueva**: `$is_one_page_checkout_enabled`.

🔴 **Solo está asignada en la página de checkout.** Fuera de ahí hay que guardar
siempre:

```smarty
{if isset($is_one_page_checkout_enabled) && $is_one_page_checkout_enabled}
```

**Variables nuevas del contexto OPC**: `$formFields`, `$contactFields`,
`$additionalCustomerFields`, `$useSameAddressField`, `$deliveryFields`,
`$invoiceFields`, `$invoiceMetaFields`, `$delivery_options`,
`$selected_delivery_option`, `$payment_options`, `$selected_payment_module`,
`$selected_payment_selection_key`, `$conditions_to_approve`,
`$validation_errors`, `$validation_error_messages`, `$opc_urls`,
`$hookDisplayBeforeCarrier`, `$hookDisplayAfterCarrier`.

**Contrato DOM obligatorio** si se sobreescriben las plantillas del OPC. Estos
selectores son API, no decoración — quitarlos rompe el pago sin dar error:

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

**Eventos nuevos del bus `prestashop.on()`**: familias `opcCarriers*`,
`opcPaymentMethods*`, `opcDeliveryAddressSelected`, `opcBillingAddressSelected`,
`updatedOpcAddressForm`, `opcGuestInit*`, `opcFinalSubmitStarted`,
`opcFormValidated`, `opcSubmitFailed`, `opcCartSummaryUpdated`.

**Objeto runtime**: `window.ps_onepagecheckout` (`enabled`, `urls`, `messages`).
Prohibido hardcodear endpoints.

**CSS base**: `views/public/one-page-checkout.css`, handle
`module-ps-onepagecheckout`, prioridad 200.

**Hook nuevo para módulos**: `actionCheckoutBuildProcess`.

🔴 **Aviso literal para migración de temas**: funciona out-of-the-box en temas
creados a partir de Hummingbird. Textual:

> *"If your theme was created from Classic or provides a custom theme structure,
> overrides of the One Page Checkout views will be broader"*

Traducido a presupuesto: **un tema hijo de classic necesita bastante más trabajo
de override para el OPC**. Si el cliente quiere el checkout de una página en 9.2,
ese coste va en la propuesta, y hay que verificarlo con el protocolo de
`checkout.md` de principio a fin.

### 🟠 JSON-LD se mueve al core

En vez de montar los datos estructurados en las plantillas, se aportan como
**arrays** vía el hook `actionFrontControllerSetVariables` + método
`getStructuredData()`, y el tema los renderiza. Permite a los módulos añadir,
sobreescribir o quitar `AggregateRating`, `MerchantReturnPolicy` o `ContactPoint`
**sin tocar plantillas**. Módulo de ejemplo: `demoseo`.

**Marcador en plantilla**: `{if !empty($structured_data)}` en
`templates/_partials/head.tpl`. El `head.tpl` de Hummingbird lo tiene; el de
classic `develop` **no**.

🔴 **Riesgo concreto**: si el tema mantiene sus `_partials/microdata/*.tpl`
manuales, convivirán con el JSON-LD del core y se puede **describir la misma
entidad dos veces**. Auditarlo con la herramienta de resultados enriquecidos
antes de dar por buena la migración. Vale aquí la misma regla de `seo.md` § 1:
no reemplazar lo que ya funciona, solo evitar el duplicado.

### 🟠 El enum de condición de producto se amplía

Además de `new`, `used` y `refurbished`, ahora existen **`open_box`, `damaged`,
`new_with_defects`**.

Cualquier `.tpl` o módulo que muestre, filtre o liste la condición con un `{if}`
o un `{switch}` cerrado sobre los tres valores antiguos **dejará casos sin
cubrir** — sin error, simplemente no pintando nada. Auditar los usos de
`$product.condition` y `$product.condition.schema_url`.

### 🟡 Extra Properties

Los campos extra registrados por módulos quedan accesibles automáticamente en el
front. **El código está marcado `@experimental`**: el propio equipo se reserva
romper compatibilidad. No construir nada crítico encima.

### 🟡 Resto de 9.2 (no es de tema, pero conviene conocerlo)

- Flags de páginas migradas a Symfony que pasan a estable: `country`,
  `merchandise_return`, `hook_module_v2`, `quick_access`,
  `email_body_translation`, `tax_rules_group`. En beta: `new_pricing`,
  `improved_b2b`.
- `config/services.php` y `services-9.2.yml` como alternativas a `services.yml`.
- Comandos CLI nuevos: `prestashop:module:list`,
  `prestashop:employee:create-admin`, `prestashop:employee:change-password`,
  `prestashop:htaccess:generate`.
- El instalador borra `install/` automáticamente.
- Cambios de esquema en `9.2.0.sql`.

### ✅ Lo que NO cambia en 9.2

PHP (`>=8.1`), Symfony (`~6.4`), **Smarty (v4.5.5, igual que 9.0 y 9.1)**, jQuery
(3.7.1 + migrate 3.4.0), el `framework` de classic
(`bootstrap-v4.0.0-alpha.5`), `{extends}`/`{block}`/`parent:`/`{hook}`/`{widget}`,
los overrides de módulo, `custom.css`/`custom.js` y la herencia padre/hijo.

**Conclusión operativa: para un tema, el salto costoso de la rama 9 es 8.2 → 9.0
(sintaxis de Smarty y presenters). 9.1 no rompe nada si se mantiene classic, y
9.2 añade sobre todo el contrato del One Page Checkout.**

---

## No verificado en 9.1 / 9.2 — no afirmar

1. Fecha de release estable de 9.2. **No anunciada.**
2. Versión exacta de classic y de Hummingbird que llevará el 9.2.0 estable.
3. Requisitos PHP oficiales de 9.2 (devdocs no tiene fila para 9.2).
4. Si en 9.2 hay cambios en variables del presenter o plantillas eliminadas.
5. Listado íntegro de hooks eliminados o renombrados en Hummingbird.
6. Fecha de fin de soporte de classic. **No existe ningún anuncio.**
