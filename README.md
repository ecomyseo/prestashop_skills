# PrestaShop Skills for AI coding agents

**29 agent skills that teach Claude Code, Antigravity, Cursor and other AI coding agents how PrestaShop 1.7, 8 and 9 really work** — the hooks that exist in each version, the V2 product page, admin grids, translations, multistore, and the traps that turn "looks right" into "breaks on a real shop".

> 🇪🇸 Skills para que los agentes de IA programen bien en PrestaShop. Algunas están escritas en castellano.

AI agents write plausible PrestaShop code that fails in production: a hook that doesn't exist in 8.2, a Symfony structure where the shop needs the legacy one, translations that never load because the domain is wrong. Each skill here is a `SKILL.md` with the exact APIs and the pitfalls, checked against the real 1.7.8, 8.2 and 9.1 sources.

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

## Why this exists

These are the notes I wish my agent had had: every rule comes from a real bug on a real shop (a product form that said "Saved" and saved nothing, a translation file in the wrong folder, a hook that only exists in 9.x).

## Contributing

Found a trap that isn't here? Open an issue or a pull request with the version where you verified it.

## License

AFL-3.0 · Ecom Experts · [gmartos.es](https://gmartos.es)

⭐ If these skills save you a debugging session, a star helps other PrestaShop developers find them.
