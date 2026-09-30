# `prestashop-modulo-8-a-9`

**Porting a PrestaShop module from 8.x to 9.x: what 9 really removed, and how to find it
without drowning in false positives.**

Written in Spanish. Derived from diffing the extracted **8.2.8 and 9.2.0** cores on disk and
from auditing **91 real modules**: **1 in 3 didn't pass**, and almost always for the same
handful of reasons.

Going from 9.1 to 9.2 removes nothing — that's a different skill
([`prestashop-9-2`](../prestashop-9-2/)). Crossing a **major** version does remove things,
and that's what this one is about.

## Install

```bash
cp -r prestashop-modulo-8-a-9 ~/.claude/skills/
```

## When the agent should load it

Adapting, auditing or debugging a module written for 8.x that has to run on 9.x.

## What's inside

| Section | What it answers |
|---|---|
| **What 9.x removed** | The methods that are gone, with the replacement for each: `Tools::displayPrice()`, `Tools::link_rewrite()`, `Tools::displayNumber()`, `Tools::getBrightness()` (now an instance call), `$this->l()` in admin controllers, `$this->ajaxDie()`, `$this->addJquery()`… Three of them break **when the screen opens**, not on install, so a test that only installs comes out green. |
| **The traps in the replacements** | `Tools::stripslashes()` **was not** PHP's `stripslashes()` — it returned the string untouched, so following the deprecation notice *changes behaviour*. `Configuration::getInt()` didn't return an int. The rule: a replacement the core names is not an equivalent replacement until you've compared both bodies. |
| **Constants that are no longer defined** | 25 `define()`s from 8.2.8 are gone. `if (!defined('_CAN_LOAD_FILES_')) exit;` as a guard makes the module **bail out while loading** and the page goes blank. And why a module can work by accident on an **upgraded** shop and die on a **freshly installed** 9.2. |
| **Libraries 8.x shipped in `vendor/` and 9.x doesn't** | 21 packages gone. The painful one is **Guzzle**: `new \GuzzleHttp\Client` on 9.x is an `Error`, **not an `Exception`**, so the surrounding `catch (\Exception $e)` doesn't catch it and the page 500s. Also why `symfony/symfony` splitting into per-component packages needs prefix matching, not key matching. |
| **Three core behaviours that break installs silently** | How `ps_versions_compliancy` is padded before comparing (`'9.0'` becomes `9.0.999.999` and **blocks 9.2**), a hook registered without its method aborting the install halfway when `_PS_MODE_DEV_` is on, and an override of a class 9.x no longer has being **ignored without a word**. |
| **Module overrides don't live where you think** | Inside a file under the module's `override/`, `__DIR__` is the *shop's* `/override`, and a top-of-file `require_once` is lost because only the methods get merged. |
| **Auditing at scale** | The order that worked over 91 modules: resolve the **inheritance chain** before calling a method "removed" (without it, half the findings were false), confirm every finding against the source, lint with PHP **8.1** (9.x's minimum, not 7.4), and the five template false-positives that produced most of the noise. |
| **Rewriting** | With PHP's **tokenizer**, never with regular expressions. Replacements longer than one line go to the module's own compatibility file, carrying the **8.2.8 body**, tested against a 9.2 core **actually booted** (Symfony kernel loaded), not against stubs. |
| **Verifying on a real 9.2** | What a naive test calls green: look at **where the request ends up**, not just the status code; the page text can contain the error phrase; a slow screen is not a broken screen; and an ionCube-encrypted module for another PHP version brings down the install of *other* modules just by sitting in `modules/`. |

Plus the failures that show up while doing this and **aren't 9.2's fault** — they break on any
version: `include_once` on an `install.sql` (which *prints* the file instead of running it),
tables created only when the configuration screen opens, `pSQL()` wrapped around a *fragment*
of SQL, and `hydrate()` redefined with less visibility.

## What is not here

No customer data: no shop names, domains, credentials or working paths. Every failure is told
through its symptom and its cause, which is the part that transfers.

## Contributing

Found a trap that isn't here? Open an issue or a pull request saying **on which version you
verified it**.

AFL-3.0 · Ecom Experts
