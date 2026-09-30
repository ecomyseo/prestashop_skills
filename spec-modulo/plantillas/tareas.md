# Tareas: [NOMBRE DE LA FUNCIONALIDAD]

**Módulo**: `[nombre_del_modulo]`  ·  **Especificación**: `spec.md`  ·  **Plan**: `plan.md`

<!--
  Agrupadas POR HISTORIA. Cada grupo tiene que poder entregarse y probarse solo: al acabar
  la P1, el usuario ya tiene algo que abrir.

  [P] = se puede hacer a la vez que las otras [P] del mismo grupo (tocan ficheros distintos).
  Las que tocan el mismo fichero van en fila, sin [P].

  Cada tarea dice EL FICHERO. Una tarea que no dice dónde no es una tarea.
-->

## Preparación

- [ ] **T001** Leer el JSON del módulo en `<carpeta de trabajo>\Json MCP modules\[modulo].json`; si no está, generarlo con `scan_module_structure`
- [ ] **T002** Primera pasada de ortografía sobre la carpeta del módulo (de las tres del CLAUDE.md)
- [ ] **T003** [P] Crear `[carpeta nueva]/` con su `index.php`
- [ ] **T004** [P] [otra preparación]

---

## Historia 1 — [título] (P1)   ← el mínimo que ya sirve para algo

**Cuando esté: se puede probar abriendo [pantalla] y comprobando [qué].**

- [ ] **T010** [qué se hace] en `[ruta/del/fichero.php]`
- [ ] **T011** [P] [qué se hace] en `[otra/ruta.tpl]`
- [ ] **T012** Declarar el hook `[hookX]` en `[modulo].php` y registrarlo en `install()` y en `addnewfeatures()`
- [ ] **T013** Comprobar: [lo que hay que ver en pantalla]

---

## Historia 2 — [título] (P2)

**Cuando esté: se puede probar [...].**

- [ ] **T020** [...]
- [ ] **T021** [P] [...]

---

## Cierre  (SIEMPRE, no se salta)

<!-- Estas salen del CLAUDE.md y son las que siempre se olvidan. -->

- [ ] **T900** `index.php` en TODAS las carpetas nuevas, recursivo
- [ ] **T901** Cabecera en todos los ficheros nuevos (`.php`, `.js`, `.css`, `.tpl`, `.sql`)
- [ ] **T902** `php -l` en todos los `.php` — cero errores
- [ ] **T903** `addnewfeatures()` al día: columnas, hooks y pestañas nuevas
- [ ] **T904** Segunda pasada de ortografía, **antes** de tocar los `.xlf`
- [ ] **T905** `.xlf` de cada idioma, en `translations/<locale>/Modules<Nombre><Dominio>.<locale>.xlf`
- [ ] **T906** Repasar que no queda ningún `{l s='...' mod='...'}` (no lee los `.xlf`)
- [ ] **T907** Los tres documentos `.md` (técnico, desarrollo y guía de usuario) en `manuales/<modulo>/`
- [ ] **T907b** Las capturas, en `capturas/<modulo>/`. Dentro del módulo NO va nada de esto.
- [ ] **T908** Registrar el módulo con el skill `prestashop_connect_gmartos`
- [ ] **T909** Tercera y última pasada de ortografía: que quede `TOTAL: 0` — **y preguntar al usuario** si está todo correcto y si quiere que se revise
- [ ] **T910** **Preguntar al usuario** si se instala en las tiendas locales. Autorizado: `8.2b` por defecto, y `python servir.py parar` al terminar
- [ ] **T911** Si toca alguna pantalla: abrirla, pulsar cada botón, exigir cero errores de JavaScript y capturar

---

## Aparecido por el camino

<!-- Lo que no estaba previsto se apunta AQUÍ como tarea, no se hace por lo bajo. -->

- [ ] **T950** [...]
