# Lz Builder — Project Context

Last updated: 2026-07-18 · **v1.7.1** · repo: `github.com/Olammyinc/lz-builder` (branch `main`)

## Direction (read first)

Lz Builder and **Lz Funnels** are a **first-party pair**, built by the same author to work
together as one system — Lz Builder is the Beaver Builder replacement, Lz Funnels is the
CartFlows replacement. The endgame is that Lz Funnels' funnel modules and step editing run
*inside* Lz Builder, the way CartFlows runs inside Beaver Builder / Elementor.

**Scope for now:** ONLY Lz Builder + Lz Funnels working together. No third-party page builder
integration — no external plugins registering modules, no public builder SDK. That is
deliberately deferred: "later, but not now." Any Beaver Builder / `FLBuilder` shim code still
present is a temporary bridge to **remove**, not to build on.

**Order of work:** Lz Builder's own foundation first. The Lz Funnels integration rides on top
of it, so it comes after the builder core is right.

---

## Current State

**Tech stack:** PHP 7.4+ · React 18 (`@wordpress/element`, built with `@wordpress/scripts`) · SCSS
**Shipped:** 7 modules (row, column, heading, text-editor, button, photo, video) · 28 field
templates · 14 AJAX actions + 14 REST routes · WP Ultimo subscription gating · template CPT

The plugin is now under git with build output (`assets/js/build/lz-builder.js`, `.asset.php`)
committed — required, since the plugin ships without a build step. `*.map` and `node_modules/`
are gitignored.

### Recent history

| Commit | What landed |
|---|---|
| `178111a` v1.7.0 | React 18 builder UI (real `src/`, webpack building), 28 field templates, sanitization fixes, postMessage hardening |
| `f90ccea` v1.7.1 | Draft isolation, drag-and-drop fix, overlay fix, security |
| `7f8ab78` v1.7.1 | Follow-up review fixes (empty-draft fallback, REST fatal, preview leak, clone position, null guards, REST perms, post_id checks) |

### Resolved (verified by code review 2026-07-18)

- **Draft isolation.** All mutators default to `'draft'`. `save_layout` is a server-side
  draft→published promotion taking no client payload; `save_draft` is a no-op ack. The client
  loads `'draft'`. `get_layout_data` correctly distinguishes `''` (never saved → fall back to
  published) from `'[]'` (deliberately emptied → stay empty).
- **Drag-and-drop over the iframe.** Document-level `dragstart`/`dragend`, `pointer-events:none`
  on the iframe mid-drag, overlay driven by `isDragging`. The buggy `relatedTarget` dragleave
  is gone.
- **Selection overlay.** CSS inlined in `builder-preview.php` so it ships with the element it
  styles (it lives inside the iframe, which only loads the frontend stylesheet), plus
  `window.scrollY/scrollX` offsets.
- **`render_content`** returns builder content only (no longer concatenates original post
  content), switches on draft/published, and gates preview behind `edit_post`.
- **Capability checks.** `check_permissions()` resolves `post_id` then checks
  `current_user_can('edit_post', $post_id)`; `check_basic_permissions()` covers non-post-scoped
  endpoints. Closes the hole where any Contributor could overwrite any post's builder data.
- **The old build landmine is gone** — `src/` exists and webpack builds cleanly. `npm run build`
  is safe again.

---

## Open Work — prioritized

### P1 · Module placement is wrong (user-visible)

**`find_last_column()` picks the wrong column.** `includes/class-lz-page-data.php:384` scans for
the globally-highest `position`, but `position` is scoped **per parent** — `add_row` assigns
columns `0..n-1` within their own group. Consequences:

- On a 2-column row (positions `0,1`) every module lands in the **right** column. The left
  column is unreachable.
- With Row A (3 cols `0,1,2`) then Row B (2 cols `0,1`), it resolves to Row A's third column,
  not anything in Row B.

**Fix:** walk in document order (rows sorted by position → column-group → columns sorted by
position), take the **last row**, then its **first column** — which matches user expectation.

### P1 · Per-column drop targets (never built)

The UI still never sends `parent_id`/`position`; there are no drop targets in the preview. This
is the highest-value remaining item — without it, placement is guesswork and the P1 above only
shifts *which* wrong column gets used.

**Fix:** mark each `.lz-column` in `builder-preview.php` with its node id, add `dragover`/`drop`
handlers, postMessage the target column id + index to the parent, and have `Canvas.js` send real
`parent_id`/`position` to `add_module`.

### P2 · Intra-column order is non-deterministic on PHP 7.4

The UI never sends `position`, so every appended module gets `position: 0`. `sort_nodes` uses
`usort` (`class-lz-page-data.php:71`), and **`usort` is not stable before PHP 8.0** — the plugin
header declares `Requires PHP: 7.4`. Module order within a column can reshuffle between renders.

**Fix:** when appending in `add_module`, set `position` to the count of existing sibling modules
in that column.

### P2 · Orphan-node edge case

`add_module` auto-creates a row only when the layout is *completely empty*. If nodes exist but no
columns do, `find_last_column` returns `''`, the module is saved with an empty `parent_id`, and
`get_builder_content` (which only walks rows→groups→columns→modules) renders it nowhere — it
looks silently lost.

**Fix:** guard on "no columns exist," not "data is empty."

### P3 · Implemented-but-unreachable endpoints

`duplicate_node`, `move_node`, and `add_row` all work server-side but have no UI. Needs
duplicate/move controls on the selected node, and a row layout picker.

### P4 · Remaining gaps

- No tests (`tests/` empty), no translations (`languages/` empty).
- Leftover `FLBuilder` shim references from the Beaver Builder lineage — remove, don't extend.
- ~53K files, mostly `node_modules/` — keep an eye on deployment zip size.

---

## Verification Notes

The v1.7.1 fixes were confirmed by **code review, not a live WordPress run.** Before treating
these as closed, exercise in a real install:

- **Drag-and-drop in Chrome *and* Firefox** — the fix reads correctly but is browser-behavior
  dependent (iframes capture drag events into their own document).
- **Draft→publish round trip** — edit, verify the live page is unchanged, publish, verify it
  updates.
- **Placement** — add modules to a multi-column row and confirm they land where clicked (expect
  failures until the two P1 items are done).

---

## Lz Funnels Integration (LATER — after the builder core is solid)

Target end state, first-party only:

1. **Native module registration** — Lz Funnels' modules (Checkout Form, Next Step, Optin Form,
   Order Details) register with Lz Builder's `LZ_Module_Registry`, not
   `FLBuilder::register_module()`. A first-party seam between two plugins, not a public SDK.
2. **Native detection** — Lz Funnels currently only checks `class_exists('FLBuilder')`. It must
   detect Lz Builder natively (e.g. `class_exists('\LzBuilder\LZ_Builder')`).
3. **Step editing opens Lz Builder** — editing an `lz_funnels_step` should launch Lz Builder for
   that step (arbitrary post types are already supported via settings).
4. **Subscription-gate wiring** — `LZ_Subscription_Gate` should also govern which Lz Funnels
   modules (checkout, upsells) are available per WP Ultimo plan.

---

## Build & Repo

```bash
cd /home/olammy/projects/lz-builder
npm run build       # JS (wp-scripts) + SCSS — safe again as of v1.7.0
npm run build:css   # SCSS only
npm run start       # watch mode

git log --oneline -10
```

**Always commit the rebuilt `assets/js/build/` output** alongside `src/` changes — the plugin
ships without a build step, so a stale bundle means the fix isn't actually live.

### Adding a module
1. `includes/modules/{slug}/class-{slug}.php`, class `\LzBuilder\Modules\{PascalCase}` extends
   `LZ_Module_Base`
2. Implement `get_settings_form()` and `render()`; optionally `get_css()`
3. `includes/modules/{slug}/includes/frontend.php`
4. `assets/scss/modules/_{slug}.scss`, imported in `lz-builder-frontend.scss`
5. Registry auto-discovers on `init()` — no manual registration
6. Assign plan in admin: Lz Builder → Module Plans
