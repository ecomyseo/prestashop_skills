# Manual de `spec-modulo`

Cómo especificar antes de picar código, según el tipo de trabajo. Método adaptado de
[GitHub Spec Kit](https://github.com/github/spec-kit); el skill es **`spec-modulo`**.

---

## 1. Lo que hay que saber antes de empezar

### Las seis fases

| Fase | Orden | Sale de ahí | Qué contesta |
|---|---|---|---|
| **Especificar** | `/spec-modulo especificar <lo que quiero>` | `spec.md` | qué y para quién |
| **Aclarar** | `/spec-modulo aclarar` | `spec.md` sin marcadores | lo que no estaba claro |
| **Planificar** | `/spec-modulo plan` | `plan.md` | cómo |
| **Tareas** | `/spec-modulo tareas` | `tareas.md` | en qué orden |
| **Implementar** | `/spec-modulo implementar` | el código | — |
| **Converger** | `/spec-modulo converger` | `notas.md` | qué falta todavía |

### Dónde va todo

```
modulos IA/
  _specs/<proyecto>/NNN-<nombre>/    spec.md · plan.md · tareas.md · notas.md
  capturas/<proyecto>/               las capturas
  manuales/<proyecto>/               los .md y los PDF que se entregan
  utiles/<proyecto>/                 guiones sueltos
  <proyecto>/                        SOLO el proyecto
```

### Tres reglas que no se saltan

1. **La constitución son los CLAUDE.md.** No hay `constitution.md`. Lo que sí hay es la
   **puerta** del `plan.md`: antes de escribir una tarea se responde por escrito a las
   reglas, con un sí o con el motivo de la excepción.
2. **Lo que no se sepa se marca `[FALTA ACLARAR: …]`, no se inventa.**
3. **Cada historia tiene que poder entregarse y probarse sola.** Si al acabar la P1 no hay
   nada que abrir y mirar, las historias están mal partidas.

### Cuándo NO usar esto

Un arreglo de dos líneas, una cadena que falta, una tilde, un `LIMIT 1` de más. Se arregla
y ya. Tres documentos para cambiar una línea es el derroche contra el que avisa el
CLAUDE.md. **Ante la duda, se pregunta.**

---

## 2. Módulo de PrestaShop

El caso normal. Plantillas tal cual.

**En el `spec.md`, lo que decide todo y nunca viene en el encargo:**

- **Versiones objetivo.** `8.x`, `9.x` o las dos. Sin un «solo PrestaShop 9.1» explícito y
  confirmado, la estructura es legacy y no se permite `src/` ni `config/`.
- **Quién lo usa:** comerciante (back-office), cliente (front), un cron o el webservice.
  Cada uno es un controlador distinto.
- **Multitienda:** configuración global o por tienda. Se decide aquí, no al final.
- **Idiomas** que hay que entregar traducidos.
- **Tablas con histórico** que se tocan. Si hay alguna, esta especificación activa la
  REGLA 0: ventana de 24 h como mucho, seed previo y kill switch.

**La puerta del `plan.md`** trae las dieciséis comprobaciones: estructura legacy, clase sin
`namespace`, `index.php` recursivo, cabeceras, `addnewfeatures()`, un solo `HelperForm` con
pestañas, `d=` y nunca `mod=`, ruta de los `.xlf`, anti-caché AJAX sin `Content-Type`,
`is_array()` en vez de `(array)`, `pSQL()`, y el skill `prestashop-admin-product` si se
toca la ficha de producto.

**Al converger:** `php -l`, `index.php` en todas las carpetas, cadenas en el `.xlf`,
ortografía `TOTAL: 0` (**y preguntar**), y **preguntar** antes de instalar en las tiendas.

**Ejemplo de historias bien partidas** — un módulo de presupuestos:

- **P1** El comerciante crea un presupuesto a mano y lo ve en un listado. *(Ya sirve.)*
- **P2** Se lo envía al cliente por correo con un enlace.
- **P3** El cliente lo acepta y se convierte en pedido.
- **P4** Caducidad automática y aviso.

---

## 3. Importación de datos (catálogos, marketplaces, ERP)

Mirakl, Factusol, un XML de fabricante, un CSV de 40.000 líneas. Aquí lo que se rompe
nunca es el código: son **los datos**.

**En el `spec.md`, además de lo normal:**

| | |
|---|---|
| **De dónde vienen** | fichero (CSV, XML, JSON), API, base de datos ajena |
| **Cada cuánto** | una vez, a mano, o un cron cada X |
| **Volumen real** | cuántas filas hoy y cuántas dentro de un año |
| **Qué identifica una fila** | referencia, EAN, id del proveedor… **el campo exacto** |
| **Qué manda si hay conflicto** | ¿el origen pisa lo que haya en PrestaShop, o no? |
| **Qué pasa con lo que ya no viene** | ¿se desactiva, se borra, se deja? |

**Historias que casi siempre son estas:**

- **P1** Leer el fichero y enseñar un informe de lo que ENTRARÍA, sin tocar nada.
  *(Esto solo ya evita el 90 % de los desastres.)*
- **P2** Importar lo nuevo.
- **P3** Actualizar lo que ya existía.
- **P4** Tratar las bajas.
- **P5** Dejarlo en un cron.

**Puerta propia del `plan.md`, además de la de siempre:**

- [ ] **Se procesa por lotes**, con reanudación. Un import de 40.000 filas no cabe en una
      petición: se hace por tandas guardando por dónde iba.
- [ ] **Modo simulación** que no escribe nada. Es la P1 y no es opcional.
- [ ] **Anti-duplicado que aguante concurrencia**: `SELECT` + `INSERT` con try/catch o un
      guardián estático, **nunca** `INSERT IGNORE` con `Affected_Rows()`.
- [ ] **Nada de acciones hacia fuera** (correos, WhatsApp, webhooks) disparadas por filas
      antiguas. Si el import toca pedidos o clientes: seed previo y kill switch.
- [ ] **Codificación y separadores** del fichero comprobados con el fichero de verdad, no
      con uno de ejemplo.
- [ ] **Las cabeceras del CSV no se acentúan jamás**: son un contrato de datos.
- [ ] **Registro por fila** de lo que se hizo y por qué se saltó, en `logs/`.

**Al converger:** pasar el fichero REAL del cliente en modo simulación y comparar el
informe con lo que espera el cliente, antes de escribir ni una fila.

---

## 4. Migración y actualización de tiendas

Subir la tienda de un cliente de 1.7 a 8.2, o de 8.2 a 9.1, a partir de un ZIP y un SQL.
Skill de apoyo: **`prestashop-upgrade-tiendas`**.

**En el `spec.md`:**

| | |
|---|---|
| **De qué versión a cuál** | y si es un salto o dos (1.7 → 8.2 → 9.1 son dos trabajos) |
| **Qué hay instalado** | módulos de terceros, tema de pago, overrides |
| **Qué NO puede dejar de funcionar** | el checkout, los pagos, las facturas, el envío |
| **Cuánto puede estar parada** | y si hay ventana de mantenimiento |
| **Quién sube a producción** | y con qué se vuelve atrás si sale mal |

**Historias:**

- **P1** La tienda del cliente monta y arranca en local, tal cual está. *(Sin esto no hay
  nada que migrar.)*
- **P2** Actualiza a la versión destino y el back-office abre.
- **P3** El front funciona: portada, categoría, ficha, carrito y checkout.
- **P4** Los módulos de terceros y los overrides, arreglados uno a uno.
- **P5** El paquete para producción, con la ruta exacta de cada fichero.

**Puerta propia:**

- [ ] **Capturas ANTES**, con la tienda vieja funcionando. Sin el antes no hay forma de
      demostrar qué se rompió y qué ya estaba roto.
- [ ] **Copia del SQL original intacta**, aparte y sin tocar.
- [ ] **Un documento por salto de versión**, no uno para los dos.
- [ ] **Lo que la actualización deja roto siempre**: `module_shop`, los hooks y las
      migraciones de cada módulo. Se comprueba, no se supone.
- [ ] **Páginas en blanco con HTTP 200**: son 39 bytes y no dan error. Se buscan a mano.
- [ ] **Nada de tocar producción** hasta que el paquete esté probado en local.
- [ ] Preguntar antes de montar tiendas en local: consume MariaDB y varios PHP.

**Al converger:** capturas DESPUÉS, en las mismas pantallas que las de antes, y el informe
comparando las dos.

---

## 5. Herramientas de escritorio para Windows

PSUpgrade, PSMonitor. C# sobre .NET, un `.exe` autocontenido.

**En el `spec.md`:** qué pantallas hay, qué se puede pulsar en cada una, y **qué NO debe
tocar nunca** (en PSMonitor: las sesiones de Claude y los servidores MCP; ponerlo por
escrito evita que un botón se lleve por delante el trabajo de alguien).

**Puerta propia:**

- [ ] **Un solo `.exe`, autocontenido.** Sin instalador y sin pedir runtime.
- [ ] **Nada de navegador embebido, PHP ni Python** para la interfaz.
- [ ] **Lo que vigila no puede molestar a lo vigilado.** Para saber si un servidor está
      vivo se mira la tabla TCP, **no se abre una conexión**: eso mata los servidores
      embebidos de PHP.
- [ ] **Todo botón conectado por delegación**, no con `onclick` sobre algo que se pinta
      después.
- [ ] La ventana se ajusta a la pantalla real: con el escalado al 150 % un portátil de 1920
      deja 1280 útiles.
- [ ] Lo que genere (capturas, informes) va a las carpetas comunes, no al lado del `.exe`.

**Al converger, sin excepción:** abrirla, **pulsar cada botón**, exigir cero errores, y
capturar. Que los endpoints respondan no es haberlo probado.

---

## 6. Módulos y plugins de WooCommerce / WordPress

**En el `spec.md`:** versión de WordPress y de WooCommerce, si es plugin o tema hijo, y si
tiene que convivir con algún otro plugin conocido.

**Puerta propia:** prefijo propio en todo (funciones, opciones, tablas, hooks) ·
`sanitize_*` a la entrada y `esc_*` a la salida · nonces en todo formulario y AJAX ·
nada de escribir fuera de `wp-content/uploads/` · desinstalación limpia.

---

## 7. Integraciones con IA

**Solo aquí** manda la regla de modelos del CLAUDE.md.

**En el `spec.md`:** qué hace el modelo exactamente, qué pasa si la API no contesta, y qué
se le manda (¿datos del cliente? ¿qué se guarda del ida y vuelta?).

**Puerta propia:**

- [ ] Modelos **solo** de la lista permitida; por defecto `gemini-flash-latest`.
- [ ] Campo de texto en la configuración para el modelo, y si no está en blanco, manda ese.
      Lo mismo para el modelo de imágenes.
- [ ] Endpoint `https://generativelanguage.googleapis.com/v1beta/models`.
- [ ] **La clave nunca en el código ni en el front.**
- [ ] Tiempo de espera, reintentos y qué se enseña cuando falla.
- [ ] Coste por llamada estimado y un tope, si el módulo llama solo.

---

## 8. Odoo

**En el `spec.md`:** versión de Odoo, si es módulo propio o herencia de uno estándar, y qué
modelos toca.

**Puerta propia:** herencia antes que copiar · las reglas de seguridad y los `ir.model.access`
desde el principio, no al final · las vistas XML separadas de la lógica · migraciones de
datos con guion propio y reversible · nada de `sudo()` sin motivo escrito.

---

## 9. Cosas que hay que aprenderse de memoria

**El `spec.md` sin tecnología.** Si aparece un nombre de hook, de tabla o de fichero, está
en el sitio equivocado: eso es el `plan.md`.

**La puerta se responde por escrito.** «Sí» o el motivo. Si el motivo no cabe en una frase,
no hay motivo.

**Las historias se entregan de una en una.** Al acabar cada una se para y se dice qué se
puede probar ya. Si no se puede probar nada, la historia estaba mal partida.

**Lo que aparece por el camino se apunta como tarea**, no se hace por lo bajo. Es la única
forma de que al final `tareas.md` se parezca a lo que se hizo de verdad.

**Al converger se compara, no se da por bueno.** Lo que falte se añade como tarea nueva.

**Y las tres preguntas obligatorias del final:** ortografía (`TOTAL: 0`), si está todo
correcto, y si se instala en las tiendas locales.
