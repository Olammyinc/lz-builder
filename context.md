# Lz Builder — Project Context

Last updated: 2026-07-17

## Direction (read first)

Lz Builder and **Lz Funnels** are a **first-party pair**, built by the same author to work
together as one system — Lz Builder is the Beaver Builder replacement, Lz Funnels is the
CartFlows replacement. The endgame is that Lz Funnels' funnel modules and step editing run
*inside* Lz Builder, the way CartFlows runs inside Beaver Builder / Elementor.

**Scope for now:** ONLY Lz Builder + Lz Funnels working together. No third-party page
builder integration (no external plugins registering modules, no public builder SDK). That
is deliberately deferred — "later, but not now." Any Beaver Builder / `FLBuilder` shim code
still in either plugin is a temporary bridge, not the target architecture.

**Order of work:** Get Lz Builder's own foundation solid first. The Lz Funnels integration
rides on top of it, so it comes after the builder core is right.

---

## Overview

Lz Builder is a frontend drag-and-drop page builder for WordPress — an agency-quality,
in-house Beaver Builder replacement. It lives at `/home/olammy/projects/lz-builder/`.

**Version:** 1.6.7
**Author:** Ibrahim Olammy
**Tech stack:** PHP 7.4+ | **vanilla JS builder UI** | SCSS
**Not a git repo** (as of 2026-07-17) — no version control in the working dir.

---

## Frontend UI — IMPORTANT correction

Older docs (including `CONTEXT.md`) describe the builder UI as an "incomplete React 18 SPA."
That is **wrong** (verified by reading the code 2026-07-17):

- The builder UI is **hand-written vanilla JS**, not React. The file
  `assets/js/build/lz-builder.js` (~447 lines) is human-readable source edited directly in
  the build folder — it is NOT compiled/minified output. There are no React dependencies
  beyond `@wordpress/element`.
- **Build landmine:** `webpack.config.js` sets its entry to `./src/index.js`, but **there is
  no `src/` directory**. So `npm run build` (which runs `wp-scripts build`) will FAIL on the
  JS step and could clobber the hand-maintained `lz-builder.js`. Only `npm run build:css`
  (sass) is currently safe to run. Before running the full build, either move the real JS
  into a proper `src/` and let webpack own it, or drop webpack for JS and keep hand-editing.
- The vanilla-JS builder is genuinely **functional**, not a stub.

### What the builder UI currently does
- Toolbar: Save Draft / Publish / Exit
- Sidebar tabs: Modules / Templates / Settings
- Iframe live preview of the page
- Add a module by dragging a card to the canvas OR clicking it
- Click a module in the preview → its settings panel opens (via postMessage), with a
  debounced auto-save that re-renders just that module in the preview
- Templates tab: list + apply
- Color fields, button-group toggles, notices

### Known builder-UI gaps (the real work)
- **Drop zone is a single global overlay** — you cannot drop a module into a *specific*
  column. Every drop goes to the default location.
- **No add-row / layout-picker UI** — the `add_row` AJAX endpoint exists but nothing in the
  UI calls it; rows are only auto-created when a module needs one.
- **No delete / duplicate / move / reorder controls** on modules in the preview — those AJAX
  endpoints exist server-side but have no UI driving them.
- **Save Draft and Publish call the same `save_layout` action** — Publish may not actually
  promote draft → published. Needs verifying/fixing.

---

## What's Built (backend — from CONTEXT.md, treated as reliable)

### Core Engine
- Frontend drag-and-drop UI (vanilla JS in an iframe)
- Node system: row → column → module, stored as a flat node array
- Flat node array in `_lz_builder_data` (published) / `_lz_builder_draft` (draft) post meta
- CSS accumulation pipeline + cached file output in uploads (3 breakpoints)
- AJAX (14 actions) + REST (14 routes), undo/redo, keyboard shortcuts

### 7 Modules
Row, Column, Heading, Text Editor, Button, Photo, Video.

### Settings System
- Form → Tab → Section → Field hierarchy
- 16 field templates implemented (29 types registered — some lack templates)
- Responsive breakpoints per field

### Admin
- `LZ_Admin`: settings page, admin bar menu, Gutenberg/Classic editor integration, user access
- `LZ_Activation`: CPT/template registration, cache dir, rewrite flush
- `LZ_Template_CPT`: `lz_template` post type with apply-to-post

### Subscription Gating
- `LZ_Subscription_Gate`: WP Ultimo / Ultimate Multisite integration, module-level gating via
  `required_plan`, client-side upsell UI, graceful degradation when WP Ultimo is absent

### Architecture
- **Autoloader:** PSR-4 `LzBuilder\` namespace → `includes/`
- **Modules:** `includes/modules/{slug}/class-{slug}.php`, class `\LzBuilder\Modules\{PascalCaseSlug}`,
  frontend template at `includes/modules/{slug}/includes/frontend.php`
- **CSS:** SCSS → `assets/css/build/`
- **JS:** hand-edited `assets/js/build/lz-builder.js` (see correction above)

---

## Lz Funnels Integration (LATER — after builder core is solid)

Target end state (first-party only):

1. **Native module registration** — Lz Funnels' funnel modules (Checkout Form, Next Step,
   Optin Form, Order Details, etc.) register with Lz Builder's `LZ_Module_Registry`, NOT with
   Beaver Builder's `FLBuilder::register_module()`. This is a first-party seam between the two
   plugins, not a public third-party SDK.
2. **Native detection** — Lz Funnels currently only checks `class_exists('FLBuilder')`. It must
   detect Lz Builder natively (e.g. `class_exists('\\LzBuilder\\LZ_Builder')`). The FLBuilder
   check is a leftover bridge to remove once native support lands.
3. **Step editing opens Lz Builder** — editing an `lz_funnels_step` should launch Lz Builder
   for that step (Lz Builder already supports arbitrary post types via its settings).
4. **Subscription-gate wiring** — `LZ_Subscription_Gate` should also govern which Lz Funnels
   modules (checkout, upsells, etc.) are available per WP Ultimo plan.

---

## Build Commands

```bash
cd /home/olammy/projects/lz-builder
npm run build:css   # SAFE — sass only
# npm run build     # UNSAFE right now — JS entry points at missing src/index.js
```

---

## Known Issues

- **Build is broken for JS** (webpack entry → nonexistent `src/index.js`). See correction above.
- **Not under version control** — no git repo in the working dir; no safety net for edits.
- ~53K files, mostly `node_modules/` — not ideal for deployment zips.
- Leftover `FLBuilder` shim references from the Beaver Builder lineage (to be removed once
  native Lz Funnels integration lands).
- Builder-UI editing gaps listed in the frontend section above.
