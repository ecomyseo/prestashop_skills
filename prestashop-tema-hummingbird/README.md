# `prestashop-tema-hummingbird`

**Building a Hummingbird child theme for PrestaShop 9.1 / 9.2, and the modules that go with
it.**

Written in Spanish. Derived from building complete child themes — home page, listings, product
page — on **9.1.5** (Hummingbird 2.0.0) and **9.2.0** (2.1.2). Almost none of this is in the
documentation: it comes from a screen that didn't render, or that rendered and then broke when
the combination changed.

## Install

```bash
cp -r prestashop-tema-hummingbird ~/.claude/skills/
```

## When the agent should load it

Building or debugging a Hummingbird child theme, or a module that paints on its home page, its
footer or its product cards.

## What's inside

| Section | What it answers |
|---|---|
| **Installing and enabling** | Why **`prestashop:theme:enable` fails on 9.x** ("Cannot build Language context") and how to enable a theme from PHP instead, why enabling from the console leaves the Symfony container stale (other back-office routes 500 until `var/cache` is cleared **with the server stopped**), and that the official 9.2 installer **renames the `admin` folder at random**. |
| **The child theme** | `parent: hummingbird` with `use_parent_assets`, and how the core loads `assets/css/custom.css` as `theme-custom` with priority 1000. The 2.0.0 vs 2.1.2 difference found in the gallery. |
| **Hummingbird uses `@layer`** | The child's unlayered rules beat the parent's normal rules, but **an unlayered `!important` loses against Bootstrap's `utilities` layer**. Where the parent uses utility classes, override the template — don't fight it with CSS. |
| **Product page** | Everything `core.js` repaints when the combination changes must stay inside `.js-product-container` with its class: `.js-product-prices`, `.js-product-add-to-cart`, `.js-product-details`, `.js-images-container`, `.js-product-variants`, `.js-product-additional-info`. |
| **Listing thumbnails** | `blockwishlist` injects its heart into `.thumbnail-container` inside `.js-product-miniature`. Without that class its JS throws and, with combined JS (CCC), **it kills every theme script after it**. |
| **A broken official es-ES translation in 9.2.0** | `Subcategories for %s` shipped as `Subcategorías de %s%`. On PHP 8 `vsprintf` raises a `ValueError` and **every category with subcategories returns a blank 500** — with untouched Hummingbird too. Fixed from the theme, because the theme's catalogue loads after the core's. |
| **Caches that hide a deployment** | CCC, Smarty not recompiling, and translation catalogues: which three directories to clear after copying files. |
| **The theme ZIP** | `dependencies/modules/<module>/` inside the ZIP, how the theme manager copies and enables them, and why `ThemeManager::install()` fails when the folder already exists. |
| **Modules that go with the theme** | A V2 product-page tab done right (and **never** `'mapped' => false`); `Configuration::updateValue($k, $v, true)` running `purifyHTML()` and storing `&` as `&amp;`; another module's `classes/` not being autoloaded outside its own screens; safe image uploads; the `ps_mainmenu` and `ps_linklist` data formats; and how to detect a visual page builder so you don't overwrite a home page that belongs to it. |
| **Testing it** | The first back-office hit after clearing the cache rebuilds the container and can take minutes — warm it up first. The 9.x `ps-switch` toggles can't be clicked by their `<label for>`. And a save test always carries an `&` and re-reads the database. |

## What is not here

No customer data: no shop names, domains, credentials or working paths.

## Contributing

Found a trap that isn't here? Open an issue or a pull request saying **on which version you
verified it**.

AFL-3.0 · Ecom Experts
