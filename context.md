# Lz Builder — Project Context

Last updated: 2026-07-19 · **v1.7.1** · repo: `github.com/Olammyinc/lz-builder` (branch `main`)

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
templates · 14 AJAX actions + 14 REST routes · WP Ultimo subscription gating · template CPT ·
`readme.txt` · 10 unit tests (`tests/test-class-lz-page-data.php`) · zip ships at ~104KB

The plugin is now under git with build output (`assets/js/build/lz-builder.js`, `.asset.php`)
committed — required, since the plugin ships without a build step. `*.map` and `node_modules/`
are gitignored.

### Commit history

| Commit | What landed |
|---|---|
| `178111a` v1.7.0 | React 18 builder UI (real `src/`, webpack building), 28 field templates, sanitization fixes, postMessage hardening |
| `f90ccea` v1.7.1 | Draft isolation, drag-and-drop fix, overlay fix, security |
| `7f8ab78` v1.7.1 | Follow-up review fixes (empty-draft fallback, REST fatal, preview leak, clone position, null guards, REST perms, post_id checks) |
| `030c063` v1.7.1 | N1-N7: column ordering, per-column drop targets, sort stability, orphan guard, duplicate/row UI, readme, tests |
| `bd487cd` v1.7.1 | Fix duplicate positioning (`$data[]` order), preview updates after delete/dup/row, CSS overflow on module panel |

### Resolved (verified by code review 2026-07-19)

- **Draft isolation.** All mutators default to `'draft'`. `save_layout` is a server-side
  draft→published promotion taking no client payload; `save_draft` is a no-op ack. The client
  loads `'draft'`. `get_layout_data` correctly distinguishes `''` (never saved → fall back to
  published) from `'[]'` (deliberately emptied → stay empty).
- **Drag-and-drop over the iframe.** Document-level `dragstart`/`dragend`, `pointer-events:none`
  on the iframe mid-drag, overlay driven by `isDragging`.
- **Per-column drop targets.** Each `.lz-column` in `builder-preview.php` gets dragover/drop
  handlers that post `lz_column_drop` with `parent_id`. `App.js` listens and calls `add_module` with
  the correct column. Module cards still work as the global drop source via the canvas overlay.
- **Selection overlay.** CSS inlined in `builder-preview.php` so it ships with the element it
  styles, plus `window.scrollY/scrollX` offsets.
- **Module placement.** `find_last_column()` walks `sort_nodes()` output in document order
  (last row's first column), not just global max position.
- **Intra-column order.** `add_module` counts existing sibling modules under the same parent and
  sets `position = sibling_count + 1` — no more `usort` tie-break issues on PHP 7.4.
- **Orphan-node guard.** Row auto-creation triggers on "no columns exist" (`$has_columns` flag),
  not `empty($data)` which missed the case where rows exist but columns don't.
- **Duplicate/row UI.** Duplicate button in Settings tab sidebar. Row layout picker (1 Col, 2
  Cols, 3 Cols, 4 Cols, Left Sidebar, Right Sidebar) in Modules tab.
- **Duplicate positioning.** `duplicate_node` now appends the clone to `$data[]` AFTER setting
  its position to `max sibling position + 1` — the previous `$data[]`-before-set bug meant
  the persisted clone had the original's position.
- **Preview updates after mutations.** All mutating AJAX handlers (`delete_node`, `duplicate_node`,
  `add_row`) now return `layout` HTML via `get_layout_html_safe()`. Sidebar.js calls
  `updatePreview(r.data.layout)` after each so the iframe refreshes immediately.
- **CSS overflow.** `.lz-modules-panel` is a flex column with `min-height: 0`; `.lz-module-list`
  uses `flex: 1` so the Rows section above it doesn't push the module grid past the sidebar edge.
- **`render_content`** returns builder content only, switches on draft/published, and gates
  preview behind `edit_post`.
- **Capability checks.** `check_permissions()` resolves `post_id` then checks
  `current_user_can('edit_post', $post_id)`; `check_basic_permissions()` covers non-post-scoped
  endpoints.
- **REST `render-settings-form`** uses shared `build_settings_form_html()` helper — no more
  undefined-method fatal.
- **Field code / sanitization.** `code` field type uses `wp_kses_post`; compound fields
  recursively apply `sanitize_text_field`.
- **Empty layouts work.** Publish reads draft, promotes any data (including `[]`) to published.
- **Author.** `Ibrahim Olammy` (was `Lz Plugins`).

---

## Open Work — prioritized

### P1 · none (all P1/P2/P3 resolved)

The critical placement, ordering, and UI gaps are closed. Remaining work is all P4-level polish
and Lz Funnels integration (see below).

### P4 · Remaining gaps

- **No translations.** `languages/` exists with a `.gitkeep` but no `.pot`/`.po`/`.mo` files.
  All strings use `lz-builder` text domain and `__()`/`esc_html__()` wrappers — pot generation
  is straightforward but hasn't been done.
- **No move-node UI.** `Sidebar.js` has Delete and Duplicate buttons. `move_node` works
  server-side but has no UI — would need up/down arrows or a drag-to-reorder affordance.
- **No apply_template confirmation.** Applying a template silently replaces the entire draft
  with no "are you sure?" prompt.
- **Save Settings form submit races auto-save.** The 400ms timeout between `doAutoSave()` (120ms
  debounce) and tab switch can lose the save if the server is slow.
- **No WordPress.org `readme.txt`**, not needed for development.

### P4 · Deferred

- **Lz Funnels integration** (see section below).
- **Move-node UI** — nice-to-have, not blocker.

---

## Verification Notes

The fixes were confirmed by **code review, not a live WordPress run.** Before treating these as
closed, exercise in a real install:

- **Drag-and-drop in Chrome *and* Firefox** — iframes have browser-specific drag behavior.
- **Per-column drops** — drag a module card onto the iframe's right column vs left column and
  confirm it lands in the targeted `.lz-column`.
- **Draft→publish round trip** — edit, verify the live page is unchanged, publish, verify it
  updates.
- **Duplicate** — click a module, hit Duplicate, confirm a clone appears in the iframe
  immediately and is persisted after reload.
- **Delete** — delete a module, confirm iframe updates immediately.
- **Row picker** — click a row layout, confirm a new row appears in the iframe.

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

# Zip for upload (no node_modules, src, or dev files)
rm -f ../lz-builder-1.7.1.zip && \
  zip -r ../lz-builder-1.7.1.zip . \
    -x "node_modules/*" "src/" ".gitignore" ".git/*" "AGENTS.md" "CONTEXT.md" "context.md" \
       "package.json" "package-lock.json" "webpack.config.js" "*.map" "assets/js/build/*.map"

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
