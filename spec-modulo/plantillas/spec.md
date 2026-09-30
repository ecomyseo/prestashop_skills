# Especificación: [NOMBRE DE LA FUNCIONALIDAD]

**Módulo**: `[nombre_del_modulo]`
**Carpeta**: `[NNN-nombre-corto]`
**Fecha**: [dd/mm/aaaa]
**Estado**: borrador
**Lo que pidió el usuario**: "[copiar aquí, literal, lo que pidió]"

<!--
  AQUÍ NO SE HABLA DE TECNOLOGÍA. Ni hooks, ni tablas, ni ficheros, ni clases.
  Todo eso va en plan.md. Si al escribir sale un nombre de hook, va en el sitio equivocado.

  Lo que no se sepa NO SE INVENTA: se marca [FALTA ACLARAR: la pregunta].
-->

## El encargo en una frase

[Qué tiene que poder hacer alguien que hoy no puede, y qué gana con ello.]

---

## Contexto de PrestaShop *(obligatorio)*

<!--
  Estas cinco líneas deciden la arquitectura entera y casi nunca vienen en el encargo.
  Sin ellas cerradas, no se pasa a planificar.
-->

| | |
|---|---|
| **Versiones objetivo** | [8.x / 9.x / las dos / SOLO 9.1] |
| **Quién lo usa** | [comerciante en el back-office · cliente en el front · un cron · el webservice] |
| **Multitienda** | [configuración global / por tienda] — [y qué se ve si `Shop::isFeatureActive()`] |
| **Idiomas a entregar** | [es-ES, en-US, …] |
| **Datos con histórico que toca** | [ninguno / `orders`, `customers`… ] |

<!--
  Si toca una tabla con histórico, esta especificación ACTIVA la REGLA 0 del CLAUDE.md:
  ventana de 24 h como mucho, seed previo y kill switch. Escribirlo aquí en negrita.
-->

---

## Historias de usuario *(obligatorio)*

<!--
  Ordenadas por importancia: P1 es lo que no puede faltar.
  Cada historia tiene que poder ENTREGARSE Y PROBARSE SOLA: si solo se hace la P1, el
  módulo ya tiene que servir para algo.
-->

### Historia 1 — [título corto] (P1)

[Contarla en cristiano, como se la contarías al comerciante.]

**Por qué es la primera**: [qué valor da y por qué antes que las demás]

**Cómo se prueba sola**: [qué hay que abrir y qué hay que ver para dar esto por bueno]

**Casos de aceptación**:

1. **Dado** [situación de partida], **cuando** [lo que hace la persona], **entonces** [lo que pasa]
2. **Dado** [...], **cuando** [...], **entonces** [...]

---

### Historia 2 — [título corto] (P2)

[...]

**Por qué esta prioridad**: [...]

**Cómo se prueba sola**: [...]

**Casos de aceptación**:

1. **Dado** [...], **cuando** [...], **entonces** [...]

---

## Casos raros

<!-- Los que en PrestaShop muerden siempre. Quitar los que no apliquen y añadir los propios. -->

- ¿Qué pasa si no hay ningún resultado? (Ojo: `Db::executeS()` devuelve `false`, no un array vacío.)
- ¿Y con el carrito vacío, o sin cliente identificado?
- ¿Y con varias tiendas activas y la configuración a medio poner?
- ¿Y en un idioma que no se ha traducido?
- ¿Y con la caché de Smarty encendida, si lo que se pinta depende del visitante?
- ¿Y si el pedido ya está en un estado que no admite el cambio?

---

## Requisitos *(obligatorio)*

<!-- Uno por línea, numerados, comprobables. Nada de "tiene que ir bien". -->

- **RF-001**: El módulo TIENE QUE [capacidad concreta]
- **RF-002**: El comerciante TIENE QUE PODER [acción concreta]
- **RF-003**: El módulo NO PUEDE [lo que está prohibido, si hay algo]
- **RF-004**: [FALTA ACLARAR: la pregunta que hay que hacerle al usuario]

### Datos que aparecen *(si los hay)*

- **[Cosa 1]**: [qué representa y qué campos tiene, sin decir de qué tipo son]
- **[Cosa 2]**: [qué representa y con qué se relaciona]

---

## Cómo sabremos que está bien *(obligatorio)*

<!-- Medible y sin tecnología: nada de "el hook responde en 200 ms". -->

- **CE-001**: [p. ej. «el comerciante configura el módulo entero sin salir de una pantalla»]
- **CE-002**: [p. ej. «un pedido de 40 líneas se procesa sin que la pantalla se quede colgada»]
- **CE-003**: [p. ej. «no queda ni una cadena sin traducir en los idiomas entregados»]

---

## Suposiciones

<!-- Lo que se ha dado por hecho al no venir en el encargo. Se escribe para que el usuario pueda desmentirlo. -->

- [p. ej. «la tienda usa el tema por defecto»]
- [p. ej. «no hace falta soporte para combinaciones en esta primera versión»]

---

## Fuera de alcance

<!-- Lo que ALGUIEN podría esperar y NO se va a hacer. Esta lista ahorra discusiones al entregar. -->

- [...]
