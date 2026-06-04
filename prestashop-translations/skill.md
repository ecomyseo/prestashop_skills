#Prestashop Translations Expert
---
name: prestashop-translations
description: >
  Protocolo estricto para gestionar traducciones en PrestaShop 1.7, 8 y 9.
  Cubre tanto el sistema Legacy (hashes MD5) como el Moderno (Symfony).
---

# Skill: PrestaShop Translations Expert

Este skill define cómo implementar y solucionar problemas de traducción en módulos de PrestaShop, asegurando compatibilidad total con el "Modern Translation System".

## 1. El Dilema de los Dos Sistemas

PrestaShop convive con dos sistemas. El uso de uno u otro depende de la función `isUsingNewTranslationSystem()`.

### A. Sistema Moderno (Symfony) - RECOMENDADO
Activado si `isUsingNewTranslationSystem()` devuelve `true`.
- **Dominio**: `Modules.Nombremodulo.Seccion` (ej. `Modules.Ecomcalculateshipping.Shop`).
- **PHP**: `$this->trans('Text', [], 'Modules.Nombremodulo.Shop');`
- **Smarty**: `{l s='Text' d='Modules.Nombremodulo.Shop'}`
- **Ficheros**: Se guardan en `translations/` pero el scanner del BackOffice es quien manda. Los archivos `.php` de traducción NO usan hashes MD5, sino la cadena literal.

### B. Sistema Legacy (Arcaico)
Activado si `isUsingNewTranslationSystem()` no existe o devuelve `false`.
- **PHP**: `$this->l('Text');`
- **Smarty**: `{l s='Text' mod='nombremodulo'}`
- **Ficheros**: `translations/es.php` con hashes MD5: `$_MODULE['<{modulo}prestashop>fichero_hash'] = 'Traducción';`

---

## 2. Reglas de Oro para el Sistema Moderno

Si `isUsingNewTranslationSystem` es `true`, sigue estas reglas para no "romper" las traducciones:

1. **Dominio PascalCase**: Aunque la carpeta del módulo sea `ecom_calculateshipping`, el dominio en la función debe ser `Modules.Ecomcalculateshipping.Shop`.
2. **Cadenas Literales**: El escáner de PrestaShop NO lee variables. 
   - ❌ MAL: `$this->trans($var, [], $dom);`
   - ✅ BIEN: `$this->trans('Calculate Shipping', [], 'Modules.Ecomcalculateshipping.Shop');`
3. **Visibilidad**: Para que una cadena aparezca en el traductor del BackOffice, debe estar escrita en un archivo `.php` o `.tpl` que sea escaneable.
4. **Traductor en Controladores**: En un `ModuleFrontController`, se accede mediante `$this->module->getTranslator()->trans(...)` o simplemente `$this->trans(...)` si hereda correctamente.

---

## 3. Implementación con Archivos XLIFF (PrestaShop 8/9)

Para que un módulo lleve traducciones nativas instalables en el sistema moderno sin configurar cada vez el Backoffice, se debe usar el formato XLIFF:

1.  **Ruta del archivo**: `translations/{iso-code}/Modules{ModuleName}{Section}.{iso-code}.xlf`
    *   Ejemplo: `translations/es-ES/ModulesEcomcalculateshippingShop.es-ES.xlf`
2.  **Convención de Dominio**: El dominio en el código debe ser exacto (PascalCase). Si el módulo es `ecom_calculateshipping`, el dominio es `Modules.Ecomcalculateshipping.Shop`.
3.  **Estructura del XLIFF**:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
  <file source-language="en" target-language="es-ES" datatype="plaintext" original="ModulesEcomcalculateshippingShop">
    <body>
      <trans-unit id="unique_hash_or_id">
        <source>Phrase in English</source>
        <target>Frase en Español</target>
      </trans-unit>
    </body>
  </file>
</xliff>
```

---

## 4. Checklist de Éxito

1.  **Visibilidad**: ¿Están todas las frases listadas en el `__construct` o en un hook del archivo principal del módulo usando `$this->trans('Literal', [], 'Dominio')`? Esto es vital para el escáner.
2.  **Caché**: ¿Se ha borrado la caché de rendimiento? Symfony no lee nuevos archivos XLIFF en caliente.
3.  **Localización**: ¿El idioma de la tienda coincide con el `{iso-code}` de la carpeta de traducciones? (ej. `es-ES` para España).
4.  **AJAX**: ¿Los controladores AJAX invocan el traductor correctamente desde el objeto del módulo?
    *   `$this->module->getTranslator()->trans('Text', [], 'Domain')`

---

## 5. Troubleshooting (Checklist)

1. **¿Faltan expresiones en el panel?**
   - Verifica que las cadenas están escritas literalmente en el PHP/TPL con el dominio correcto.
   - Limpia la caché (`/var/cache/`).
2. **¿Se muestra en inglés a pesar de estar traducido?**
   - Revisa que el dominio coincida letra por letra (Mayúsculas/Minúsculas importan).
   - Asegúrate de que `isUsingNewTranslationSystem()` devuelve `true`.
- Los controladores AJAX a menudo pierden el contexto del traductor. Asegúrate de pasar el `id_lang` correctamente o usar `$this->module->getTranslator()`.

---

## ⚠️ NOTA CRÍTICA DE FUNCIONALIDAD (PHP 7.4 & AJAX)
Para que el sistema de traducciones (especialmente vía AJAX) funcione sin bloqueos en servidores con PHP 7.4 o configuraciones restrictivas:
1. **AJAX Bypass**: Los controladores que sirven traducciones vía AJAX **DEBEN** sobrescribir `checkAccess()` y `checkToken()` para retornar `true`, y declarar `public $ajax = true;`. Esto evita el error "Action not allowed".
2. **Legacy Class Access**: Usar `get_class($obj)` en lugar de `$obj::class`.
3. **Contexto Robusto**: Usar siempre `\Context::getContext()` (con backslash inicial) para asegurar la carga del idioma correcto en el traductor.
