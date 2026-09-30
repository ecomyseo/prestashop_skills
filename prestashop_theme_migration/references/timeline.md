# Línea temporal de features del tema classic

Verificado extrayendo `themes/classic` de cada tag del repo `PrestaShop/PrestaShop`
(1.7.x) y de `PrestaShop/classic-theme` (8.x / 9.x).

## Matriz de features

`—` = no existe · `✓` = presente

| Feature | 1.7.0 | 1.7.5 | 1.7.6 | 1.7.7 | 1.7.8 | 8.0 | 8.1 | 8.2 | 9.0 | 9.1 |
|---|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| Microdatos inline (`itemprop`) | ✓ | ✓ | ✓ | ✓ | — | — | — | — | — | — |
| JSON-LD (`application/ld+json`) | — | — | — | — | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `loading="lazy"` | — | — | — | — | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `width`/`height` en `<img>` | — | — | — | — | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Selectores `js-*` (`selectors.js`) | — | — | — | — | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `$product.url` en miniaturas | — | — | — | — | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Política de contraseñas | — | — | — | — | — | ✓ | ✓ | ✓ | ✓ | ✓ |
| **Descripción adicional de categoría** | — | — | — | — | — | **✓** | ✓ | ✓ | ✓ | ✓ |
| `payment-{id}-form` | — | — | — | — | — | ✓ | ✓ | ✓ | ✓ | ✓ |
| `$order_customer` | — | — | — | — | — | ✓ | ✓ | ✓ | ✓ | ✓ |
| `displayCheckoutBeforeConfirmation` | — | — | — | — | — | ✓ | ✓ | ✓ | ✓ | ✓ |
| Embalaje reciclable | — | — | — | — | — | ✓ | ✓ | ✓ | ✓ | ✓ |
| **`<picture>` + WebP + AVIF** | — | — | — | — | — | — | **✓** | ✓ | ✓ | ✓ |
| `data-image-*-sources` | — | — | — | — | — | — | ✓ | ✓ | ✓ | ✓ |
| `fetchpriority="high"` | — | — | — | — | — | — | — | — | ✓ | ✓ |
| Presenters marca/proveedor | — | — | — | — | — | — | — | — | ✓ | ✓ |
| `{elseif}` obligatorio (Smarty 4.5) | — | — | — | — | — | — | — | — | ✓ | ✓ |
| `availability == 'in_stock'` | — | — | — | — | — | — | — | — | ✓ | ✓ |
| Multi-envío | — | — | — | — | — | — | — | — | 9.0.2 | ✓ |
| `$product.quantity_required` | — | — | — | — | — | — | — | — | — | ✓ |
| Campo `textarea` en formularios | — | — | — | — | — | — | — | — | 9.0.1 | ✓ |
| `displayCartExtraProductInfo` | — | — | — | — | — | — | — | — | 9.0.1 | ✓ |
| `displayCustomerAccountTop` | — | — | — | — | — | — | — | — | 9.0.1 | ✓ |
| `- ~` en hooks de `theme.yml` | — | — | — | — | — | — | — | — | — | ✓ |

En la columna 9.0 el número indica el **patch** exacto en que entra la feature;
`✓` sin número significa que ya está desde el primer release de esa minor.

## Altas y bajas de plantillas

Solo se listan los cambios; las versiones no mencionadas no añaden ni quitan
ficheros.

### 1.7.1
```
+ catalog/_partials/product-additional-info.tpl
- cms/_partials/sitemap-tree-branch.tpl
+ cms/_partials/sitemap-nested-list.tpl
```

### 1.7.5
```
+ catalog/_partials/category-header.tpl
```

### 1.7.6
```
+ catalog/_partials/product-flags.tpl
+ checkout/_partials/cart-summary-subtotals.tpl
```

### 1.7.7
```
+ catalog/_partials/productlist.tpl
```

### 1.7.8
```
+ _partials/microdata/head-jsonld.tpl
+ _partials/microdata/product-jsonld.tpl
+ _partials/microdata/product-list-jsonld.tpl
+ _partials/pagination-seo.tpl
+ catalog/_partials/subcategories.tpl
+ checkout/_partials/cart-summary-products.tpl
+ checkout/_partials/cart-summary-top.tpl
+ _partials/helpers.tpl          (en las últimas 1.7.8.x)
+ catalog/_partials/variant-links.tpl
+ catalog/listing/search.tpl
```

### 8.0
```
+ _partials/password-policy-template.tpl
+ catalog/_partials/category-footer.tpl
+ customer/_partials/account-transformation-form.tpl
+ errors/410.tpl
```

### 8.1 y 8.2
Sin altas ni bajas.

### 9.0
```
+ catalog/_partials/miniatures/supplier.tpl
- catalog/brands.tpl
```

### 9.0.2 (tema 3.0.4) — no es 9.1
```
+ checkout/_partials/order-confirmation-table-multishipment.tpl
+ checkout/_partials/order-final-summary-table-multishipment.tpl
```

### 9.0.3 (tema 3.0.5) — no es 9.1
```
+ customer/_partials/order-carrier.tpl
+ customer/_partials/order-shipments.tpl
+ customer/_partials/order-detail-no-return-multishipment.tpl
+ customer/_partials/order-detail-return-multishipment.tpl
+ customer/_partials/order-detail-product-line-no-return.tpl
+ customer/_partials/order-detail-product-line-no-return-mobile.tpl
+ customer/_partials/order-detail-product-line-return.tpl
+ customer/_partials/order-detail-product-line-return-mobile.tpl
```

### 9.1
Sin altas ni bajas: hereda las de 3.0.4 / 3.0.5.

Número total de plantillas `.tpl`: 117 (1.7.0) → 121 (1.7.6) → 130 (1.7.8.11)
→ 134 (8.0 a 9.0.1) → 136 (9.0.2) → 144 (9.0.3 en adelante).

## Correspondencia exacta tema ↔ PrestaShop

Sacada del `composer.lock` de cada tag. El tema **cambia dentro de la misma
minor**, así que hay que mirar el patch:

| Tema classic | Fecha | PrestaShop |
|---|---|---|
| 2.0.6 | 2022-10-25 | 8.0.0 |
| 2.1.1 | 2023-05-26 | 8.1.0 |
| 2.1.3 | 2024-02-26 | **8.2.0** |
| 2.2.0 | 2025-01-30 | 8.2.1 – 8.2.7 |
| 2.2.1 | 2025-11-26 | (8.2.8, pendiente) |
| 3.0.1 | 2025-01-29 | solo 9.0.0-beta.1 |
| 3.0.2 | 2025-05-02 | 9.0.0 |
| 3.0.3 | 2025-10-07 | 9.0.1 |
| 3.0.4 | 2025-11-21 | 9.0.2 |
| 3.0.5 | 2025-12-16 | 9.0.3 |
| **3.0.6** | 2026-01-23 | **ninguna — tag huérfano** |
| **3.1.0** | 2026-01-21 | **ninguna — tag huérfano** |
| 3.1.1 | 2026-02-17 | 9.1.0, 9.1.1 |
| 3.1.2 | 2026-05-11 | 9.1.2, 9.1.3, 9.1.4, 9.2.0-beta.1 |

**3.1.2 es el último tag de classic. No existe 3.2.x.**

⚠️ **3.0.6 y 3.1.0 existen en GitHub pero nunca se distribuyeron en ningún ZIP de
PrestaShop.** Si un `theme.yml` declara esas versiones, es una instalación
manual, no un core estándar. (3.1.0 es además un tag de una sola línea: su único
cambio frente a 3.0.5 es el `config/theme.yml` con el marcador `- ~`.)

Las ramas 2.2.x, 3.0.x y 3.1.x **están vivas a la vez**: PrestaShop mantiene el
tema en paralelo para 8.2, 9.0 y 9.1. Publicar en una rama no implica publicar
en las otras, y hay backports en ambos sentidos.

Para comprobarlo en cualquier tag sin adivinar:

```bash
curl -sL https://raw.githubusercontent.com/PrestaShop/PrestaShop/<TAG>/composer.lock \
 | jq -r '.packages[] | select(.type=="prestashop-theme") | .name+"="+.version'
```

Devuelve las **dos** líneas, `prestashop/classic` y `prestashop/hummingbird`.
Ojo al nombre: el repositorio se llama `classic-theme`, el paquete Composer es
`prestashop/classic`.

## Tema Hummingbird (paralelo a classic desde 9.0)

| Hummingbird | Fecha | `from` | `to` | Bootstrap | PrestaShop |
|---|---|---|---|---|---|
| v1.0.0 | 2025-01-29 | 8.1.0 | `~` | 5.2.0 | 8.1.x+ (aparte) |
| v1.0.1 | 2025-06-16 | 8.1.0 | `~` | 5.2.0 | 9.0.1 – 9.0.3 |
| v2.0.0 | 2026-02-11 | 9.1.0 | `~9.1.0` | 5.3.3 | **solo 9.1.x** |
| v2.1.0 | 2026-07-14 | 9.2.0 | `~9.2.0` | 5.3.3 | **solo 9.2.x** |

🔴 **v2.1.0 no se instala en 9.1.x** pese a que sus notas de release digan
"default theme in PrestaShop 9.1+": su `theme.yml` exige `from: 9.2.0`. Manda el
fichero. Para 9.1 hay que usar v2.0.0.

🔴 **Hummingbird 2.x pone tope superior** (`to: ~9.x.0`); classic **no**
(`to: ~`). Un tema hijo que copie el `meta.compatibility` del padre quedará
bloqueado al subir de minor.

⚠️ El campo `version:` de `theme.yml` **no es fiable en ninguno de los dos
temas**: classic 3.0.3 y 3.0.4 declaran ambos `3.0.2`; Hummingbird v1.0.0 declara
`0.2.0`; v0.1.6 declara `0.1.5`.

Detalle completo en `hummingbird.md`.

## Estado de las ramas de PrestaShop (a 24-07-2026)

| Rama | Última estable | Fecha |
|---|---|---|
| 8.2 | **8.2.7** | 2026-06-04 |
| 9.0 | 9.0.3 | 2026-02-03 |
| 9.1 | **9.1.4** | 2026-06-04 |
| 9.2 | **ninguna** — solo `9.2.0-beta.1` | 2026-07-22 |

Feature freeze de 9.2: 9-jul-2026. **No hay fecha anunciada de release estable
de 9.2**, así que cualquier planificación que la dé por hecha es especulación.

## Volumen de cambio por salto

Sirve para estimar el esfuerzo antes de empezar:

| Salto | Plantillas modificadas | Dificultad |
|---|---|---|
| 1.7.0 → 1.7.6 | ~110 (casi todas) | media — mucho ruido de cabeceras de licencia |
| 1.7.6 → 1.7.8.11 | ~112 | **alta** — JSON-LD + selectores `js-*` |
| 1.7.8.11 → 8.0 | 38 | **alta** — checkout |
| 8.0 → 8.1 | 23 | baja — solo imágenes |
| 8.1 → 8.2 | 3 | trivial |
| 8.2 → 9.0 | 16 | **muy alta** — sintaxis de Smarty + presenters |
| 9.0 → 9.1 | 11 | media — multi-envío |
| 9.1.0 → 9.1.2 | 2 | trivial — breadcrumb y contactform |
| 9.1 → 9.2 | 0 en classic | baja **si no se toca el checkout**; **alta si se adopta el One Page Checkout** |
| classic → Hummingbird | **todas** | **no es un salto: es un rediseño** |

⚠️ La última fila no es una exageración retórica. Classic y Hummingbird comparten
136 rutas de plantilla, pero el marcado interno está reescrito por completo
(Bootstrap 4-alpha.5 → 5.3.3, `js-*` → `data-ps-*`, BEM, `@layer`).
Reutilizable copiando y pegando: **0 %**. Ver `hummingbird.md`.

En 9.2 el tema classic **no cambia** (sigue 3.1.2, el mismo que 9.1.2+): todo el
coste está en el core, y concentrado en el contrato DOM del One Page Checkout.

En los saltos de la rama 1.7 buena parte del diff es la cabecera de licencia
(cambia de "2007-2016 PrestaShop" a "Copyright since 2007 PrestaShop SA and
Contributors" y de OSL-3.0 a AFL-3.0). Al diffear conviene filtrarla:

```bash
diff -ru vA/templates vB/templates | grep -vE '^[-+ ] \*|^[-+ ]\{\*\*'
```

## Rutas de migración habituales

- **1.7.6 → 1.7.8.11** — el más común dentro de 1.7. Lo caro son los selectores
  `js-*` y el JSON-LD, no el maquetado.
- **1.7.8.11 → 8.2.7** — salto natural de "modernizar sin cambiar de rama mayor".
  Bootstrap idéntico; el riesgo está concentrado en el checkout (8.0) y se gana
  WebP/AVIF gratis (8.1).
- **8.1 → 9.1.x** — el más caro. Obliga a pasar por la sintaxis de Smarty y por
  los presenters. Presupuestar en consecuencia y no venderlo como un retoque.
- **8.2.7 → 9.0** antes que ir directo a 9.1: separar el cambio de sintaxis del
  de multi-envío hace el problema mucho más fácil de depurar.
- **9.1.4 → 9.2** — barato para el tema mientras no se toque el checkout. El
  tema classic es el mismo fichero a fichero. Lo que cuesta es adoptar el One
  Page Checkout, y eso es opcional.
- **classic → Hummingbird** — no es una ruta de migración, no existe guía oficial
  y la URL de devdocs que la prometería da 404. Es un rediseño. Ver
  `hummingbird.md` § 12 y § 13 antes de aceptar el encargo.
