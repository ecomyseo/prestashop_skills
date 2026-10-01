# `prestashop-modulo-1-7-a-8`

**Porting a PrestaShop module from 1.7.x to 8.x — where the damage actually comes from.**

Written in Spanish. Derived from diffing the extracted **1.7.8.11 and 8.2.8** cores on disk
and from upgrading real shops with two hundred-odd modules on board.

The headline, because it changes where you start looking:

> **PrestaShop 8 barely breaks anything. What breaks is the PHP that comes with it.**

1.7.8 tops out at **PHP 7.4**; 8.2 runs on **8.1**. The shop's version bump drags a language
version bump along with it, and that is where almost all of the damage lives. A module that
has worked for years can fall over without anyone having touched a single PrestaShop API.

Going from 8.x to 9.x is a different skill —
[`prestashop-modulo-8-a-9`](../prestashop-modulo-8-a-9/) — and there things *do* disappear
wholesale. What changes in 9.2 is in [`prestashop-9-2`](../prestashop-9-2/).

## Install

```bash
cp -r prestashop-modulo-1-7-a-8 ~/.claude/skills/
```

## When the agent should load it

Adapting, auditing or debugging a module written for 1.7.x that has to run on 8.x — or when
a shop that has just been moved to 8.2 serves a 500 with an empty body.

## The numbers, measured

| | 1.7.8.11 | 8.2.8 |
|---|---|---|
| PHP accepted | 7.1.3 – **7.4** | 7.2.5 – 8.1+ |
| Core classes that disappear | — | **8** |
| Public methods that disappear | — | **68** |
| Signatures that change | — | **10** (2 dangerous) |
| Constants no longer defined | — | **5** |
| Hooks | 678 | **896** — 218 arrive, **none leave** |

## What's inside

| Section | What it answers |
|---|---|
| **The jump is PHP, not PrestaShop** | Where dead syntax actually lives: **carrier and payment modules bundle old libraries** (TCPDF, hand-rolled SHA256, XML-RPC clients) and that's what fails. One real shop: 16 syntax errors across 5,179 module files, one of them in the payment gateway. Plus the three failures `php -l` **cannot** see — a non-static method called statically (a warning in PHP 7, a **fatal** in PHP 8), narrowing inherited visibility, and an incompatible override signature — all of which fire *when the class loads*, so a test that only installs comes out green. |
| **What 8.x did remove** | The 8 classes (the big one: **`Attribute` → `ProductAttribute`**), the 68 public methods that matter with their replacements, and the 5 constants — `_PS_TCPDF_PATH_` catches the most modules. And the precision that saves you a pointless rewrite: **`Tools::displayPrice()` still exists in 8.2**; it goes in 9.x. |
| **Ten signatures, two of them dangerous** | Eight only append parameters. **Two remove them from the middle** — `Tools::displayDate` and `Module::getModulesOnDisk` — so calls stay syntactically valid and the arguments silently shift one slot. Measured: **110 call sites** across one batch of shops, 106 of them `displayDate`. Clean `php -l`, HTTP 500 with an empty body, and invisible to both a retired-method detector (the method still exists) and an override-signature detector (nobody declares it). |
| **218 new hooks, none retired** | Nothing to migrate; new places to hook instead, including `actionAfterLoadRoutes` and `actionAdminMenuTabsModifier` — which also explains a whole back-office menu section going missing after an upgrade. |
| **Traps only runtime shows** | The duplicated table prefix that is a time bomb with years of delay (`insert()` adds `_DB_PREFIX_` *again*, and it only fires on the first INSERT after the cache is cleared); `Configuration::getInt()` never returning an int; `include_once` on an `.sql` file, which *prints* it instead of running it. |
| **Auditing at scale** | The order that works: lint with 8.1 **and** 7.4, resolve the inheritance chain before calling a method "removed", confirm every finding against the source, compare signatures **by parameter count, not by name**, and rewrite with PHP's **tokenizer**, never with regular expressions. |
| **Verifying** | Installing is not testing. Check **where the request ends up**, not just its status code. A 500 with an empty body is the signature of a `TypeError` — and in AJAX you won't even see that unless you read the body of the 5xx. |
| **And 1.7.x straight to 9.x** | Not possible, and it's arithmetic: `autoupgrade` boots the shop on the **old** core while the requirements demand the PHP of the **new** one, and `[7.1.3–7.4]` and `[8.1–8.5]` do not overlap. You go through 8.2. |

## What is not here

No customer data: no shop names, domains, credentials or working paths. Every failure is
told through its symptom and its cause, which is the part that transfers.

## Contributing

Found a trap that isn't here? Open an issue or a pull request saying **on which version you
verified it**.

AFL-3.0 · Ecom Experts
