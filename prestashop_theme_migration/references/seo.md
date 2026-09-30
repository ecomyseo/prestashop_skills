# Cambios con impacto SEO, por orden de importancia

Todo verificado sobre el código del tema classic. Los porcentajes de impacto no
se estiman aquí: se indica **qué cambia** y **qué se rompe si no se migra**.

## 1. 🔴 Datos estructurados: microdatos → JSON-LD (**1.7.8**)

### 🔑 Regla de oro: no reemplazar lo que ya funciona, solo añadir lo que falta

**Microdatos y JSON-LD son dos sintaxis del mismo vocabulario (schema.org), y
Google soporta ambas.** Que un tema use microdatos no es un defecto ni algo que
haya que "arreglar": recomendar JSON-LD no es lo mismo que penalizar microdatos.

Por tanto, ante un tema que ya emite datos estructurados:

- **No se sustituyen.** Migrar microdatos válidos a JSON-LD es refactorización,
  no SEO. No mejora el posicionamiento, cuesta muchos ficheros y arriesga romper
  marcado que hoy funciona.
- **Solo se añade lo que no exista ya**, y en JSON-LD, que es más fácil de meter
  sin tocar maquetación.
- **La única regla dura: nunca describir la misma entidad dos veces**, ni en el
  mismo formato ni en formatos distintos. Eso sí genera avisos de duplicado en
  Search Console. Formatos distintos para entidades distintas en la misma página
  es perfectamente válido.

Migrar a JSON-LD por completo solo se justifica si el marcado existente está
**roto o incompleto**, o si el cliente paga la mantenibilidad (3 ficheros en vez
de 30 y pico). En ese caso, completo o nada: dejarlo a medias sí duplica.

### Paso 1: inventariar qué hay

Antes de decidir nada:

```bash
# ¿qué formatos emite el tema?
grep -rl "ld+json" <tema>/templates
grep -rl "itemscope" <tema>/templates <tema>/modules

# ¿qué entidades cubre ya?
grep -rhoE 'itemtype="[^"]+"' <tema>/templates <tema>/modules | sort | uniq -c | sort -rn
grep -rhoE '"@type": *"[A-Za-z]+"' <tema>/templates | sort | uniq -c | sort -rn
```

Ojo con `<tema>/modules/`: los módulos de reseñas (`productcomments` y los
equivalentes de cada tema comercial) suelen aportar el `AggregateRating` por su
cuenta. Cuenta como cubierto, y hay que coordinarlo si algún día se migra.

### Paso 2: comparar con lo que emite classic

Entidades del JSON-LD de classic 9.1.1, para saber qué buscar:

| Fichero | Entidades |
|---|---|
| `head-jsonld.tpl` | `Organization`, `WebSite` + `SearchAction`, `WebPage`, `BreadcrumbList`, `ImageObject` |
| `product-jsonld.tpl` | `Product`, `Offer`, `Brand`, `AggregateRating`, `QuantitativeValue` |
| `product-list-jsonld.tpl` | `ItemList` + `ListItem` |

Incluidos desde:

```smarty
{* catalog/product.tpl *}
{block name='head_microdata_special'}{include file='_partials/microdata/product-jsonld.tpl'}{/block}

{* catalog/listing/product-list.tpl *}
{include file='_partials/microdata/product-list-jsonld.tpl' listing=$listing}
```

Lo que a los temas antiguos les suele faltar de verdad es **`Organization` y
`WebSite` + `SearchAction`** (la caja de búsqueda de sitelinks): son de sitio, no
de producto, así que casi ningún tema comercial los trae. Y son justo lo que
aporta `head-jsonld.tpl`, un solo fichero.

De ahí sale la intervención barata y de mayor rendimiento en la mayoría de casos:
**añadir solo `head-jsonld.tpl`** y, a la vez, retirar el `BreadcrumbList` en
microdatos de `_partials/breadcrumb.tpl` si el tema lo tenía — que es el único
punto donde ambos se solapan. Un fichero nuevo, uno editado, y el marcado de
producto se queda intacto.

### Cuándo sí es un problema real

- **El tema no emite nada.** Sobreescribe `product.tpl` o `product-list.tpl` sin
  trasladar los `{include}`, y tampoco tiene microdatos propios. Es el peor caso
  y el más silencioso: la web se ve perfecta y los rich snippets desaparecen en
  semanas.
- **Marcado duplicado.** Se añadieron los parciales sin quitar el `itemprop`
  inline de la misma entidad.
- **Marcado incompleto.** Hay `Product` pero sin `Offer` con precio y
  disponibilidad: no habrá rich snippet de precio. Eso sí hay que completar,
  aunque se pueda hacer sobre los propios microdatos.

**Verificación:** pasar una ficha y un listado por el test de resultados
enriquecidos de Google **antes y después**, y comparar. El objetivo es que la
lista de entidades detectadas crezca o se mantenga; nunca que encoja. No fiarse
de que "se ve bien".

### ⚠️ En 9.2 el JSON-LD se genera desde el core: nuevo riesgo de duplicado

A partir de 9.2 los datos estructurados dejan de montarse solo en plantillas: el
core los aporta como **arrays** vía el hook `actionFrontControllerSetVariables` +
`getStructuredData()`, y el tema los renderiza con

```smarty
{if !empty($structured_data)}
```

en `templates/_partials/head.tpl`. Eso permite a los módulos añadir, sobreescribir
o quitar `AggregateRating`, `MerchantReturnPolicy` o `ContactPoint` **sin tocar
plantillas** (módulo de ejemplo: `demoseo`).

🔴 **El riesgo es concreto**: un tema que conserve sus `_partials/microdata/*.tpl`
manuales *y* renderice `$structured_data` describirá **la misma entidad dos
veces**. Es justo lo que esta sección lleva advirtiendo desde el principio, pero
ahora la duplicación puede aparecer **sin que nadie toque el tema**, solo por
subir a 9.2 o por instalar un módulo que aporte datos.

Al migrar a 9.2, decidir explícitamente una de las dos vías y documentarla:

- **Vía A (recomendada si el tema ya funciona):** no renderizar `$structured_data`
  y mantener los parciales del tema. No se gana nada, pero no se rompe nada.
- **Vía B:** adoptar `$structured_data` y **quitar** los parciales manuales de las
  entidades que el core ya cubre. Solo si se va a verificar entidad por entidad.

Lo que no vale es tener las dos a la vez sin comprobarlo. Verificar siempre con el
test de resultados enriquecidos, que marca los duplicados.

⚠️ El `head.tpl` de Hummingbird ya trae el bloque `{if !empty($structured_data)}`;
el de classic `develop` **no**. Si el tema es hijo de classic, la vía A es el
comportamiento por defecto y no hay que hacer nada.

## 2. 🔴 Descripción adicional de categoría (**8.0**) — contenido invisible

PrestaShop 8.0 parte la descripción de categoría en dos campos. `description` se
sigue pintando arriba; el nuevo **`additional_description`** va **debajo del
listado de productos**. Es un campo de core nuevo, no un invento del tema:

```php
// classes/Category.php (8.0+) — no existe en 1.7.8.11
public $additional_description;
'additional_description' => ['type' => self::TYPE_HTML, 'lang' => true, ...]
```

El propósito es puramente SEO: permitir texto largo con palabras clave sin
empujar los productos por debajo del pliegue.

En el tema, la parte nueva es `catalog/_partials/category-footer.tpl`:

```smarty
<div id="js-product-list-footer">
    {if isset($category) && $category.additional_description && $listing.pagination.items_shown_from == 1}
        <div class="card">
            <div class="card-block category-additional-description">
                {$category.additional_description nofilter}
            </div>
        </div>
    {/if}
</div>
```

incluido desde `catalog/listing/category.tpl`:

```smarty
{block name='product_list_footer'}
    {include file='catalog/_partials/category-footer.tpl' listing=$listing category=$category}
{/block}
```

`catalog/listing/product-list.tpl` declara el bloque vacío
(`{block name='product_list_footer'}{/block}`) para que otros listados no lo pinten.

### Por qué es prioritario

**El comerciante escribe el texto, lo guarda, lo ve guardado en el back office —
y no aparece nunca en la web.** No hay error, no hay aviso, no hay nada en los
logs. Y ocurre precisamente en las páginas de categoría, que suelen ser las que
posicionan para las búsquedas genéricas del sector.

Es el mismo patrón silencioso que el JSON-LD ausente, pero peor en un aspecto:
aquí hay trabajo humano de redacción tirado a la basura, a veces meses de él, y
el cliente suele descubrirlo por casualidad.

Le pasa a **cualquier tema anterior a 8.0** puesto en un PrestaShop 8+, que es el
escenario más habitual de esta skill.

### Cómo saber si aplica

No aplica si nadie ha rellenado el campo. Se comprueba en un segundo:

```sql
SELECT id_category, id_lang, LEFT(additional_description, 80)
FROM ps_category_lang
WHERE additional_description IS NOT NULL AND additional_description != '';
```

Si devuelve filas, hay contenido que no se está viendo → **capa 2, no capa 3**:
no es una mejora, es contenido roto. Si no devuelve ninguna, es capa 3: se añade
la plantilla para que el campo sea usable a partir de ahora, y merece la pena
avisar al cliente de que existe.

### Detalles de implementación

- **`items_shown_from == 1` es intencionado**: el texto solo sale en la página 1
  del listado, para no duplicarlo en `?page=2`, `?page=3`… Va de la mano del punto
  3 (limpieza del canonical paginado). No quitar esa condición.
- **`isset($category)`** se añade en 8.1 porque el parcial es alcanzable desde
  listados sin categoría (búsqueda). Usar siempre la versión de 8.1, no la de 8.0.
- **`nofilter` es obligatorio**: el campo es HTML.
- El envoltorio `#js-product-list-footer` sobrevive al filtrado facetado: el
  `listing.js` de classic solo reemplaza `#js-product-list-top` y
  `#js-product-list-bottom`, no el footer.

## 3. 🟠 `rel="prev"` / `rel="next"` en paginación (**1.7.8**)

Nuevo `_partials/pagination-seo.tpl`, incluido desde `head.tpl`. Además de emitir
`prev`/`next`, **limpia el `?page=N` del canonical**:

```smarty
{$queryPage = '?page='|cat:$page_nb}
{$page.canonical = $page.canonical|replace:$queryPage:''}
```

Esa limpieza es lo importante. Sin ella, cada página de un listado emite su
propio canonical con `?page=2`, `?page=3`… y se generan tantas URLs canónicas
como páginas: contenido duplicado en categorías grandes.

Google dejó de usar `rel=prev/next` como señal de indexación en 2019, así que los
`<link>` en sí valen poco hoy. **La corrección del canonical sigue valiendo
mucho.** Si el tema sobreescribe `head.tpl`, hay que arrastrar el `{include}`.

## 4. 🟠 `$product.canonical_url` → `$product.url` en miniaturas (**1.7.8**)

```smarty
{* ≤1.7.7 *}  <a href="{$product.canonical_url}">
{* ≥1.7.8 *}  <a href="{$product.url}">
```

`canonical_url` apunta al producto **sin combinación**. Mantenerlo hace que todos
los enlaces internos de listados, bloques de destacados y venta cruzada apunten a
la variante por defecto. Impacto doble: se diluye el enlazado interno hacia las
combinaciones y el usuario aterriza en una variante que no es la que vio.

## 5. 🟠 Canonical y Open Graph se centralizan (**1.7.8**)

Hasta 1.7.7, `catalog/product.tpl` declaraba lo suyo por su cuenta:

```smarty
{block name='head_seo' prepend}<link rel="canonical" href="{$product.canonical_url}">{/block}
{block name='head' append}
  <meta property="og:url" content="{$urls.current_url}">
  <meta property="og:title" content="{$page.meta.title}">
  <meta property="og:site_name" content="{$shop.name}">
  <meta property="og:description" content="{$page.meta.description}">
  <meta property="og:image" content="{$product.cover.large.url}">
```

Desde 1.7.8 el canonical sale de `product.tpl` (queda solo en `head.tpl`, bajo
`{if $page.canonical}`) y los `og:` genéricos pasan al bloque `head_open_graph`
de `head.tpl`. En la ficha solo queda `og:type` y `og:image`, esta protegida:

```smarty
{if $product.cover}<meta property="og:image" content="{$product.cover.large.url}">{/if}
```

Un tema que conserve la versión antigua **emite el canonical y los `og:` dos
veces** en la ficha de producto. Google elige uno de los dos canonical de forma
impredecible, y las previsualizaciones sociales se vuelven inconsistentes.

### El namespace `product:` de Open Graph

Aparte de los `og:` habituales, classic emite marcado de producto de Meta en
`catalog/product.tpl`, y lo ha hecho en **todas** las versiones, de 1.7.0 a 9.1.1:

```html
<meta property="product:pretax_price:amount"   content="{$product.price_tax_exc}">
<meta property="product:pretax_price:currency" content="{$currency.iso_code}">
<meta property="product:price:amount"          content="{$product.price_amount}">
<meta property="product:price:currency"        content="{$currency.iso_code}">
<meta property="product:weight:value"          content="{$product.weight}">
<meta property="product:weight:units"          content="{$product.weight_unit}">
```

No es schema.org ni lo usan los buscadores: alimenta los catálogos de
Facebook/Instagram. Es independiente del JSON-LD y **no entra en conflicto con
él**, así que no hay que elegir: se conservan los dos.

Merece la pena comprobar que sigue ahí si el tema sobreescribe `product.tpl` y el
cliente vende por Meta.

### Formatos que classic no usa nunca

Verificado en todas las versiones de 1.7.0 a 9.1.1: **cero RDFa** (`vocab=`,
`typeof=`), **cero Twitter Cards** y **cero microformatos**. Si aparecen en un
tema, los ha añadido el proveedor y hay que revisarlos aparte — sobre todo por si
duplican entidades que ya van en microdatos o JSON-LD.

## 6. 🟠 Core Web Vitals: `loading="lazy"` + `width`/`height` (**1.7.8**)

Se añaden a la vez en 21 plantillas, y van juntos por diseño:

- `loading="lazy"` reduce peso inicial y mejora el LCP.
- `width` / `height` explícitos reservan el hueco y evitan el *layout shift*
  (CLS). Sin ellos, el lazy loading **empeora** el CLS: la imagen entra tarde y
  desplaza el contenido.

⚠️ Al copiarlos, usar siempre los dinámicos (`{$…width}` / `{$…height}`) y no los
literales que classic tuvo hardcodeados hasta 8.0 (`141` / `180`). Con tamaños de
imagen personalizados, los fijos deforman la maquetación.

🔴 **Pero classic aplica `lazy` donde no debe, y eso hay que corregirlo.** La
primera viñeta de arriba es la teoría; la práctica es que classic marca
`loading="lazy"` **en la portada de la ficha de producto** —el LCP de la
página— y en **todas** las miniaturas de listado, incluidas las de la primera
fila. Verificado en classic 3.1.2, presente desde 1.7.8, y heredado por todo tema
hijo.

Marcar el LCP como `lazy` **retrasa deliberadamente su descarga** y penaliza Core
Web Vitals en lugar de mejorarlos. La corrección son dos ficheros y es la mejora
de rendimiento con mejor relación coste/beneficio de toda la capa 3: quitar el
`lazy` y poner `fetchpriority="high"` en la portada, dejando `lazy` en las
miniaturas laterales. Detalle y snippets en `images.md`, § "Classic lazy-carga su
propio LCP".

**Incluirlo siempre en el informe de auditoría**, aunque el encargo no mencione
rendimiento: es un defecto de origen que el cliente no sabe que tiene y que no
requiere cambiar de tema para arreglar.

## 7. 🟠 WebP y AVIF (**8.1**) — el de mayor alcance

`<picture>` con `<source>` AVIF y WebP en 11 plantillas (14 en 9.0, 15 en 9.1).

**Por qué está en el puesto 7 y no en el 1**, que es contraintuitivo: los cinco
anteriores afectan a cómo Google *interpreta e indexa* la página (datos
estructurados, canonical, enlazado interno); WebP afecta a lo *rápido que carga*.
Como señal de posicionamiento, Core Web Vitals pesa menos que perder los rich
snippets o duplicar canonicals.

**Pero tiene el alcance más amplio de la lista**: afecta a *todas* las páginas
con imágenes, no solo a las fichas de producto, y las imágenes son casi siempre
el grueso del peso de una tienda. En un catálogo con listados de 30 productos, el
ahorro es de megabytes por página. Si el cliente viene con un problema de Core
Web Vitals en rojo, esto es lo primero que hay que mirar, por delante de todo lo
demás de este documento.

Efectos concretos:

- **LCP.** En listados y en la ficha, el LCP es casi siempre una imagen. WebP y
  AVIF reducen el peso de forma sustancial a calidad equivalente (AVIF más que
  WebP). Es la vía más directa para bajar el LCP sin tocar el diseño.
- **Peso total y rastreo.** Menos bytes por página también significa menos
  presupuesto de rastreo consumido, relevante en catálogos grandes.
- **INP / CLS.** No los mejora por sí solo. El CLS depende de los `width`/`height`
  (punto 6), no del formato.

### 🔑 Las URLs de imagen NO cambian

Esto es lo que hace que el patrón de classic sea seguro, y conviene entenderlo
bien porque **lo distingue de la mayoría de módulos de WebP del mercado**:

```smarty
<picture>
  <source srcset="…/12345-home_default.avif" type="image/avif">   ← fichero aparte
  <source srcset="…/12345-home_default.webp" type="image/webp">   ← fichero aparte
  <img src="…/12345-home_default.jpg" …>                          ← la URL de siempre
</picture>
```

PrestaShop **genera los formatos alternativos como ficheros adicionales** y deja
el `src` del `<img>` apuntando al JPG/PNG original. Consecuencias:

- La URL canónica de cada imagen se mantiene. No hay redirecciones, no hay
  reindexación, **no se pierde el posicionamiento acumulado en Google Imágenes**.
- Es reversible: desactivar WebP en el back office vacía los `sources`, los
  `{if !empty()}` no entran y queda el `<img>` de siempre. Sin rastro.

Los módulos que **reescriben** el `src` a `.webp` sí cambian las URLs, y ahí sí
hay que planificar redirecciones y asumir un periodo de reindexación. Si el
cliente ya tiene uno de esos módulos instalado, **quitarlo antes de migrar a 8.1+**:
tener los dos mecanismos a la vez produce `<source>` apuntando a un sitio y `src`
a otro, con resultados impredecibles.

> Sobre qué formato indexa exactamente Google Imágenes cuando hay `<picture>`:
> Googlebot soporta WebP y AVIF, pero no he verificado su comportamiento exacto
> de selección en `<picture>` y no conviene afirmarlo de memoria. Lo que sí es
> seguro por construcción es que el `<img src>` sigue existiendo y sirviendo el
> original, así que el peor caso es el estado previo a la migración.

### ⚠️ Los dos riesgos que convierten esto en una regresión SEO

**1. `srcset` vacío = todas las imágenes en blanco.** Si se copia el patrón sin
los `{if !empty(...)}` y el formato no está generado, el navegador elige igual el
`<source>` vacío y no pinta nada. Pasa en toda la tienda a la vez. De mejora de
Core Web Vitals a caída de conversión en un despliegue.

**2. `alt` obsoleto al cambiar de imagen.** Antes de 8.1, el JS de la ficha solo
cambiaba el `src` al pulsar una miniatura. 8.1 añade también el `alt` y el
`title`:

```js
productCover.attr('alt', newSelectedThumb.attr('alt'));
modalProductCover.attr('title', newSelectedThumb.attr('title'));
```

Sin esto, el `alt` de la imagen grande se queda con el de la primera foto para
siempre. Y si se añade `<picture>` sin el `updateSources` que lo acompaña, la
imagen directamente **no cambia** (los `<source>` ganan al `src`) — ver
`images.md`.

### Hueco conocido: la imagen "sin foto" no lleva `alt`

En todas las versiones hasta 9.1 incluida, el *fallback* de producto sin imagen
se pinta sin atributo `alt`:

```smarty
<img src="{$urls.no_picture_image.bySize.home_default.url}" loading="lazy" width="…" height="…" />
```

Es un fallo de accesibilidad y una oportunidad de mejora barata en un tema
propio: `alt="{$product.name}"`. No lo arregla classic, así que no hay que
esperar a ninguna versión para hacerlo.

## 8. 🟡 `fetchpriority="high"` en la portada de categoría (**9.0**)

```smarty
<img src="{$category.cover.large.url}" … fetchpriority="high"
     width="{$category.cover.large.width}" height="{$category.cover.large.height}">
```

Sin `loading="lazy"` y con prioridad alta, porque es el LCP de la página de
categoría. Poner `lazy` en esa imagen penaliza Core Web Vitals en lugar de
mejorarlos: es un error habitual en temas que aplican `lazy` a todo por sistema.

## 9. 🟡 Marca en JSON-LD: `Thing` → `Brand` (**8.0**)

```smarty
{* ≤1.7.8.11 *}  "@type": "Thing",
{* ≥8.0 *}       "@type": "Brand",
```

En `head-jsonld.tpl`. `Thing` es el tipo genérico y Google no lo interpreta como
marca. Cambio de una línea que arregla el campo `brand` del rich snippet de
producto.

## 10. 🟡 `gtin13` desde el UPC (**8.0**, sintaxis corregida en 9.0 / 2.2.1)

`product-jsonld.tpl` rellena `gtin13` con el EAN13 y, si no hay, con el UPC
prefijado con un cero. Ojo con la sintaxis en la migración a 9:

```smarty
{if $product.ean13}"gtin13": "{$product.ean13}",{elseif $product.upc}"gtin13": "0{$product.upc}",{/if}
```

`{else if}` (con espacio) **rompe la compilación en 9.0+**. Como el fichero
es el que genera los datos estructurados, el fallo se lleva por delante todo el
JSON-LD de la ficha.

## 11. 🟡 `<meta name="keywords">` eliminado (**9.0**)

Sale de `head.tpl`. Google lo ignora desde 2009, así que quitarlo **no tiene
ningún efecto SEO**; se documenta solo para que nadie lo dé por una regresión al
diffear. Sigue existiendo en `layouts/layout-error.tpl`, vacío.

Si un cliente lo reclama, se puede devolver sin riesgo, pero no aporta nada.

---

## Orden recomendado al migrar pensando en SEO

0. **Contenido invisible** — la consulta SQL de `additional_description`. Si
   devuelve filas, esto va **antes que nada**: no es SEO técnico, es contenido
   del cliente que no se está publicando.
1. **Datos estructurados** — inventariar primero qué emite ya el tema y en qué
   formato. Si hay microdatos válidos, **no se sustituyen**: solo se añade en
   JSON-LD lo que falte (normalmente `Organization` y `WebSite`+`SearchAction`),
   cuidando de no describir la misma entidad dos veces.
2. **Canonical** — una sola declaración por página, sin `?page=N`.
3. **Enlaces internos** — `$product.url`, no `canonical_url`.
4. **Open Graph** — sin duplicados en la ficha.
5. **CWV** — `width`/`height` **antes** que `loading="lazy"`, y `<picture>` al
   final, con los `{if !empty()}` bien puestos y el `updateSources` en el JS.

**Excepción:** si el encargo es explícitamente de rendimiento (Core Web Vitals en
rojo, LCP alto, informe de PageSpeed), invertir el orden y empezar por el punto 5.
WebP/AVIF tiene el mayor alcance por página de toda la lista y es lo único que
mueve la aguja del LCP. Los puntos 1–4 se pueden hacer después sin perder nada.

## Checklist de verificación SEO

- [ ] **Descripción adicional de categoría**: lanzar la consulta SQL; si hay
      contenido, verificar que se ve al pie del listado en la página 1 de esa
      categoría, y que **no** se repite en la página 2.
- [ ] Ficha de producto y listado en el test de resultados enriquecidos de Google:
      sin errores y con los mismos tipos que antes de migrar.
- [ ] Ver código fuente: **un solo** `rel="canonical"` por página.
- [ ] Ver código fuente: **un solo** juego de `og:`.
- [ ] Página 2 de una categoría: el canonical **no** lleva `?page=2`.
- [ ] Ninguna **entidad** descrita dos veces (p. ej. `BreadcrumbList` en
      microdatos y en JSON-LD a la vez). Que convivan microdatos y JSON-LD para
      entidades **distintas** es correcto y no hay que tocarlo.
- [ ] La lista de entidades detectadas por el test de Google no ha encogido
      respecto a antes de la intervención.
- [ ] Los enlaces de las miniaturas conservan la combinación.
- [ ] Todas las imágenes tienen `alt` (classic cae al nombre del producto cuando
      no hay `legend`) y `width`/`height`.
- [ ] Con WebP activo, ninguna imagen en blanco. Y con WebP desactivado tampoco.
- [ ] El `src` del `<img>` sigue apuntando al JPG/PNG original: las URLs de
      imagen **no** deben haber cambiado. Si han cambiado, hay un módulo de WebP
      reescribiendo el `src` y hace falta plan de redirecciones.
- [ ] En la ficha, cambiar de miniatura actualiza imagen, `alt` y `title`.
- [ ] Lighthouse antes y después: CLS y LCP no deben empeorar. Si el objetivo era
      rendimiento, medir el peso total de una página de listado antes y después
      para cuantificar lo ganado con WebP/AVIF.
