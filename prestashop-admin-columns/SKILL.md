---
name: prestashop-admin-columns
description: Skill para la gestion y adicion de columnas personalizadas en listados de administracion de PrestaShop 8/9 usando Symfony Grid.
---

# PRESTASHOP ADMIN COLUMNS SKILL (PS 8 / PS 9)

Este skill proporciona los patrones para extender los listados de administracion (Grids) de PrestaShop usando el sistema Symfony Grid.

> **NOTA**: En PS 8+ todos los listados principales (Pedidos, Clientes, Productos) usan Symfony Grid. Los hooks legacy (`actionAdminOrdersListingFieldsModifier`) NO aplican para PS 8/9.

## 1. HOOKS POR ENTIDAD

| Entidad | Hook Query Builder | Hook Definition |
| :--- | :--- | :--- |
| **Orders** | `actionOrderGridQueryBuilderModifier` | `actionOrderGridDefinitionModifier` |
| **Customers** | `actionCustomerGridQueryBuilderModifier` | `actionCustomerGridDefinitionModifier` |
| **Products** | `actionProductGridQueryBuilderModifier` | `actionProductGridDefinitionModifier` |
| **Carts** | `actionCartGridQueryBuilderModifier` | `actionCartGridDefinitionModifier` |

Patron general: `action{GridId}GridQueryBuilderModifier` y `action{GridId}GridDefinitionModifier`.

---

## 2. IMPLEMENTACION (SYMFONY GRID)

Requiere 2 o 3 hooks coordinados + registro en `install()`.

### 2.0 Registro de Hooks
```php
public function install()
{
    return parent::install()
        && $this->registerHook('actionOrderGridQueryBuilderModifier')
        && $this->registerHook('actionOrderGridDefinitionModifier');
}
```

### 2.1 Modificar la Query (QueryBuilder)

```php
public function hookActionOrderGridQueryBuilderModifier(array $params): void
{
    /** @var \PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteria $searchCriteria */
    $searchCriteria = $params['search_criteria'];

    /** @var \Doctrine\DBAL\Query\QueryBuilder $searchQueryBuilder */
    $searchQueryBuilder = $params['search_query_builder'];

    $searchQueryBuilder->addSelect('m.my_value');
    $searchQueryBuilder->leftJoin(
        'o', // Alias principal (o=Orders, c=Customers, p=Products)
        _DB_PREFIX_ . 'my_table',
        'm',
        'm.id_order = o.id_order'
    );

    // Tambien para el count
    $countQueryBuilder = $params['count_query_builder'];
    $countQueryBuilder->leftJoin('o', _DB_PREFIX_ . 'my_table', 'm', 'm.id_order = o.id_order');
}
```

### 2.2 Modificar la Definicion (Columns)
Se añade la columna visual al objeto GridDefinition.

```php
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;

public function hookActionOrderGridDefinitionModifier(array $params): void
{
    /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
    $definition = $params['definition'];

    $definition->getColumns()->addAfter(
        'reference', // ID de la columna despues de la cual se inserta
        (new DataColumn('my_value'))
            ->setName($this->trans('Mi Valor', [], 'Modules.Mymodule.Admin'))
            ->setOptions([
                'field' => 'my_value',
            ])
    );
}
```

> **NAMESPACE CORRECTO**: `PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn` (sin `Definition\` intermedio).

### 2.3 Filtros y Ordenacion

```php
// Dentro de hookActionOrderGridQueryBuilderModifier
foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
    if ($filterName === 'my_value') {
        $searchQueryBuilder->andHaving('m.my_value LIKE :my_value');
        $searchQueryBuilder->setParameter('my_value', '%' . pSQL($filterValue) . '%');
    }
}

if ($searchCriteria->getOrderBy() === 'my_value') {
    $searchQueryBuilder->orderBy('m.my_value', $searchCriteria->getOrderWay());
}
```

### 2.4 Columnas con HTML personalizado
Para mostrar HTML, usar `HtmlColumn` o una plantilla Twig:

```php
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\HtmlColumn;

$definition->getColumns()->addAfter(
    'reference',
    (new HtmlColumn('my_html_field'))
        ->setName($this->trans('Mi Campo', [], 'Modules.Mymodule.Admin'))
        ->setOptions([
            'field' => 'my_html_field',
        ])
);
```

Para mayor control con plantilla Twig:
```php
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;

// Crear columna con template Twig personalizado
(new DataColumn('my_custom_field'))
    ->setOptions([
        'field' => 'my_custom_field',
    ])
```

---

## 3. ENTIDADES ESPECIFICAS (CHEAT SHEET)

### PRODUCTOS (AdminProducts)
```php
// Query Builder
$searchQueryBuilder->leftJoin(
    'p',
    _DB_PREFIX_ . 'product_lang',
    'pl',
    'pl.id_product = p.id_product AND pl.id_lang = ' . (int) \Context::getContext()->language->id
);

// Definition
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;

$definition->getColumns()->addAfter(
    'name',
    (new DataColumn('my_field'))
        ->setName($this->trans('Label', [], 'Modules.Mymodule.Admin'))
        ->setOptions(['field' => 'my_field'])
);
```

---

## 4. REGLAS Y BUENAS PRACTICAS

1. **Strict Types**: Siempre usar `declare(strict_types=1);` al principio del archivo.
2. **Namespace Global**: El archivo principal `.php` del modulo **NO** debe tener namespace.
3. **`ClassName::class`**: Usar libremente (valido desde PHP 5.5). Solo evitar `$obj::class` si se necesita compatibilidad con PHP < 8.0 (PS 8.0).
4. **Proteccion SQL**: Usar siempre `pSQL()` para strings y cast `(int)` para IDs.
5. **Multitienda**: Siempre añadir `Shop::addSqlRestriction()` o filtrar por `id_shop`.
6. **Escapado HTML**: En columnas con contenido del usuario, usar `HtmlColumn` con datos previamente escapados o `DataColumn` (escapa automaticamente).
7. **Traducciones**: Usar `$this->trans('text', [], 'Modules.Mymodule.Admin')`, NUNCA `$this->l()`.

---

## 5. TIPS DE RENDIMIENTO

- **GROUP BY**: Evitar duplicados en listados al usar JOINs 1:N.
- **Indexacion**: Asegurarse de que las columnas usadas en el JOIN de tablas personalizadas tengan indices.
- **Cache**: Si la obtencion del dato es costosa, considerar guardarlo en una tabla de soporte o usar `Configuration` si es global.
