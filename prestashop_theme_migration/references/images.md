# Imágenes: WebP, AVIF, lazy loading y `<picture>`

## Cuándo entró cada cosa

| Versión | Cambio |
|---|---|
| ≤ 1.7.7 | `<img src="…">` pelado, sin dimensiones ni lazy |
| **1.7.8** | `loading="lazy"` + `width` / `height` explícitos |
| 8.0 | dimensiones dinámicas en subcategorías (antes `141`/`180` fijos) |
| **8.1** | **`<picture>` con `<source>` AVIF y WebP** — el cambio clave |
| 9.0 | `fetchpriority="high"` en la portada de categoría; `<picture>` en logo de marca y ficha de fabricante |

La compatibilidad WebP/AVIF del tema llega en **8.1** (classic 2.1.x). El núcleo
genera los formatos alternativos y los expone en el presenter como
`sources.webp` y `sources.avif`; el tema solo tiene que consumirlos.

## El patrón `<picture>` de 8.1+

Es siempre el mismo, con AVIF primero, WebP después y el `<img>` original como
último recurso. El navegador elige el primer `<source>` que entiende:

```smarty
<picture>
  {if !empty($product.cover.bySize.home_default.sources.avif)}<source srcset="{$product.cover.bySize.home_default.sources.avif}" type="image/avif">{/if}
  {if !empty($product.cover.bySize.home_default.sources.webp)}<source srcset="{$product.cover.bySize.home_default.sources.webp}" type="image/webp">{/if}
  <img
    src="{$product.cover.bySize.home_default.url}"
    alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
    loading="lazy"
    data-full-size-image-url="{$product.cover.large.url}"
    width="{$product.cover.bySize.home_default.width}"
    height="{$product.cover.bySize.home_default.height}"
  />
</picture>
```

Los `{if !empty(...)}` **no son opcionales**. Si el formato no está generado,
`sources.webp` no existe y un `<source srcset="">` vacío hace que el navegador lo
elija igualmente y no muestre nada: imagen rota en toda la tienda. Es el error
más frecuente al copiar este patrón a mano.

Este marcado es retrocompatible: en 8.0 o inferior los `sources` no existen, los
`{if}` no entran y queda el `<img>` de siempre. **Se puede aplicar sin miedo a un
tema que aún deba funcionar en versiones anteriores.**

## Plantillas que llevan `<picture>` en 8.1

```
catalog/_partials/category-header.tpl
catalog/_partials/miniatures/category.tpl
catalog/_partials/miniatures/pack-product.tpl
catalog/_partials/miniatures/product.tpl      ← la más importante
catalog/_partials/product-cover-thumbnails.tpl
catalog/_partials/product-images-modal.tpl
catalog/_partials/subcategories.tpl
```

En 9.0 se suman `miniatures/brand.tpl`, `miniatures/supplier.tpl` y
`product-details.tpl` (logo del fabricante).

Rutas de datos según el contexto:

| Contexto | Ruta |
|---|---|
| Miniatura de producto | `$product.cover.bySize.home_default.sources.{webp,avif}` |
| Ficha, imagen grande | `$product.default_image.bySize.large_default.sources.*` |
| Miniaturas de la ficha | `$image.bySize.small_default.sources.*` |
| Sin imagen | `$urls.no_picture_image.bySize.<tamaño>.sources.*` |
| Categoría (≤ 8.2) | `$category.image.large.sources.*` |
| Categoría (≥ 9.0) | `$category.cover.large.sources.*` |
| Subcategoría (≤ 8.2) | `$subcategory.image.large.sources.*` |
| Subcategoría (≥ 9.0) | `$subcategory.thumbnail.large.sources.*` |

Ojo con los renombrados de 9.0 (`image` → `cover` / `thumbnail`): ver
`breaking-changes.md` § 9.0.

## 🔴 El JS que acompaña a `<picture>`

Aquí está la trampa de 8.1. Al cambiar de miniatura en la ficha de producto, el
JS antiguo hacía:

```js
productCover.attr('src', newSelectedThumb.data('image-medium-src'));
```

Con `<picture>` eso **ya no basta**: los `<source>` siguen apuntando a la imagen
anterior y tienen prioridad sobre el `src` del `<img>`. Resultado: se pincha una
miniatura, el `src` cambia, pero se sigue viendo la imagen antigua. Y solo pasa
en navegadores con soporte WebP/AVIF, con lo que es fácil no reproducirlo.

8.1 lo resuelve con un módulo nuevo, `_dev/js/components/update-sources.js`, y
reescribe el intercambio en `product.js`:

```js
import updateSources from './components/update-sources';

const swipe = (selectedThumb, thumbParent) => {
  const newSelectedThumb = thumbParent.find(prestashop.themeSelectors.product.thumb);

  selectedThumb.removeClass('selected');
  newSelectedThumb.addClass('selected');

  modalProductCover.prop('src', newSelectedThumb.data('image-large-src'));
  productCover.prop('src', newSelectedThumb.data('image-medium-src'));

  productCover.attr('title', newSelectedThumb.attr('title'));
  modalProductCover.attr('title', newSelectedThumb.attr('title'));
  productCover.attr('alt', newSelectedThumb.attr('alt'));
  modalProductCover.attr('alt', newSelectedThumb.attr('alt'));

  updateSources(productCover, newSelectedThumb.data('image-medium-sources'));
  updateSources(modalProductCover, newSelectedThumb.data('image-large-sources'));
};
```

Y en la plantilla de miniaturas hay que exponer los sources como JSON:

```smarty
<img
  class="thumb js-thumb {if $image.id_image == $product.default_image.id_image} selected js-thumb-selected {/if}"
  data-image-medium-src="{$image.bySize.medium_default.url}"
  {if !empty($image.bySize.medium_default.sources)}data-image-medium-sources="{$image.bySize.medium_default.sources|@json_encode}"{/if}
  data-image-large-src="{$image.bySize.large_default.url}"
  {if !empty($image.bySize.large_default.sources)}data-image-large-sources="{$image.bySize.large_default.sources|@json_encode}"{/if}
  src="{$image.bySize.small_default.url}"
  …
>
```

**Regla:** si se añade `<picture>` a un tema, hay que añadir también el
`updateSources` y los `data-image-*-sources`. Poner solo el `<picture>` es peor
que no ponerlo, porque rompe la galería de producto.

### ⚠️ Zooms de terceros: elevateZoom, fancybox y compañía

Muchos temas comerciales no usan el intercambio de imágenes de classic, sino un
plugin de zoom (elevateZoom, Cloud Zoom, drift…) que manipula el `src` por su
cuenta y del que **no se controla el código**. Ahí `updateSources` no basta:
habría que engancharse a los eventos internos del plugin, o vigilar el `src` con
un `MutationObserver`.

Regla práctica, y la más segura cuando no se puede probar en un navegador:

> **`<picture>` solo en imágenes cuyo `src` no cambie nunca en caliente.**

En la práctica eso deja:

| Imagen | ¿`<picture>`? |
|---|---|
| **Miniatura de listado si hay selector de variantes** | ❌ **no** — ver abajo |
| Miniatura de listado sin variantes | ✅ sí |
| Miniaturas de categoría y de pack | ✅ sí |
| Portada de ficha (`#zoom_product`, `.js-qv-product-cover`) | ❌ no si hay plugin de zoom |
| Portada del modal (`.js-modal-product-cover`) | ❌ no |
| Miniaturas de la ficha si el tema les reescribe el `src` | ❌ no |
| Imagen "sin foto" (fallback estático) | ✅ sí |

### ⚠️ Las miniaturas de listado también se intercambian

Es el caso menos evidente y el que más fácil se cuela. Muchos temas comerciales
permiten **elegir color o talla desde el propio listado**, y al hacerlo cambian
la miniatura por AJAX:

```js
$product_article_e.find('.product-thumbnail img')
  .attr('src', obj.product_cover.bySize.home_default.url)
  .attr('alt', obj.product_cover.legend);
```

Con `<picture>` encima, ese `src` se escribe pero **no se ve**: los `<source>`
mandan. El cliente pulsa "azul" y le sigue apareciendo la foto roja, solo en
navegadores con WebP. Indicio barato de que la tienda tiene esto: las URLs de
producto del listado acaban en un ancla de combinación, tipo `#/28-talla-0`.

### Cómo detectarlo antes de tocar nada

Buscar en **todo** el JS —tema y módulos—, no solo en el del tema:

```bash
grep -rn "attr('src'\|prop('src'\|\.src *=" \
  <tema>/assets/js/ modules/*/views/js/ | grep -i "thumb\|cover\|product"
```

Cualquier imagen que aparezca ahí como destino de una escritura de `src` **no
lleva `<picture>`**.

Si aparece el `src` siendo reescrito sobre una imagen, esa imagen no lleva
`<picture>`. Se pierde WebP en esa imagen concreta —normalmente la más grande,
lo cual escuece— pero se conservan las dimensiones, el `fetchpriority` y el
`alt`, que ya son ganancia. Y la galería sigue funcionando, que es lo
innegociable.

**Un tema con el JS compilado y sin fuentes no es un impedimento.** El
`updateSources` es código *añadido*, y `assets/js/custom.js` lo carga el core en
todos los temas con prioridad 1000, o sea después del `theme.js` del tema. Basta
con enganchar ahí un handler de click sobre las miniaturas que actualice los
`<source>` además de lo que ya haga el tema:

```js
// assets/js/custom.js — se ejecuta después del theme.js
$(document).on('click', '.js-thumb, .thumb', function () {
  var $t = $(this);
  updateSources($('.js-qv-product-cover'),      $t.data('image-medium-sources'));
  updateSources($('.js-modal-product-cover'),   $t.data('image-large-sources'));
});
```

Ajustar los selectores a los del tema. Lo único que hay que vigilar es el orden:
si el handler del tema repinta el `<img>` después, hay que reaccionar a ese
evento en lugar de al click.

Las miniaturas del listado (`miniatures/product.tpl`) son seguras en cualquier
caso: ahí no hay intercambio dinámico y no hace falta JS ninguno.

Ver `SKILL.md` § 1.bis para las vías de modificación del JS de un tema.

## Paso operativo: sin regenerar miniaturas no hay WebP

La plantilla solo *consume* `sources`. Esos ficheros los genera PrestaShop al
crear las miniaturas, así que después de activar WebP/AVIF en el back office hay
que **regenerar las imágenes** para que existan.

Mientras no se regeneren, `sources` viene vacío, los `{if !empty()}` no entran y
se sirve el JPG de siempre: no se rompe nada, pero tampoco se gana nada. Es la
causa habitual de "he puesto el `<picture>` y no ha mejorado el PageSpeed".

En catálogos grandes la regeneración tarda y consume disco (cada miniatura pasa a
ocupar dos o tres ficheros). Contarlo antes, y comprobar espacio.

Verificación rápida de que existen de verdad, sin depender del inspector:

```bash
ls img/p/**/*.webp | head    # deben aparecer junto a los .jpg
```

## Lazy loading (1.7.8+)

`loading="lazy"` en todas las imágenes salvo las que están sobre el pliegue.
Desde 9.0, la portada de categoría usa lo contrario:

```smarty
<img src="{$category.cover.large.url}" … fetchpriority="high" width="{$category.cover.large.width}" height="{$category.cover.large.height}">
```

Sin `loading="lazy"` y con `fetchpriority="high"`, porque es el LCP de la página
de categoría. Poner `lazy` ahí penaliza Core Web Vitals en lugar de mejorarlos.

### 🔴 Classic lazy-carga su propio LCP: el bug que hereda todo tema hijo

Classic aplica `loading="lazy"` **también donde no debe**, y ningún tema derivado
de classic lo ha corregido nunca porque viene del original. Verificado en
classic **3.1.2** (PrestaShop 9.1.2+), y presente igual desde 1.7.8:

| Fichero | Línea | Imagen | Problema |
|---|--:|---|---|
| `catalog/_partials/product-cover-thumbnails.tpl` | 41 | `class="js-qv-product-cover"` (`large_default`) | **es el LCP de la ficha de producto** |
| `catalog/_partials/product-cover-thumbnails.tpl` | 56 | imagen "sin foto" (`large_default`) | mismo caso |
| `catalog/_partials/miniatures/product.tpl` | 39 | miniatura de listado (`home_default`) | **lazy también en la primera fila**, sobre el pliegue |

La imagen principal de la ficha es, en la práctica totalidad de las tiendas, el
elemento LCP. Marcarla `lazy` **retrasa deliberadamente su descarga**: el
navegador no la pide hasta que resuelve el layout. Es un antipatrón reconocido y
penaliza directamente Core Web Vitals.

Lo mismo pasa en el listado: `product.tpl` marca `lazy` en **todas** las
miniaturas, incluidas las de la primera fila, que están sobre el pliegue.

**Qué hacer** (capa 3, mejora real y barata):

```smarty
{* ficha de producto — la portada NO se lazy-carga *}
<img class="js-qv-product-cover img-fluid" … fetchpriority="high" width="…" height="…">
```

- En la ficha: quitar `loading="lazy"` de la portada y añadir
  `fetchpriority="high"`. Las miniaturas laterales (línea 86) **sí** se quedan
  con `lazy`: esas no son el LCP.
- En el listado: quitar `lazy` solo de las primeras N miniaturas (las visibles
  sin scroll). Si el tema no permite distinguirlas fácilmente, usar el índice del
  `{foreach}`:

```smarty
{if $smarty.foreach.<nombre>.index < 4}fetchpriority="high"{else}loading="lazy"{/if}
```

Ajustar el `4` al número real de productos por fila del tema.

⚠️ **Medir antes y después con la URL real**, no darlo por bueno. El LCP depende
del diseño concreto: en un tema con un slider o un banner grande sobre la ficha,
el LCP puede ser otro elemento y este cambio no mueve la aguja.

Es, junto con WebP, la mejora de rendimiento con mejor relación coste/beneficio
de toda la capa 3, y toca **dos ficheros**. Hummingbird ya lo hace bien (usa
`fetchpriority="high"` en la primera imagen de ficha y no la lazy-carga), pero
**no hace falta cambiar de tema para arreglarlo**.

Los `width` / `height` explícitos existen para reservar el espacio y evitar
*layout shift* (CLS). Al copiarlos, usar siempre los dinámicos
(`{$…width}` / `{$…height}`) y no los valores fijos que classic tenía
hardcodeados en 8.0 (`141` / `180`): con tamaños de imagen personalizados, los
fijos deforman la maquetación.

## Verificación

1. Con WebP activado en el back office (Diseño → Configuración de imágenes),
   comprobar en el inspector que las imágenes se sirven como `image/webp`.
2. Desactivar WebP y recargar: deben seguir viéndose, ahora en JPG/PNG.
3. **Ficha de producto**: pinchar cada miniatura y confirmar que la imagen grande
   cambia de verdad. Repetir abriendo el modal de zoom.
4. Comprobar que no hay saltos de maquetación al cargar (CLS) en listado y ficha.
5. **LCP**: en el inspector, pestaña Rendimiento, comprobar qué elemento es el
   LCP de la ficha y del listado, y que **no** lleva `loading="lazy"`. Es el
   error que classic trae de fábrica.

## Nota sobre Hummingbird

Hummingbird añade **9 tipos de imagen propios** a los 7 clásicos (`default_xs`
160, `default_sm` 216, `default_md` 261, `default_lg` 336, `default_xl` 400,
`product_main` 720, `product_main_2x` 1440, `category_cover` 1000×200,
`category_cover_2x` 2000×400), y los usa para servir `srcset` + `sizes` reales,
cosa que classic no hace: classic sirve un único tamaño por contexto.

Consecuencia operativa: activar Hummingbird obliga a **regenerar todo el
catálogo**, ×2 si hay WebP y ×3 si hay AVIF. En catálogos grandes es tiempo de
proceso y espacio en disco muy relevantes; planificarlo antes, no descubrirlo el
día del cambio. Ver `hummingbird.md` § 11.4.
