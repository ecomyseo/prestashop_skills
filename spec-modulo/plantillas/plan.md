# Plan técnico: [NOMBRE DE LA FUNCIONALIDAD]

**Módulo**: `[nombre_del_modulo]`
**Especificación**: `spec.md`
**Fecha**: [dd/mm/aaaa]

<!--
  Aquí SÍ va el cómo. Y antes de nada se pasa la puerta de abajo: si no se pasa, no hay plan.
-->

## Contexto técnico

| | |
|---|---|
| **Versiones de PrestaShop** | [8.2.x / 9.1.x / …] — probado en [8.2b, 9.1b, 9.2] |
| **PHP** | [7.4 y 8.x — lo que obligue la versión objetivo] |
| **Estructura** | legacy · [o «Symfony, porque el destino es SOLO 9.1 y lo ha confirmado el usuario»] |
| **Dependencias** | ninguna (funciones nativas y `require_once`) |
| **Dónde se guardan los datos** | [tabla propia `ps_xxx` / `Configuration` / ambas] |
| **Dominio de traducción** | `Modules.[Studly].[Admin\|Shop]` |
| **Cómo se prueba** | [pantallas que hay que abrir, casos que hay que reproducir] |

---

## PUERTA: comprobación contra el CLAUDE.md

<!--
  Se responde a TODAS. Con «sí», o con el motivo escrito. Esto no es papeleo: cada línea
  viene de una avería que ya costó horas.
-->

| Regla | ¿Se cumple? | Si no, por qué |
|---|---|---|
| Estructura legacy: sin `src/`, `config/`, `composer.json`, `vendor/`, `upgrade/` | | |
| Clase principal = nombre de la carpeta, sin `namespace` | | |
| `index.php` en TODAS las carpetas, recursivo | | |
| Cabecera en todos los ficheros (`.php`, `.js`, `.css`, `.tpl`, `.sql`), sin líneas en blanco antes del `<?php` | | |
| `if (!defined('_PS_VERSION_')) exit;` en todos los PHP | | |
| Migraciones dentro de `addnewfeatures()` con `try{}` | | |
| Configuración: UN `HelperForm` con pestañas y un solo botón de guardar | | |
| `isUsingNewTranslationSystem()` a true y todas las cadenas con `d=`, nunca `mod=` | | |
| `.xlf` en `translations/<locale>/Modules<Nombre><Dominio>.<locale>.xlf` | | |
| Multitienda: decidido global o por tienda, y avisado en pantalla si es global | | |
| Endpoints AJAX: las TRES cabeceras anti-caché y **sin** `Content-Type` | | |
| Nada de scans sobre histórico: ventana ≤ 24 h, seed previo, kill switch | | |
| `foreach (is_array($x) ? $x : array())`, nunca `foreach ((array) $x)` | | |
| Sin `LIMIT 1` a mano; `pSQL()` en todo el SQL manual | | |
| Ficha de producto del back-office: skill `prestashop-admin-product` invocado | | |
| Overrides (si hay): método manual `override_v8` + copy, nunca `installOverrides()` | | |

---

## Ficheros que se tocan

```
[nombre_del_modulo]/
  [nombre_del_modulo].php        [qué se añade: hooks, install, addnewfeatures]
  classes/
    [Clase].php                  [para qué]
  controllers/admin/
    [Admin...Controller].php     [para qué]
  controllers/front/
    [...].php                    [para qué]
  views/
    templates/admin/[...].tpl
    js/[...].js
    css/[...].css
  translations/
    es-ES/Modules[...]es-ES.xlf
  sql/
  logs/
```

<!-- Ficheros NUEVOS y ficheros que se MODIFICAN, separados. Lo que no esté aquí, no se toca. -->

---

## Hooks

| Hook | Para qué | ¿Existe ya en el módulo? |
|---|---|---|
| `[hookNombre]` | [qué pinta o qué hace] | [sí / se registra en `install()` y en `addnewfeatures()`] |

<!-- Todo hook registrado tiene que tener su método declarado, aunque esté vacío. -->

---

## Datos

### Tablas

```sql
-- ps_[tabla]: [para qué sirve]
-- columnas nuevas sobre tablas existentes: van en addnewfeatures(), con
-- comprobación en INFORMATION_SCHEMA antes del ALTER TABLE.
```

<!--
  Si se añaden campos a un ObjectModel, las columnas TIENEN que existir antes de que el
  modelo se cargue (también en el front) o el SELECT revienta. La migración va guardada por
  un flag de Configuration e invocada en install(), en addnewfeatures() y en el constructor.
-->

### Claves de configuración

| Clave | Global o por tienda | Valor por defecto |
|---|---|---|
| `[MODULO_LO_QUE_SEA]` | [Configuration::get / getGlobalValue] | [...] |

---

## Traducciones

- Dominios: `Modules.[Studly].Admin`, `Modules.[Studly].Shop`
- Idiomas: [...]
- **Cadenas que el extractor NO ve** y hay que sacar a mano:
  - [ ] las que están dentro de `.js` → sacarlas a PHP e inyectarlas con `Media::addJsDef`
  - [ ] las que se guardan ya traducidas en la base de datos (pestañas, estados) → resolver
        en `install()` con `$this->trans($src, [], $dom, $locale)`, idioma a idioma
  - [ ] los asuntos de los correos → con el idioma del destinatario, no el del empleado
  - [ ] lo que se monte con `smarty->fetch()` → traduce con el idioma del CONTEXTO

---

## Lo que nos saltamos y por qué

<!-- Solo si hay algo. Una fila por excepción, con el motivo en una frase. Sin motivo, no hay excepción. -->

| Qué regla | Por qué hace falta | Qué se probó antes |
|---|---|---|
| | | |

---

## Riesgos

| Riesgo | Qué pasaría | Qué se hace para evitarlo |
|---|---|---|
| [...] | [...] | [...] |
