# `prestashop-9-2`

**What actually changes in PrestaShop 9.2 compared to 9.1, and what you have to touch in a
module so it keeps working.**

Written in Spanish. Derived from diffing the extracted **9.1.5 and 9.2.0** cores on disk and
from upgrading real shops all the way to 9.2 — not from the release notes. Where something
comes from the devdocs, the document says so.

Two related jobs live in their own skills, because they are different tasks:
[`prestashop-modulo-8-a-9`](../prestashop-modulo-8-a-9/) for porting a module from 8.x, and
[`prestashop-tema-hummingbird`](../prestashop-tema-hummingbird/) for child themes.

## Install

```bash
cp -r prestashop-9-2 ~/.claude/skills/
```

## When the agent should load it

Upgrading a shop to 9.2 · adapting a payment or carrier module to the one-page checkout ·
using the extra properties · something in the checkout stopped working after upgrading.

## What's inside

| Section | What it answers |
|---|---|
| **9.2 removes nothing** | Measured against 9.1.5: 0 methods, 0 constants, 0 routes, 0 hooks, 0 columns gone. Three things change instead. |
| **The one-page checkout, now in core** | `ps_onepagecheckout` ships in the package and **the page no longer reloads**: carriers and payment methods re-render over AJAX. What happens to a **binary** payment module that never calls `submitBeforePayment()` — the customer is sent off to pay before anything is saved — and to any JavaScript-built UI (a pickup-point map, a shipping calculator) that doesn't listen to `opcCarriersUpdated` / `opcPaymentMethodsUpdated`. The five event names are read from the module's own code, not from the docs. Plus how to tell whether a module is a carrier module: **not** by `getOrderShippingCost`. |
| **Six signatures that change** | All append optional parameters, so calls keep working — an **override** with the old signature does not. It is a fatal **when the class is linked**, so `php -l` stays clean. Why it **kills `autoupgrade` mid-jump** (the upgrade boots the shop with the *new* core to migrate the database), and how to fix it beforehand so the declaration is valid on both cores: append the new parameters as optional with **literal** defaults, and compare parameters **by count, not by name**. |
| **Files on 9.2 with the database still on 9.1.5** | When that is *not* a dead end. `autoupgrade` reads the source version from the **files** and answers *Versions are identical* — but only when it finds no state. While `state_update.var` survives, `currentVersion` still says 9.1.5 and you can resume with `--action`. There's a table to tell a half-finished state from a stale one. |
| **Product conditions** | `open_box`, `damaged` and `new_with_defects` join the three that existed. A module matching the condition against a closed list silently drops products. |
| **46 new hooks** | None mandatory. The 16 `*ExtraPropertyDefinition*` ones give you extra fields on products, combinations, customers and orders **with no table of your own** — marked `@experimental` in the core itself. |
| **10 new tables** | `extra_property_definition` and eight B2B tables, plus the new columns. Improved B2B sits behind the `improved_b2b` flag, in beta and off. |
| **Odds and ends** | Per-version module services (`services-9.2.yml`), JSON-LD passed through `actionFrontControllerSetVariables`, the `install/` folder deleting itself, and the new console commands — `prestashop:employee:create-admin` among them. |

## Upgrade path

```
8.2  ->  9.1  ->  9.2
```

Three jumps from 1.7.8.11, two from 8.2.8, one from 9.1.5. Going from **1.7.x straight to 9.x
is not possible**, and it was measured from both ends: on PHP 7.4 `update:check-requirements`
rejects the 9.x target, and on PHP 8.1 the `vendor/doctrine` shipped with 1.7.8 doesn't
compile, so `update:start` never boots the shop to migrate it.

9.2 needs **PHP 8.1 minimum** and accepts up to 8.5.

## What is not here

* **Private tooling.** Some of these checks are automated in scripts that aren't published.
  Where they're mentioned, the document says **what they check** — the reimplementable part —
  and the last section lists them.
* **Customer data.** No shop names, domains, credentials or working paths. Every failure is
  told through its symptom and its cause, which is the part that transfers.

## Contributing

Found a 9.2 trap that isn't here? Open an issue or a pull request saying **on which version
you verified it**. A rule without that gets re-argued every six months.

AFL-3.0 · Ecom Experts
