# PrestaShop Skills for AI coding agents

**41 agent skills that teach Claude Code, Antigravity, Cursor and other AI coding agents how PrestaShop 1.7, 8, 9 and 9.2 really work** — the hooks that exist in each version, the V2 product page, admin grids, translations, multistore, and the traps that turn "looks right" into "breaks on a real shop".

> 🇪🇸 Skills para que los agentes de IA programen bien en PrestaShop. Algunas están escritas en castellano.

AI agents write plausible PrestaShop code that fails in production: a hook that doesn't exist in 8.2, a Symfony structure where the shop needs the legacy one, translations that never load because the domain is wrong. Each skill here is a `SKILL.md` with the exact APIs and the pitfalls, checked against the real 1.7.8, 8.2, 9.1 and 9.2 sources.

## Install

**Claude Code** — copy the folders you want into `~/.claude/skills/` (all projects) or `.claude/skills/` (one project):

```bash
git clone https://github.com/ecomyseo/prestashop_skills
cp -r prestashop_skills/prestashop-* ~/.claude/skills/
```

**Antigravity / Cursor / others** — point the agent's skills or rules folder to this repository, or reference the `SKILL.md` you need.

## Skills

| Skill | What it covers |
|---|---|
| [`prestashop-9-2`](prestashop-9-2/) | **What changes in 9.2**: the one-page checkout now in core, the six signatures, and the `autoupgrade` traps of the 9.1 → 9.2 jump |
| [`prestashop-modulo-8-a-9`](prestashop-modulo-8-a-9/) | **Porting a module from 8.x to 9.x**: what 9 removed, the traps in the replacements, and auditing at scale without false positives |
| [`prestashop-tema-hummingbird`](prestashop-tema-hummingbird/) | **Hummingbird child themes (9.1 / 9.2)** and the modules that go with them: enabling, CSS layers, product page, ZIP, caches |
| `prestashop-product-form` | Product and combination forms (V2) with Symfony form modifiers · 8.1+ and 9 |
| `prestashop_product_v2_hybrid_tabs` | Custom tabs in the V2 product page (hybrid Symfony + legacy injection) |
| `prestashop-product-extra-tabs` | Product tabs with the direct-injection pattern |
| `prestashop-front-product-tabs` | Extra tabs and content on the front-office product page |
| `prestashop-admin-grid` | Creating and extending Symfony admin grids (8, 9) |
| `prestashop-admin-columns` | Adding custom columns to admin listings |
| `prestashop-order-hooks` | Extending the V2 order view page |
| `prestashop-form-data-providers` | Modifying core form data with data-provider hooks |
| `prestashop-symfony-form` | Custom Symfony forms and configuration pages |
| `prestashop-multistore-form` | Multistore-aware configuration forms |
| `prestashop-controller-tabs` | Symfony controllers and admin tabs |
| `prestashop-module-routes` | Front-office routes and pretty URLs |
| `prestashop-js-routing` | Generating admin links from JavaScript |
| `prestashop_js_events` | Listening to and reacting to core JavaScript events (1.7, 8, 9) |
| `prestashop-translations` | Legacy (MD5) and modern (Symfony/XLIFF) translation systems |
| `prestashop-mail-themes` | Modern email themes |
| `prestashop-template-overrides` | Overriding back-office and front-office templates from a module |
| `prestashop-safe-overrides` | Safe, manually copied overrides that don't clash with the installer |
| `prestashop-doctrine-entities` | Doctrine ORM in modules |
| `prestashop-console-commands` | CLI commands with Symfony Console |
| `prestashop-api-module` | APIs: legacy webservice (8) and API Platform (9) |
| `prestashop-webservice-extend` | New resources for the XML webservice |
| `prestashop_login` | Authenticating employees and customers from code: passkeys, 2FA, PS 9 Symfony Security |
| `prestashop_psmcpserver` | Exposing a module to AI agents through the official PrestaShop MCP server, no Composer |
| `prestashop-intel-mcp` | Indexing modules with a local MCP server and a JSON cache |
| `prestashop-validator` | Local version of the PrestaShop validator: structure, security, standards |
| `prestashop-architect-master` | Reference architecture (Symfony/CQRS) — read for APIs, not as a mandatory structure |
| `prestashop_openrouter_integration` | Calling OpenRouter models from a module |
| `prestashop_omnimind_openrouter_adapter` | OpenRouter adapter for the OmniMind module |
| `prestashop_theme_migration` | Migrating or auditing a theme across 1.7.0 → 8.x → 9.2 |
| `spec-modulo` | Spec-driven development for modules, adapted from GitHub Spec Kit |
| `prestashop-example-modules` | What the official `PrestaShop/example-modules` repo actually shows, cross-checked against the core |

### Spanish carriers

Real endpoints, auth and `ps_configuration` keys as the **official** carrier modules use them
— so a module can reuse the credentials the merchant already entered instead of asking again.

| Skill | What it covers |
|---|---|
| `prestashop-transportistas-espana` | Index of the six: which has an API, of what kind, and which credentials it needs |
| `prestashop-carrier-seur` | SEUR REST + OAuth2 password (module `seur` 2.5.26) |
| `prestashop-carrier-mrw` | MRW SOAP (SAGEC), production and test `.asmx` (`mrwcarrier` 8.0.7, `mrwtracking` 3.8.1) |
| `prestashop-carrier-gls` | GLS Spain SOAP (ASM / asmred), `b2b.asmx`, GUID auth (`glsshipping` 3.7.20) |
| `prestashop-carrier-correos` | Correos REST (P3 / CorreosID) and Correos Express, production and PRE (`correosoficial` 3.0.0) |
| `prestashop-carrier-envialia` | Envialia SOAP (ECOMM / RemObjects), session id in `ROClientIDHeader` (`envialiacarrier` 1.0.10) |

`redsys-master-skills/` is a set of notes on the Redsys gateway (core integration, Bizum,
Apple Pay / Google Pay, secrets) rather than a loadable skill.

## New: PrestaShop 9.2 — three skills

Each folder has its own README with the detail. They're split by **task**, because that's how
an agent picks a skill:

* [**`prestashop-9-2/`**](prestashop-9-2/) — *what changes in 9.2.* It **removes nothing** (0
  methods, constants, routes, hooks or columns gone since 9.1.5). What changes is the
  **one-page checkout now in core** — the page no longer reloads, so a binary payment module
  or a JavaScript-built UI can break in silence —, **six signatures** that append optional
  parameters, and the product conditions. Plus why an override with an old signature **kills
  `autoupgrade` mid-jump**, and when "files on 9.2, database on 9.1.5" is *not* a dead end.
* [**`prestashop-modulo-8-a-9/`**](prestashop-modulo-8-a-9/) — *porting a module from 8.x.*
  Crossing a major version **does** remove things: methods, 25 constants, 21 `vendor/`
  packages (Guzzle throws an `Error`, not an `Exception`). Plus the traps in the replacements
  the core itself suggests, and the audit order that worked over 91 modules.
* [**`prestashop-tema-hummingbird/`**](prestashop-tema-hummingbird/) — *child themes on 9.1 /
  9.2.* Enabling a theme when `prestashop:theme:enable` fails, Hummingbird's CSS `@layer`,
  what `core.js` repaints on the product page, the ZIP with `dependencies`, and the caches
  that hide a deployment.

Upgrade path: **`8.2 → 9.1 → 9.2`**. From 1.7.x straight to 9.x **is not possible**, measured
from both ends. 9.2 needs **PHP 8.1 minimum**, up to 8.5.

## Why this exists

These are the notes I wish my agent had had: every rule comes from a real bug on a real shop (a product form that said "Saved" and saved nothing, a translation file in the wrong folder, a hook that only exists in 9.x).

## Contributing

Found a trap that isn't here? Open an issue or a pull request with the version where you verified it.

## License

AFL-3.0 · Ecom Experts · [gmartos.es](https://gmartos.es)

⭐ If these skills save you a debugging session, a star helps other PrestaShop developers find them.
