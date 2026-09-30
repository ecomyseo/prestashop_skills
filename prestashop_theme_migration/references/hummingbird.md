# Hummingbird: qué es, cuándo aplica y qué cuesta de verdad

Fecha de corte de todo este fichero: **24 de julio de 2026**. Es un tema en
evolución activa; antes de citar una versión, revalidar contra el repo.

Repositorio: <https://github.com/PrestaShop/hummingbird> (licencia AFL-3.0).

⚠️ No confundir con `hummingbird-project/hummingbird`, que es un framework HTTP
en Swift y no tiene ninguna relación con PrestaShop.

---

## 1. La premisa "en PS 9 hay que usar Hummingbird" es falsa como obligación

Es el malentendido más extendido y condiciona presupuestos enteros. Desglosado:

| Afirmación | Veredicto |
|---|---|
| "En PS 9 hay que usar Hummingbird en vez de classic" | **falso como obligación** |
| "En PS 9.0 el tema por defecto es Hummingbird" | **falso** — es classic |
| "En PS 9.1+ el tema por defecto es Hummingbird" | **cierto, solo en instalaciones NUEVAS** |
| "Hay que instalar Hummingbird aparte" | **falso** — va en el paquete desde 9.0 |
| "Classic está deprecado" | **cierto pero acotado** — *deprecated for new development* desde 9.1 |
| "Classic dejará de funcionar en PS 9" | **falso / sin evidencia** |
| "Existe guía oficial de migración classic → Hummingbird" | **falso** |
| "Migrar classic → Hummingbird es una migración" | **falso — es una reescritura** |

Citas oficiales, verbatim, de <https://devdocs.prestashop-project.org/9/themes/>:

> "Starting from PrestaShop 9.1, Hummingbird is the default theme."

> "The Classic theme is deprecated for new development starting from PrestaShop
> 9.1. It remains available for backward compatibility but will not receive new
> features."

Y de <https://devdocs.prestashop-project.org/9/modules/core-updates/9.1/>:

> "Existing installations upgrading from 9.0.x keep their current theme.
> Hummingbird is only set as default for fresh installations."

De la nota de lanzamiento de 9.0
(<https://build.prestashop-project.org/news/2025/prestashop-9-0-available/>):

> "While Hummingbird is not the default theme in PrestaShop 9, it sets the
> direction for the future of PrestaShop theming"

**No existe ningún anuncio de fecha de eliminación de classic.** Classic sigue
publicando releases (3.1.2, 11-may-2026), su repo no está archivado, y su
`theme.yml` declara `to: ~` (sin tope superior).

`https://devdocs.prestashop-project.org/9/themes/hummingbird/migration/` devuelve
**HTTP 404** (comprobado). Las páginas que sí existen —`from-scratch`,
`from-hummingbird`, `child-theme`— tratan de **crear un tema nuevo**, no de
convertir uno existente.

### Formulación correcta

> Classic sigue siendo la referencia correcta para **migrar un tema existente**.
> Hummingbird es la referencia correcta para **empezar un tema nuevo**.

Toda tienda que llegue a 9.x actualizando desde 8.x o 9.0.x conserva classic. Ese
es el caso mayoritario y es el que cubre el resto de este skill.

---

## 2. Emparejamiento Hummingbird ↔ PrestaShop (bloqueante)

Valores de `config/theme.yml` (`meta.compatibility`) de cada tag.

| Hummingbird | Fecha | `from` | `to` | `framework` | PrestaShop |
|---|---|---|---|---|---|
| v1.0.0 | 2025-01-29 | 8.1.0 | `~` | bootstrap-v5.2.0 | 8.1.x+ |
| v1.0.1 | 2025-06-16 | 8.1.0 | `~` | bootstrap-v5.2.0 | 8.1.x+ |
| v2.0.0 | 2026-02-11 | 9.1.0 | `~9.1.0` | bootstrap-v5.3.3 | **solo 9.1.x** |
| v2.1.0 | 2026-07-14 | 9.2.0 | `~9.2.0` | bootstrap-v5.3.3 | **solo 9.2.x** |

Reglas que no se negocian:

- **PS 9.1.x → Hummingbird 2.0.0.** v2.1.0 declara `from: 9.2.0` y por tanto **no
  se instala en 9.1.x**, aunque sus notas de release digan "default theme in
  PrestaShop 9.1+". Es una contradicción real del propio proyecto; manda el
  `theme.yml`.
- **PS 9.2 → Hummingbird 2.1.0.**
- **PS 8.1.x → línea 1.x.** Hummingbird no viene empaquetado en PS 8: se descarga
  aparte.
- **Hummingbird 2.x pone tope superior** (`to: ~9.1.0` / `~9.2.0`); **classic
  no** (`to: ~`). Un tema hijo que copie el `meta.compatibility` del padre
  **quedará bloqueado al subir de minor**. Classic no tiene ese problema.

⚠️ El campo `version:` de `theme.yml` **no es fiable** en ninguno de los dos
temas: classic 3.0.3 y 3.0.4 declaran ambos `3.0.2`; Hummingbird v1.0.0 declara
`0.2.0`. Datar siempre por contenido o por la versión de PrestaShop.

⚠️ El README de la rama `develop` dice que apunta a `~10.0.0`, mientras el
`theme.yml` de esa misma rama sigue diciendo `from: 9.2.0`. El README va por
delante del fichero.

---

## 3. Cómo reconocer un tema Hummingbird (o un hijo suyo)

**Definitivo, en `config/theme.yml`:**
- `name: hummingbird` y `framework: bootstrap-v5.3.3`
- `parent: hummingbird` → es un **tema hijo** de Hummingbird
- `global_settings.modules.to_disable` con `blockwishlist`, `ps_brandlist`,
  `ps_supplierlist` (classic no desactiva ninguno)
- `modules_to_unhook` con `displayFooter → ps_socialfollow` y
  `displaySearch → ps_searchbar`
- `image_types` con `default_xs` / `default_sm` / `default_md` / `default_lg` /
  `default_xl` / `product_main` / `product_main_2x` / `category_cover` /
  `category_cover_2x` (los 9 tipos que classic no tiene)

**En la raíz del tema:**
- `src/scss/`, `src/js/`, `webpack.config.js`, `tsconfig.json`
- `package.json` con `"bootstrap": "5.3.3"` (pineado, sin caret)

**En el marcado:**

| Marcador | Tema |
|---|---|
| `data-ps-component`, `data-ps-action`, `data-ps-target`, `data-ps-data`, `data-ps-ref`, `data-ps-label` | **Hummingbird** |
| `data-bs-toggle` / `data-bs-target` / `data-bs-dismiss` | Bootstrap 5 → Hummingbird |
| `data-toggle` / `data-target` (sin `bs`) | Bootstrap 4-alpha → classic |
| `visually-hidden`, `form-select`, `form-check`, `btn-close`, `ms-*`/`me-*`, `float-start`/`float-end`, `text-start`/`text-end`, `fw-bold`, `g-0`, `ratio-16x9` | BS5 → Hummingbird |
| `col-xs-*`, `hidden-*-up/down`, `card-block`, `float-xs-*`, `text-xs-*`, `form-group`, `form-control-label`, `input-group-addon`, `tag-*`, `sr-only`, `close` | BS 4.0.0-alpha.5 → classic |

**En la estructura de plantillas:**
- Existe `templates/components/` → Hummingbird. Classic no tiene esa carpeta.
- `templates/catalog/brands.tpl` → Hummingbird (en classic: `manufacturers.tpl`)
- `templates/_partials/preload.tpl` → Hummingbird (classic no lo tiene)
- `catalog/_partials/miniatures/product-pack.tpl` → Hummingbird
  (en classic: `miniatures/pack-product.tpl`)

**Señal de despliegue:** si el tema viene de `git clone` y **no existe
`assets/css/theme.css`**, es normal: `assets/` está en `.gitignore`. Hay que
ejecutar `npm ci && npm run build` con Node ≥ 20. Los ZIP de release sí traen
`assets/`.

---

## 4. El salto de Bootstrap no es uno, son dos

Classic usa `4.0.0-alpha.5` (octubre 2016), que **no es Bootstrap 4**. La
nomenclatura cambió en alpha.6 (enero 2017) y otra vez en beta.1 (agosto 2017):

```
4.0.0-alpha.5  →  4.0.0 estable  →  5.3.3
    (salto A)          (salto B)
```

**La capa de compatibilidad oficial de PrestaShop solo cubre el salto B.** El
salto A no lo cubre nadie.

Verificado por grep sobre el `dist` real de `bootstrap@4.0.0-alpha.5`: en alpha.5
**no existen** `col-4`, `card-body`, `d-none`, `float-right`, `text-right`,
`badge`, `no-gutters`, `thumbnail`, `page-header`, ni **ninguna utilidad flex**
(`d-flex`, `justify-content-*`, `align-items-*`). Grep sobre las plantillas de
classic: **0 usos** de utilidades flex. El espaciado ya usa sintaxis corta
(`mt-3`), así que ahí solo falta el cambio de dirección.

### 4.1 Inventario real de clases legacy en classic

Ocurrencias en `.tpl` + `.js` del tema classic:

| Clase | Usos | | Clase | Usos |
|---|--:|---|---|--:|
| `hidden-*` (todas) | 68 | | `card-block` | 26 |
| `col-xs-12` | 53 | | `form-group` | 20 |
| `hidden-sm-down` | 47 | | `col-xs-8` | 19 |
| `col-xs-4` | 41 | | `modal-dialog` | 15 |
| `text-xs-right` | 40 | | `form-control-label` | 14 |
| `data-toggle` | 38 | | `close` | 14 |
| `hidden-md-up` | 35 | | `card-title` | 14 |
| `float-xs-right` | 34 | | `float-xs-left` | 13 |
| `col-sm-6` | 29 | | `data-dismiss` | 13 |
| `data-target` | 26 | | espaciados `ml-`/`pr-` | 13 |

Cola larga: `img-fluid` 9 · `nav-link`/`nav-item` 8/7 · `text-xs-left` 7 ·
`custom-checkbox` 5 · `float-md-right` 4 · `hidden-xs-down` 4 · `hidden-md-down` 4
· `page-header` 4 · `label-pill` 4 · `input-group-btn` 4 · `hidden-sm-up` 3 ·
`data-slide` 3 · `hidden-xs-up` 2 · `data-ride` 1 · `custom-file`, `sr-only`,
`media-left`, `media-body`, `carousel-item`, `carousel-inner`, `input-lg`,
`input-sm` 1 cada una.

Nota: `label-pill`, `page-header`, `input-lg` e `input-sm` **ni siquiera existen
en alpha.5** — son restos muertos de Bootstrap 3 que classic arrastra.

### 4.2 Tablas de renombrados

Columna **BCL** = lo cubre la capa de compatibilidad oficial (§5).
**`—` = no lo cubre, hay que reescribir el marcado a mano.**

#### Grid y layout

| Classic / alpha.5 | BS4 estable | BS 5.3.3 | BCL |
|---|---|---|:-:|
| `col-xs-*` | `col-*` | `col-*` | **—** |
| `offset-xs-*` | `offset-*` | `offset-*` | **—** |
| `push-xs-*` / `pull-xs-*` | `order-*` | `order-*` | **—** |
| `col-{sm,md,lg,xl}-*` | igual | igual (+ nuevo `xxl` a 1400px) | n/a |
| (no existe) | `.no-gutters` | `.g-0` (+ `gx-*`, `gy-*`) | ✅ |
| gutter fijo 15px | 15px | `--bs-gutter-x` (1.5rem) | parcial |
| `.order-6` … `.order-12` | existen | **eliminadas** (solo 1–5) | ✅ |

#### Visibilidad responsive

| Classic / alpha.5 | BS 5.3.3 | BCL |
|---|---|:-:|
| `hidden-xs-down` | `d-none d-sm-block` | **—** |
| `hidden-sm-down` | `d-none d-md-block` | **—** |
| `hidden-md-down` | `d-none d-lg-block` | **—** |
| `hidden-lg-down` | `d-none d-xl-block` | **—** |
| `hidden-xs-up` | `d-none` | **—** |
| `hidden-sm-up` | `d-sm-none` | **—** |
| `hidden-md-up` | `d-md-none` | **—** |
| `hidden-lg-up` / `hidden-xl-up` | `d-lg-none` / `d-xl-none` | **—** |
| `hidden-print` | `d-print-none` | **—** |
| `sr-only` / `sr-only-focusable` | `visually-hidden` / `visually-hidden-focusable` | ✅ |

🔴 **Las `hidden-*-up/down` se eliminaron por completo en BS4 beta.1 y no tienen
equivalente 1:1.** `d-none d-md-block` **fuerza `display:block`**, mientras
`hidden-sm-down` solo ocultaba sin tocar el display original. Si el elemento era
`inline`, `flex` o `table-cell`, hay que usar `d-md-inline` / `d-md-flex` / etc.
**Es la fuente nº1 de regresiones visuales silenciosas de esta migración**, y son
68 ocurrencias en classic.

#### Flotado, texto, espaciado, bordes

| Classic / alpha.5 | BS 5.3.3 | BCL |
|---|---|:-:|
| `float-xs-left` / `float-xs-right` | `float-start` / `float-end` | **—** |
| `float-{sm,md,lg,xl}-left/right` | `float-{bp}-start/end` | ✅ |
| `text-xs-left` / `text-xs-right` | `text-start` / `text-end` | **—** |
| `text-{bp}-left/right` | `text-{bp}-start/end` | ✅ |
| `text-justify` | **eliminada** | ✅ |
| `text-hide` | **eliminada** | ✅ |
| `text-monospace` | `font-monospace` | ✅ |
| `font-weight-bold/light/normal` | `fw-bold` / `fw-light` / `fw-normal` | ✅ |
| `font-italic` | `fst-italic` | ✅ |
| `ml-*` / `mr-*` | `ms-*` / `me-*` | ✅ |
| `pl-*` / `pr-*` | `ps-*` / `pe-*` | ✅ |
| `ml-auto` / `mr-auto` | `ms-auto` / `me-auto` | ✅ |
| `border-left` / `border-right` | `border-start` / `border-end` | ✅ |
| `rounded-left` / `rounded-right` | `rounded-start` / `rounded-end` | ✅ |
| `rounded-sm` / `rounded-lg` | **eliminadas** → `rounded-1` … `rounded-3` | ✅ |

#### Formularios — el bloque más destructivo

| Classic / alpha.5 | BS 5.3.3 | BCL |
|---|---|:-:|
| `form-group` | **eliminada** → `mb-3` u otra utilidad de margen | ✅ |
| `form-control-label` | `col-form-label` | **—** |
| (no existe) | **`form-label` obligatoria** en `<label>` | parcial |
| `form-row` | **eliminada** → `row` + `g-*` | ✅ |
| `form-inline` | **eliminada** → utilidades flex | ✅ |
| `custom-select` / `-sm` / `-lg` | `form-select` / `-sm` / `-lg` | ✅ |
| `custom-control` + `custom-checkbox` / `custom-radio` | `form-check` | ✅ |
| `custom-switch` | `form-switch` | ✅ |
| `custom-control-input` | `form-check-input` | ✅ |
| `custom-control-label` | `form-check-label` | ✅ |
| `custom-control-inline` | `form-check-inline` | ✅ |
| `custom-range` | `form-range` | ✅ |
| `custom-file` / `-input` / `-label` | **eliminadas** → `form-control` en `<input type="file">` | ✅ |
| `form-control-file` / `form-control-range` | **eliminadas** | ✅ |
| `input-group-addon` | `input-group-text` **como hijo directo** de `.input-group` | **—** |
| `input-group-btn` | botón **como hijo directo** | **—** |
| `input-group-prepend` / `-append` | **eliminadas** | ✅ |
| `form-control-static` | `form-control-plaintext` | parcial |
| `input-lg` / `input-sm` | `form-control-lg` / `-sm` | **—** |

#### Componentes

| Classic / alpha.5 | BS 5.3.3 | BCL |
|---|---|:-:|
| `card-block` | `card-body` | **—** |
| `card-deck` | **eliminada** → `row row-cols-*` | ✅ |
| `card-columns` | **eliminada** → Masonry | ✅ |
| card acordeón (`data-parent`) | **eliminado** → componente `accordion` | ✅ |
| `tag` / `tag-default` / `tag-primary` | `badge` + `bg-*` | **—** |
| `tag-pill` / `label-pill` | `rounded-pill` | parcial |
| `close` | `btn-close` (+ `btn-close-white`) | ✅ |
| `btn-block` | **eliminada** → `d-grid` + `gap-*`, o `w-100` | ✅ |
| `btn-group-toggle` + `data-toggle="buttons"` | **eliminado** → `btn-check` | — |
| `jumbotron` / `jumbotron-fluid` | **eliminadas** | ✅ |
| `media` / `media-body` / `media-left` | **eliminadas** → utilidades flex | parcial |
| `embed-responsive` | `ratio` | ✅ |
| `embed-responsive-item` | **eliminada** → `.ratio > *` | ✅ |
| `embed-responsive-16by9` | `ratio-16x9` | ✅ |
| `thead-light` / `thead-dark` | **eliminadas** → `table-light` / `table-dark` | ✅ |
| `pre-scrollable` | **eliminada** | ✅ |
| `dropdown-menu-right` / `-left` | `dropdown-menu-end` / `-start` | parcial |
| `dropleft` / `dropright` | `dropstart` / `dropend` | — |
| `arrow` (tooltip/popover) | `tooltip-arrow` / `popover-arrow` | — |
| `navbar` | **requiere un `.container*` dentro** (breaking) | — |

#### Atributos `data-*`

Todos pasan a llevar el prefijo `bs`. Mapa exacto de los 15 que reescribe la capa
de compatibilidad:

```
data-autohide   → data-bs-autohide      data-ride       → data-bs-ride
data-content    → data-bs-content       data-slide      → data-bs-slide
data-dismiss    → data-bs-dismiss       data-slide-to   → data-bs-slide-to
data-html       → data-bs-html          data-spy        → data-bs-spy
data-offset     → data-bs-offset        data-target     → data-bs-target
data-parent     → data-bs-parent        data-toggle     → data-bs-toggle
data-placement  → data-bs-placement     data-trigger    → data-bs-trigger
data-reference  → data-bs-reference
```

#### Sass / SCSS — afecta al `_dev/css` del tema hijo

| Classic / alpha.5 | BS 5.3.3 |
|---|---|
| `color-yiq()` | `color-contrast()` |
| `$yiq-contrasted-threshold` | `$min-contrast-ratio` |
| `$yiq-text-dark` / `$yiq-text-light` | `$color-contrast-dark` / `$color-contrast-light` |
| `theme-color()`, `color()`, `gray()` | **eliminadas** → usar variables |
| `theme-color-level()` | **eliminada** → `tint-color()` / `shade-color()` |
| `scale-color()` | `shift-color()` |
| mixins `hover()`, `hover-focus()`, `float()`, `text-hide()`, `visibility()`, `nav-divider()`, `retina-img()`, `form-control-focus()` | **eliminados** |
| `$embed-responsive-aspect-ratios` | `$aspect-ratios` |
| `media-breakpoint-down(lg)` = "< md" | `media-breakpoint-down(lg)` = **"< lg"** |
| 4 breakpoints | 5 breakpoints (+ `xxl`) |
| Bourbon 7 + Bootstrap alpha | Dart Sass, sin Bourbon |

🔴 El cambio de `media-breakpoint-down()` es especialmente traicionero:
**compila sin error y produce media queries desplazadas un breakpoint entero.**

---

## 5. La Bootstrap Compatibility Layer: qué resuelve y qué no

PrestaShop mantiene **`PrestaShop/bootstrap-compatibility-layer`**
(npm `bootstrap-compatibility-layer`, licencia MIT).

Doc (ambas URLs responden 200):
- <https://devdocs.prestashop-project.org/9/themes/reference/bootstrap-compatibility/>
- <https://devdocs.prestashop-project.org/9/themes/hummingbird/bootstrap-compatibility/>

Descripción literal de su `package.json`: *"Bootstrap compatibility layer to help
using a module based on **bootstrap 4** with a theme based on bootstrap 5"*.

Dos piezas:

- **CSS:** redefine clases BS4 sobre BS5. Cubre `components/{badges,buttons,cards,close,jumbotron}`,
  `content/{code,table}`, `form/{custom-forms,form,input-group}`,
  `helpers/{ratio,screen-readers}`, `layout/grid`, `utilities/{border,float,spacing,text}`.
- **JS:** una clase `BSCompatibilityLayer` que reescribe los 15 atributos `data-*`
  → `data-bs-*` **y monta un `MutationObserver`** (`{attributes, childList, subtree}`)
  para cubrir también el marcado inyectado por AJAX. Es la parte más valiosa:
  resuelve el problema de los data-attributes incluido el contenido dinámico
  (carrito, facetas, quickview).

🔴 **Limitación crítica: parte de Bootstrap 4 estable, no de alpha.5.** Grep sobre
su `src/styles/` da **cero coincidencias** de `col-xs`, `hidden-*`, `card-block`,
`float-xs`, `text-xs`, `push-xs`, `offset-xs`, `input-group-addon`,
`input-group-btn`, `form-control-label`, `tag-*`, `card-title`. Su
`layout/_grid.scss` son **25 líneas** (`.no-gutters`, `.order-6…12`,
`.media`/`.media-body`) y **no incluye columnas**.

→ **Las cinco familias más frecuentes de classic no las arregla.**

Otras dos cosas que hay que saber:

- **Hummingbird no la incluye.** Grep sobre todo el repo: 0 resultados. Es opt-in.
- El snippet oficial la carga desde **unpkg.com**. En producción, instalarla vía
  npm y servirla desde el propio dominio (CSP, RGPD, dependencia de un tercero).
- La doc la califica de parche temporal, no de solución permanente.

---

## 6. Arquitectura JS

### 6.1 jQuery: la afirmación "Hummingbird no usa jQuery" es doblemente inexacta

Hay que separar tres planos:

1. **Código del tema.** El `CONTEXT.md` del repo es tajante: *"Vanilla JavaScript
   & TypeScript (NO jQuery. jQuery is strictly forbidden)"*. Esa es la norma para
   escribir código nuevo y hay que respetarla.
2. **Dependencias declaradas.** El `package.json` del tag **v2.1.0 sigue
   declarando en `dependencies`**: `"jquery": "^3.7.1"`,
   `"jquery-touchswipe": "^1.6"`, `"jquery.browser": "^0.1.0"` (más
   `@types/jquery` en `devDependencies`). Las notas de v2.1.0 hablan de
   *"continued jQuery removal"* — es decir, la eliminación **está en curso, no
   terminada**.
   ⚠️ No verificado: si el `theme.js` compilado llega a empaquetar jQuery o si
   las dependencias son residuales. Comprobar con `npm run build:analyze`.
3. **La página servida.** jQuery 3.7.1 + jquery-migrate 3.4.0 **se siguen
   cargando** en el front, los use el tema o no, porque los carga `themes/core.js`:

```php
// classes/controller/FrontController.php::setMedia()
if ($this->context->shop->theme->requiresCoreScripts()) {
    $this->registerJavascript('corejs', '/themes/core.js', ['position' => 'bottom', 'priority' => 0]);
}
```
```php
// src/Core/Addon/Theme/Theme.php
public function requiresCoreScripts(): bool
{
    return $this->attributes->get('theme_settings.core_scripts', true);
}
```

El `config/theme.yml` de Hummingbird 2.x **no declara `theme_settings.core_scripts`**
(verificado), luego se aplica el default `true`. Resultado: ~140 KB (~43 KB
comprimido) de jQuery + migrate en toda página, con Hummingbird igual que con
classic.

Doc oficial: *"jQuery is loaded by `core.js` for backward compatibility with
existing modules. **Theme code must not use jQuery**."*

🔴 **No poner `core_scripts: false` "para quitar jQuery".** Aviso oficial:
*"Disabling `core.js` removes the `prestashop` event system and jQuery. Most
modules depend on these."* Se cargan casi todos los módulos por delante.

### 6.2 El bus de eventos sobrevive intacto

Es el único punto de la migración que se reutiliza al 100 %.

- El objeto global `prestashop` lo crea el **core** (`FrontController::buildFrontEndObject`),
  no el tema.
- El `EventEmitter` lo añade el tema con el paquete npm `events`, en ambos casos:
  Hummingbird en `src/js/prestashop.ts`
  (`Object.assign(window.prestashop, EventEmitter.prototype)`), classic con un
  bucle equivalente en `_dev/js/theme.js`.
- Los nombres de evento son **los mismos**: `updateCart`, `updatedCart`,
  `updateProduct`, `updatedProduct`, `updateProductList`, `updateFacets`,
  `updatedDeliveryForm`, `handleError`, `quickviewOpened`, `clickQuickview`,
  `responsiveUpdate`, `combinationFocusRestored`.

⚠️ Trampa documentada: *"`prestashop.on()` and `prestashop.emit()` are not
available until the theme's `theme.js` has executed."* → registrar con
`priority` > 50 y/o dentro de `DOMContentLoaded`.

Hummingbird publica además `window.Theme.selectors` y `window.Theme.events`
(webpack `library: {name: 'Theme', type: 'window'}`).

### 6.3 Los selectores `js-*` no se conservan

Classic declara 105 selectores `js-*`, Hummingbird 118, y **solo 70 coinciden**.
Los 35 que desaparecen:

```
js-arrow-down  js-arrow-up  js-arrows  js-cart-detailed-subtotals  js-checkout-modal
js-child-focus  js-content-wrapper  js-count  js-customization-modal  js-dropdown
js-edit-delivery  js-file-name  js-footer  js-hint-password  js-input-column
js-modal-arrow-down  js-modal-arrow-up  js-modal-arrows  js-modal-content  js-modal-mask
js-modal-product-cover  js-modal-product-images  js-modal-thumb  js-product
js-product-miniature  js-product-nav-active  js-product-tab-active  js-quick-view
js-qv-mask  js-qv-product-images  js-show-details  js-thumbnails  js-top-menu
js-top-menu-bottom  js-visible-password
```

Los que más rompen módulos de terceros: `js-product-miniature`, `js-quick-view`,
`js-product`, `js-thumbnails`, `js-customization-modal`.

Hummingbird engancha el JS por atributos **`data-ps-*`** (`data-ps-component`,
`data-ps-action`, `data-ps-target`, `data-ps-data`, `data-ps-ref`,
`data-ps-label`), **nunca por clases CSS**.

⚠️ No verificado: si los 70 selectores `js-*` comunes siguen realmente enganchados
a JS en Hummingbird o son residuos de marcado — su `CONTEXT.md` prohíbe bindear
JS a clases CSS.

---

## 7. Build

- **Webpack 5** (`^5.95.0`). `webpack.config.js` + carpeta `webpack/`
  (`common`, `production`, `development`, `parts`, `vars`, `rtl`) con
  `webpack-merge`. Entradas: `theme`, `error`, `theme_rtl`, `error_rtl`, `rtl`.
  Salida a `../assets`, `library: {name: 'Theme', type: 'window'}`.
  ⚠️ `CONTEXT.md` menciona Vite como intención; **no existe `vite.config.*`**.
- **Node.js ≥ 20** (`engines`, `.nvmrc`).
- TypeScript `~5.5.4` (`any` prohibido), Sass `^1.61.0`, PostCSS + autoprefixer,
  Jest, Storybook 8.6.14 (<https://build.prestashop.com/hummingbird/>), ESLint,
  Stylelint (`stylelint-config-prestashop`), Prettier.
- Scripts npm: `build`, `build:analyze`, `watch`, `dev`, `stylelint`,
  `stylelint:fix`, `prettier`, `prettier:fix`, `lint`, `lint:fix`, `test`,
  `coverage`, `storybook`, `storybook:build`.
- Docker en `docker/` (front `:8887`, BO `/admin-dev`, phpMyAdmin `:8889`).
- 🔴 **`assets/` está en `.gitignore`** (igual que en classic-theme). Un
  `git clone` sin `npm ci && npm run build` da un tema sin estilos ni JS. Los ZIP
  de release sí los incluyen.
- SCSS con **BEM** y CSS **`@layer`** en este orden: `vendors`, `bs-base`,
  `bs-components`, `bs-custom-components`, `ps-base`, `ps-components`,
  `ps-pages`, `ps-modules`, `utilities`. **`@extend` prohibido.**
- El `composer.json` del tema solo tiene `require-dev`
  (`prestashop/header-stamp`, `prestashop/autoindex`): **no declara restricción de
  versión de PrestaShop**. La compatibilidad vive solo en `config/theme.yml`.

**Qué se puede tocar sin compilar:** solo los `.tpl` de `templates/` y `modules/`,
más `assets/css/custom.css` y `assets/js/custom.js`.

---

## 8. Temas hijo

El mecanismo es **del core**, no del tema: funciona con Hummingbird igual que con
classic (`Theme.php` fusiona el `theme.yml` del padre con el del hijo;
`config.inc.php` define `_PARENT_THEME_NAME_`; `defines_uri.inc.php` define
`_PS_PARENT_THEME_DIR_` y `_PS_PARENT_THEME_URI_`).

Estructura mínima: **2 ficheros** (`config/theme.yml` + `preview.png`).

```yaml
parent: hummingbird
name: mi-hijo            # DEBE coincidir con el nombre de la carpeta
display_name: Mi Hijo
version: 1.0.0
assets:
  use_parent_assets: true
```

`use_parent_assets` redirige `$urls.css_url`, `$urls.js_url`, `$urls.img_url` y
`$urls.theme_assets` al padre, y añade `child_css_url`, `child_js_url`,
`child_img_url`, `child_theme_assets`. La cadena de resolución de assets es
`[hijo, padre, raíz]`, así que `theme.css` y `theme.js` se resuelven solos.

✅ **Ventaja clave: un tema hijo de Hummingbird no necesita Node, ni npm, ni
webpack.** Hereda los `assets/` ya compilados del padre. Si el encargo es
"cambiar colores y algún bloque", esta es la vía y evita toda la toolchain.

Para heredar bloques del mismo fichero del padre, prefijo `parent:`:

```smarty
{extends file='parent:catalog/listing/category.tpl'}
{block name='product_list_header'} … {/block}
```

Sin `parent:` habría bucle infinito.

⚠️ **No copiar el `meta.compatibility` del padre.** Hummingbird 2.x declara
`to: ~9.1.0` / `~9.2.0`; copiarlo bloquea el tema hijo al subir de minor.

**No existe CLI de scaffolding.** Los únicos comandos de tema del core son
`prestashop:theme:enable` y `prestashop:theme:export`. El "scaffolding" oficial es
manual: `git clone … mytheme && rm -rf .git && git init`, editar
`config/theme.yml`, `npm ci && npm run build`, `php bin/console prestashop:theme:enable mytheme`.

---

## 9. Compatibilidad de módulos

El mecanismo de override es **idéntico** al de 1.7/8 (orden: hijo → padre →
módulo). Lo que cambia es el marcado que hay dentro.

| | classic | Hummingbird |
|---|--:|--:|
| Módulos nativos sobrescritos | 31 | **38** |
| `.tpl` de módulo | 42 | **73** |

Hummingbird cubre 7 módulos más y añade 31 plantillas: todo `blockwishlist`,
todo `productcomments`, todo `psgdpr`, `ps_cashondelivery`, `ps_checkpayment`,
`ps_wirepayment`, `ps_emailalerts`, `ps_facetedsearch`, `ps_searchbar`,
`ps_customtext` y tres variantes de `blockreassurance`. Más 17 SCSS específicos
por módulo en `src/scss/prestashop/modules/`.

🔴 **Solo módulos nativos. Los de terceros van a cargo del integrador.**

Evidencia de que el problema es real: grep sobre las plantillas por defecto del
módulo nativo `ps_facetedsearch` (repo oficial, rama `dev`) → `form-group` 27,
`data-toggle` 17, `hidden-sm-down` 3, `col-xs-12` 1, `float-xs-right` 1,
`close` 1. Con Hummingbird y sin capa de compatibilidad, ese módulo pierde grid,
pierde ocultado responsive, pierde espaciado de formulario, y **sus dropdowns y
collapses dejan de funcionar**. Lo mismo le pasa a cualquier módulo escrito
contra classic.

Reconocimiento oficial, en el epic abierto
[PrestaShop#36145](https://github.com/PrestaShop/PrestaShop/issues/36145):
*"the theme is based on bootstrap5 and won't be compatible with all modules until
they handle the theme"*.

**Ni siquiera Hummingbird está limpio:** quedan restos alpha.5 en sus propios
overrides — `col-xs-4` y `col-xs-8` en `modules/ps_shoppingcart/modal.tpl`
(grid roto en el modal del carrito del tema oficial) y `form-group` en tres
modales de `blockwishlist`.

### 9.1 El `theme.yml` cambia la distribución de módulos, no solo el marcado

Verificado en `config/theme.yml` de v2.1.0:

```yaml
global_settings:
  modules:
    to_disable:
      - blockwishlist
      - ps_brandlist
      - ps_supplierlist
  hooks:
    modules_to_unhook:
      displayFooter:
        - ps_socialfollow
      displaySearch:
        - ps_searchbar
```

Y reengancha más de 20 hooks (`displayAfterBodyOpeningTag`, `displayNav1`,
`displayNav2`, `displayNavFullWidth`, `displayTop`, `displayHome`,
`displayFooterBefore`, `displayFooter`, `displayFooterAfter`, `displayLeftColumn`,
`displayContact*`, `displayProductAdditionalInfo`, `displayProductListReviews`,
`displayFooterProduct`, `displayCrossSellingShoppingCart`,
`displayOrderConfirmation2`, `displayReassurance`, `displayCustomerAccount`,
`displayGDPRConsent`).

Todos ellos llevan el marcador `- ~` con el comentario
`# Keep existing hooks and append them after`, igual que classic 3.1.0+.

---

## 10. Cobertura de plantillas: no es el problema

Recuento de `.tpl` (classic 3.1.2 vs Hummingbird 2.1.0/`develop`):

| | classic | Hummingbird | Δ |
|---|--:|--:|--:|
| `templates/` | 144 | 161 | +17 |
| `modules/` | 42 | 73 | +31 |
| **Total** | **186** | **234** | **+48 (+26 %)** |

Los **6 layouts son idénticos en nombre** y **136 plantillas comparten ruta
relativa exacta**. Solo 8 plantillas de classic no tienen homónima, y ninguna es
una página:

| Solo en classic | En Hummingbird |
|---|---|
| `catalog/manufacturers.tpl` | renombrada → `catalog/brands.tpl` |
| `_partials/password-policy-template.tpl` | movida → `components/password-policy-template.tpl` |
| `catalog/_partials/miniatures/pack-product.tpl` | renombrada → `miniatures/product-pack.tpl` |
| `checkout/_partials/header.tpl` / `footer.tpl` | absorbidas ⚠️ (no verificado dónde) |
| `checkout/_partials/cart-summary-items-subtotal.tpl` | absorbida ⚠️ (no verificado dónde) |
| `customer/_partials/order-detail-product-line-{no-,}return-mobile.tpl` | eliminadas |

🔑 **Conclusión: Hummingbird cubre el 100 % de las páginas de front. El problema
no es la cobertura, es que el marcado interno de esas 136 plantillas comunes está
completamente reescrito.** Reutilizable copiando y pegando: **0 %**.

---

## 11. SEO y rendimiento: lo que gana y lo que no

### 11.1 Lo que NO gana (marketing desmontado por medición)

| Afirmación habitual | Realidad |
|---|---|
| "Añade JSON-LD que classic no tiene" | **falso** — classic tiene los mismos tres `_partials/microdata/{head,product,product-list}-jsonld.tpl` en todas sus ramas |
| "Añade WebP/AVIF/lazy loading" | **falso** — classic ya los tenía desde 8.1 |
| "Bundle CSS más ligero" | **falso** — `theme.css` **345,1 KB vs 190,0 KB** (+82 %); comprimido 48,7 vs 33,4 KB (+46 %) |
| "Elimina jQuery" | **a medias** — del código del tema sí; de la página no (§6.1) |
| "Mejora Core Web Vitals" | **sin ningún benchmark público riguroso** |

Sobre el último punto, con detalle porque se vende mucho: **no existe ni un solo
dato público de LCP, CLS o INP** (ni CrUX ni laboratorio) comparando ambos temas
en igualdad de condiciones. Las cifras Lighthouse del blog oficial están **dentro
de una imagen**, no en un informe reproducible. Las cifras de agencia
("Lighthouse móvil de 40 a 75+", "TTFB 120 ms vs 250 ms") mezclan cambio de PHP,
de core y de hosting, y no aíslan el tema.

Peso comparado (medido sobre ficheros compilados):

| Fichero | Hummingbird 2.1.0 | classic (PS 8.2.7) |
|---|---|---|
| `assets/css/theme.css` | 345,1 KB (48,7 gz) | 190,0 KB (33,4 gz) |
| `assets/js/theme.js` | 206,1 KB (59,7 gz) | 199,7 KB (54,5 gz) |
| `themes/core.js` (core) | 140,3 KB (43,4 gz) | 140,3 KB (43,4 gz) |
| Fuentes empaquetadas | 74 ficheros / 1.460,7 KB | 25 ficheros / 1.285,9 KB |

`_partials/stylesheets.tpl` y `_partials/javascript.tpl` son **equivalentes byte a
byte** entre los dos temas: ninguno hace CSS crítico inline ni `defer`/`async`.

### 11.2 Lo que SÍ gana (medido)

- **Accesibilidad.** Es el diferencial más sólido, y el único con números claros:

  | Atributo | Hummingbird | classic |
  |---|--:|--:|
  | `role=` | 292 | 53 |
  | `aria-label` | 197 | 31 |
  | `aria-hidden` | 173 | 21 |
  | `visually-hidden` | 50 | 0 |
  | `tabindex` | 44 | 7 |
  | `aria-labelledby` | 35 | 7 |
  | `aria-current` | 24 | 0 |
  | `aria-expanded` | 23 | 9 |
  | `aria-controls` | 20 | 8 |
  | `autocomplete=` | 14 | 2 |
  | **`aria-live`** | **9** | **0** |
  | **`aria-describedby`** | **8** | **0** |
  | `<fieldset>` / `<legend>` | 2 / 2 | 0 / 0 |

  En CSS compilado: `:focus-visible` ×33 y `prefers-reduced-motion` ×33 en
  Hummingbird; **0 y 0** en classic.

  ⚠️ Las cifras que circulan de cumplimiento del EAA (">95 %", ">93 %") son
  **afirmaciones del proyecto sin auditoría publicada**, sin nivel WCAG declarado
  ni herramienta documentada. No repetirlas ante un cliente.

- **Imágenes.** Dos diferenciales reales:
  - **`srcset` + `sizes`** de verdad (miniatura 216w/261w/336w, ficha 400w/720w).
    Classic sirve un único tamaño.
  - **`fetchpriority="high"`** en la primera imagen de ficha, **eliminando el
    `loading="lazy"` que classic pone sobre la portada del producto** — que es un
    antipatrón LCP y probablemente el peor bug de rendimiento de classic.

- **Preload de fuentes** (`_partials/preload.tpl`, generado por
  `FontPreloadPlugin`). Classic no tiene bloque de preload.

- **JSON-LD mejor construido**, aunque no "nuevo": escapado con `|json_encode`
  (classic rompe el JSON si el nombre lleva comillas), `ItemList` con `Product` +
  `Offer` completos (classic solo `name` + `url`), `gtin`/`gtin12` correctos
  (classic mapeaba el UPC a `"gtin13": "0{$product.upc}"`), `itemCondition` vía
  `$product.condition.schema_url`, y el bloque `{if !empty($structured_data)}`
  que consume el JSON-LD del core de 9.2.

### 11.3 Regresiones SEO a vigilar

1. 🔴 **`ps_brandlist` y `ps_supplierlist` quedan desactivados** por el
   `theme.yml`. Desaparece el enlazado interno hacia las páginas de marca y
   proveedor, que quedan **huérfanas**. Es la regresión SEO más seria y es
   **reversible**: reactivar y reenganchar los módulos.
2. 🟠 `product-list-jsonld.tpl` envuelve cada ítem en `{if $item.show_price}` →
   en modo catálogo o B2B con precio tras login el resultado es `"itemList": []`.
   Classic listaba igualmente.
3. 🟠 `alt="{$image.legend}"` **sin fallback**. Classic hace
   `{if !empty($product.cover.legend)}…{else}{$product.name|truncate:30}{/if}`.
   Si la leyenda está vacía en BD, Hummingbird genera `alt=""`.

### 11.4 Coste operativo de las imágenes

El `theme.yml` de Hummingbird declara **16 tipos de imagen: los 7 clásicos + 9
propios** (`default_xs` 160, `default_sm` 216, `default_md` 261, `default_lg` 336,
`default_xl` 400, `product_main` 720×720, `product_main_2x` 1440×1440,
`category_cover` 1000×200, `category_cover_2x` 2000×400).

Activar el tema obliga a **regenerar todo el catálogo** (×2 con WebP, ×3 con
AVIF). En catálogos grandes es tiempo de proceso y disco muy relevantes.
Planificarlo antes, no descubrirlo el día del cambio.

---

## 12. Decidir: cuándo sí y cuándo no

### Cuándo SÍ

1. **Tienda nueva o rediseño ya presupuestado.** Aquí no hay debate: empezar en
   Hummingbird. Es la recomendación oficial y classic no recibirá funcionalidades
   nuevas.
2. **Obligación de accesibilidad (EAA).** Es el argumento más sólido y el único
   con datos que aguantan (§11.2).
3. **Se desarrollan módulos o temas para el Marketplace.** El proyecto lo ha
   declarado el nuevo estándar de referencia.
4. **Tema propio poco personalizado**, con pocas plantillas sobreescritas y poco
   CSS.
5. **PS 9.2 con One Page Checkout.** El OPC funciona out-of-the-box sobre
   Hummingbird; sobre un tema derivado de classic exige overrides mucho más
   amplios (ver `breaking-changes.md`, parte de 9.2).

### Cuándo NO (quedarse en classic)

1. **El tema es comercial** (Warehouse, Panda, Transformer, Alysum, Leo, ETS…).
   No se ha podido confirmar la existencia de **ningún** tema comercial construido
   sobre Hummingbird, ni ningún roadmap público. Migrar significa **abandonar el
   tema comercial**, no migrarlo.
2. **Hay muchos módulos de terceros de front** (megamenú, page builder, sliders,
   filtros). Cada uno es una superficie de rotura y no se controla el calendario
   de su proveedor.
3. **El objetivo inmediato es subir a 9.1/9.2 sin incidencias.** Cambiar de major
   de core *y* de tema en la misma ventana es sumar dos vectores de fallo. Subir
   primero, mantener classic, estabilizar, y evaluar el tema como proyecto aparte.
4. **La tienda ya está en 9.x y funciona con classic.** No hay ningún disparador
   técnico que obligue a cambiar.
5. **No hay toolchain Node/SCSS/TS ni quien la mantenga.** Si el proceso actual es
   "editar `custom.css` y subir por FTP", el cambio es organizativo antes que
   técnico. (Matiz: un **tema hijo** de Hummingbird sí evita la toolchain, §8.)
6. **El objetivo es PS 8.x, o 8.x y 9.x a la vez.** Hummingbird 2.x exige 9.1+.

### La regla práctica

> Si cambiar de tema obliga a reescribir el CSS, el JS y el marcado —y siempre
> obliga—, entonces no es una migración: es un **rediseño**. Preséntalo,
> presupuéstalo y planifícalo como rediseño, con su fase de QA y su riesgo de
> conversión. Si el negocio no quiere pagar un rediseño ahora, **la respuesta
> correcta es quedarse en classic**, que sigue mantenido y funcional en 9.x.

---

## 13. Si aun así hay que migrar: orden de ataque

No existe ruta oficial. Este orden está ordenado por retorno sobre esfuerzo.

1. **Integrar la `bootstrap-compatibility-layer` (JS + CSS)**. Resuelve de golpe
   los ~78 `data-toggle`/`data-target`/`data-dismiss`/`data-ride`/`data-slide` de
   classic y, gracias al `MutationObserver`, también los del contenido AJAX de
   módulos de terceros. Máximo retorno por esfuerzo.
2. **Script determinista** (regex sobre los `.tpl`, **no agentes LLM**) para
   reescribir las cinco familias que la capa no cubre (~236 ocurrencias):
   `col-xs-*` → `col-*`, `hidden-*-up/down` → `d-*`, `text-xs-*` →
   `text-start/end`, `float-xs-*` → `float-start/end`, `card-block` → `card-body`.
3. **Revisar a mano cada `hidden-*` convertida.** `d-none d-md-block` fuerza
   `display:block` y rompe elementos inline, flex o table-cell. Son 68 casos.
4. **Auditar los módulos de terceros uno a uno**: marcado, jQuery y selectores
   `js-*`.
5. **Reescribir el SCSS** contra la arquitectura `@layer`, empezando por
   sobreescribir variables de Bootstrap antes de escribir CSS propio.
6. **Reactivar `ps_brandlist` y `ps_supplierlist`** y reengancharlos (§11.3).
7. **Regenerar miniaturas** de los 9 tipos de imagen nuevos (§11.4).

Sobre el coste: la única cifra que circula ("2 a 6 semanas") es de agencia, sin
desglose. Tratarla como orden de magnitud, nunca como presupuesto.

---

## 14. Qué NO tocar en Hummingbird

1. **No editar nada dentro de `assets/`.** Es salida del build de Webpack; se
   pierde en el siguiente `npm run build`. Tocar `src/scss/` y `src/js/` y
   recompilar, o usar `assets/css/custom.css` / `assets/js/custom.js`.
2. **No añadir jQuery al código del tema.** Está prohibido explícitamente.
3. **No poner `theme_settings: {core_scripts: false}` "para quitar jQuery".**
   Elimina también el bus de eventos `prestashop` y rompe casi todos los módulos.
4. **No enganchar JS a clases CSS.** Hummingbird usa `data-ps-*`, y 35 selectores
   `js-*` de classic han desaparecido (§6.3).
5. **No usar `@extend` en el SCSS**, y respetar el orden de `@layer`: los
   overrides puestos en la capa equivocada dejan de ganar.
6. **No copiar el `meta.compatibility` del padre** en el `theme.yml` de un tema
   hijo (§2).
7. **No portar un `.tpl` de classic copiando y pegando.** Reutilizable: 0 %.
   Reescribir con el `.tpl` de classic al lado como referencia funcional, no como
   código fuente.
8. **No borrar los `image_types` nuevos** ni activar el tema sin regenerar
   miniaturas (§11.4).
9. **No cargar la capa de compatibilidad desde unpkg en producción** (§5).
10. **No afirmar que Hummingbird "mejora el rendimiento"** sin medirlo en la
    tienda concreta. El CSS pesa +82 % y jQuery se sigue cargando.

---

## 15. No verificado — no afirmar

1. Fecha de release estable de PrestaShop 9.2. **No anunciada.**
2. Versión de classic y de Hummingbird que llevará el 9.2.0 estable.
3. Requisitos PHP oficiales de 9.2 (devdocs no tiene fila para 9.2).
4. **Fecha de fin de soporte o eliminación de classic. No existe ningún anuncio.**
5. Listado íntegro de hooks eliminados o renombrados en Hummingbird. Existe
   `/9/themes/hummingbird/hooks/` (responde 200) pero su contenido se renderiza
   por JS y no se ha podido extraer. Lo único confirmado son los tres cambios de
   `breaking-changes.md`.
6. Si el `theme.js` compilado de Hummingbird 2.1.0 empaqueta jQuery realmente, o
   si las dependencias declaradas son residuales (§6.1).
7. Si los 70 selectores `js-*` comunes siguen enganchados a JS en Hummingbird.
8. Dónde se resolvieron `checkout/_partials/header.tpl`, `footer.tpl` y
   `cart-summary-items-subtotal.tpl` de classic (§10).
9. Existencia de temas comerciales sobre Hummingbird en Addons, y roadmap de los
   proveedores. Ni confirmada ni desmentida.
10. Adopción real en producción. No verificable con fuentes públicas.
