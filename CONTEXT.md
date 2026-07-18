# Lz Builder — Complete Project Context

## Overview

**Lz Builder** is a WordPress frontend drag-and-drop page builder plugin (v1.6.7) designed for subscription-based WooCommerce/WaaS marketplaces. It integrates tightly with **WP Ultimo (v2.3.2) / Ultimate Multisite (v2.13.1)** to gate builder modules behind membership plans. The plugin is developed by **Lz Plugins** and is a sibling of **Lz Funnels** (checkout funnel plugin at `/home/olammy/projects/lz-funnels`).

**Location**: `/home/olammy/projects/lz-builder/`  
**PHP**: 7.4+, **WP**: 5.9+, **React**: 18 (@wordpress/element)  
**Author**: Ibrahim Olammy

---

## Architecture

### Plugin Type
Standalone WordPress plugin with **optional WP Ultimo integration** (graceful degradation via `function_exists()` and `class_exists()` checks). When WP Ultimo is not active, all modules are freely available.

### Design Inspiration
The plugin is modeled structurally after **Beaver Builder** (Agency v2.9.0.5, located at `/home/olammy/projects/bb-plugin/`). Key borrowings:
- Node tree stored as flat array in post meta (`_lz_builder_data`)
- Server-side rendered settings forms (PHP templates)
- Accumulated CSS pipeline → cached file on disk
- Module class-per-slug pattern with abstract base
- Categories: content, layout, media, advanced, interactive

### Key Differences from Beaver Builder
- **Subscription gating** built in at the architecture level (not retrofitted)
- **React 18 builder UI** in iframe (Beaver uses Backbone/jQuery in iframe)
- Simplified node model (no column-group complexity — rows hold columns directly)
- 16 field types vs Beaver's 40+ (smaller surface area, easier to maintain)

---

## Directory Structure

```
lz-builder/
├── lz-builder.php              # Bootstrap: constants, loader init
├── package.json                # npm scripts (build, start, build:css)
├── webpack.config.js           # @wordpress/scripts entry: src/index.js
├── .gitignore                  # node_modules/, assets/js/build/, *.map
│
├── includes/
│   ├── class-lz-loader.php             # PSR-4 autoloader + explicit requires
│   ├── class-lz-builder.php            # Orchestrator: hooks, templates, activation
│   ├── class-lz-module-base.php        # Abstract base for all modules (90 lines)
│   ├── class-lz-module-registry.php    # Singleton registry, auto-discovers modules
│   ├── class-lz-page-data.php          # Flat node tree CRUD (524 lines)
│   ├── class-lz-css-accumulator.php    # CSS rule accumulation + file output (266 lines)
│   ├── class-lz-subscription-gate.php  # WP Ultimo plan gating (108 lines)
│   ├── class-lz-settings-form.php      # Form→Tab→Section→Field hierarchy (99 lines)
│   ├── class-lz-ajax-handlers.php      # 14 AJAX + 14 REST endpoints (1010 lines)
│   ├── class-lz-admin.php              # Admin menu, settings, Gutenberg integration (268 lines)
│   ├── class-lz-activation.php         # Cache dir, default options, rewrite flush
│   ├── class-lz-template-cpt.php       # lz_template CPT, CRUD, apply_to_post (223 lines)
│   ├── compatibility.php               # Shims for lz_trailingslashit, um detection
│   │
│   ├── modules/
│   │   ├── heading/class-heading.php, includes/frontend.php
│   │   ├── button/class-button.php, includes/frontend.php
│   │   ├── photo/class-photo.php, includes/frontend.php
│   │   ├── text-editor/class-text-editor.php, includes/frontend.php
│   │   ├── video/class-video.php, includes/frontend.php
│   │   ├── row/class-row.php, includes/frontend.php
│   │   └── column/class-column.php, includes/frontend.php
│   │
│   ├── settings/
│   │   ├── class-lz-field-types.php    # 29 field type registrations
│   │   └── fields/                     # 16 field template files
│   │       ├── field-text.php, field-textarea.php, field-editor.php
│   │       ├── field-color.php, field-select.php, field-button-group.php
│   │       ├── field-checkbox.php, field-hidden.php, field-unit.php
│   │       ├── field-dimension.php, field-border.php, field-typography.php
│   │       ├── field-link.php, field-photo.php, field-icon.php, field-code.php
│   │
│   └── limitations/
│       ├── class-limit-builder-modules.php   # UM Limit: per-module visibility
│       └── class-limit-builder-templates.php # UM Limit: per-category template visibility
│
├── assets/
│   ├── js/
│   │   ├── admin/lz-builder-gutenberg.js    # Gutenberg "Edit with Lz Builder" button
│   │   └── build/lz-builder.js             # Built React app (447 lines, 24KB — likely incomplete)
│   ├── scss/
│   │   ├── lz-builder.scss                 # Builder UI SCSS (698 lines)
│   │   ├── lz-builder-frontend.scss        # Frontend styles (imports module partials)
│   │   └── modules/                        # Per-module SCSS partials
│   └── css/build/                          # Compiled CSS output
│       ├── lz-builder.css, lz-builder-frontend.css, lz-admin.css
│
├── templates/
│   ├── builder-shell.php                   # Iframe shell (<div id="lz-builder-root">)
│   ├── builder-preview.php                 # Preview iframe with click-to-select JS
│   ├── admin-settings.php                  # Settings page (post types, breakpoints, cache)
│   └── admin-plans.php                     # Module→plan assignment form
│
├── tests/             # Empty — no tests written yet
└── languages/         # Empty — no translations yet
```

---

## Module System

### Base Class (`LZ_Module_Base` at `includes/class-lz-module-base.php`)
```
Namespace: LzBuilder
Abstract methods:
  - get_settings_form(): array  → returns Form→Tab→Section→Field tree
  - render(\stdClass $node, \stdClass $settings): string
Concrete hooks:
  - get_css() → return CSS rules via LZ_CSS_Accumulator
  - enqueue_scripts(), enqueue_ui_scripts(), update(), delete(), filter_settings()
Properties:
  - slug, name, description, category, icon, group, required_plan (null|array), dir, url
```

### Registry (`LZ_Module_Registry`)
- Singleton; scans `includes/modules/{slug}/class-{slug}.php` during `init()` (priority 1)
- Resolves kebab slug → PascalCase class: `text-editor` → `\LzBuilder\Modules\Text_Editor`
- `get_modules_for_panel()` returns available modules grouped by category (gated by plan)

### 7 Registered Modules

| Module | Slug | Category | Key Fields |
|--------|------|----------|------------|
| Heading | heading | content | text, tag (h1-h6), alignment, color, typography, border, margin |
| Button | button | content | text, link, style (flat/gradient/outlined), size, width, bg/text color, border, padding, margin |
| Image | photo | media | photo (ID), alt, link, alignment, size, border_radius, margin |
| Text Editor | text-editor | content | text (TinyMCE), color, typography |
| Video | video | media | video_type (embed/file), embed_url, video_file, poster, autoplay, loop, controls, aspect_ratio |
| Row | row | layout | width (full/fixed), max_content_width, min_height, bg color/image/repeat/size, border, padding, margin |
| Column | column | layout | size (hidden), vertical_alignment, responsive_order, bg color, padding, border |

### CSS Pattern
All modules use the selector pattern `.lz-node-{node_id}` for unique targeting. CSS is accumulated via `LZ_CSS_Accumulator::add_rule()` and output as a cached file.

---

## WP Ultimo Integration (Subscription Gating)

### How It Works
The plugin implements a **hybrid gating model**:

**Server-side (primary)**: Modules with a `required_plan` property are checked against the user's active WP Ultimo membership. The `LZ_Subscription_Gate::check_plan_access()` method calls `wu_has_product($plan_slug)` to verify the user owns the required product. If not, the module is excluded from `get_available_modules()` and never registers.

**Client-side (supplemental)**: Locked modules are sent to the React frontend via `get_locked_modules_data()` with upgrade URLs/buttons. The UI shows the locked state and upgrade CTA.

### Limitation Classes (`includes/limitations/`)
These extend `WP_Ultimo\Limitations\Limit` — WP Ultimo's native limitation framework:

- **`Limit_Builder_Modules`**: Each module has `{visibility: visible|hidden, behavior: available}` properties. WP Ultimo admins can hide/restrict specific builder modules per plan. Registered via `wu_register_limit_module('lz_builder_modules', ...)`.
- **`Limit_Builder_Templates`**: Template categories can be hidden per plan. Registered via `wu_register_limit_module('lz_builder_templates', ...)`.

### Key Functions Used (from WP Ultimo)
- `wu_has_product($slug, $check_site = true)` — checks if the current user/site has a given product/membership
- `wu_register_limit_module($id, $class)` — registers a custom limitation class
- `wu_generate_upgrade_to_unlock_url($args)` — generates upgrade URL
- `wu_generate_upgrade_to_unlock_button($label, $args)` — generates upgrade button HTML

### Graceful Degradation
All WP Ultimo calls are guarded by `function_exists()` or `class_exists('WP_Ultimo')`. When WP Ultimo is absent, all modules are freely available and limitation classes are never instantiated.

---

## Settings / Form System

### Hierarchy
**Form → Tabs → Sections → Fields**

A form is an array of tabs:
```php
[
  [ 'title' => 'General', 'sections' => [
    [ 'title' => 'Content', 'fields' => [
      'my_field' => [ 'type' => 'text', 'label' => 'My Field', 'default' => 'hello' ],
    ]],
  ]],
]
```

### 16 Field Types Implemented
text, textarea, editor (TinyMCE), color, select, button-group, checkbox, hidden, unit (number+unit combo), dimension (4 sides), border (compound), typography (compound), link (url+target+rel), photo (media library), icon (grid picker), code (monospace)

### 29 Registered Types (some lack field templates)
spacing, align, font, gradient, shadow, animation, form, raw, ordering, suggest, multiple-photos, video — these are registered but may lack frontend template files.

### Settings Storage
Settings are stored as flat keys in the node's `settings` object. Compound types (border, typography) use prefixed flat keys: `border_style`, `border_width`, `border_color`, `typography_font_family`, etc.

### Render Flow
`LZ_Settings_Form::render_field($type, $key, $value, $config)` loads `includes/settings/fields/field-{$type}.php`, passing `$field_value` and `$field` into scope.

---

## Page Data Management (`LZ_Page_Data`)

### Storage
- **Published**: `_lz_builder_data` post meta (JSON array of nodes)
- **Draft**: `_lz_builder_draft` post meta (same format)
- Static cache: `self::$cache` keyed by `post_id|status`

### Node Structure
```php
[
  'node_id'   => 'unique_string',
  'type'      => 'row' | 'column' | 'module',
  'parent_id' => 'parent_node_id' | '',
  'position'  => int,
  'module'    => 'slug' (only for type=module),
  'settings'  => \stdClass,
]
```

### Row Layouts (9 presets)
1-col, 2-cols, 3-cols, 4-cols, 5-cols, 6-cols, left-sidebar, right-sidebar, left-right-sidebar

### Key Methods
- `get_layout_data($post_id, $status)` — fetch node tree
- `add_row($post_id, $layout, $position)` — row + column group + columns
- `add_module($post_id, $module_slug, $parent_id, $position)` — auto-creates row if needed
- `delete_node($post_id, $node_id)` — recursive delete
- `move_node($post_id, $node_id, $new_parent, $position)`
- `duplicate_node($post_id, $node_id)` — deep copy subtree
- `save_settings($post_id, $node_id, $settings)`
- `get_builder_content($post_id)` — renders full page HTML
- `validate_node_tree($data)` — structural validation

### ID Generation
Uses `uniqid('lz_', true)` prefixed with the node type.

### Post ID Resolution
`get_current_data()` / `save_current_data()` resolve the post ID from `$post` global or `debug_backtrace()` for context-free CRUD calls.

---

## CSS Pipeline (`LZ_CSS_Accumulator`)

### Breakpoints
| Breakpoint | Media Query |
|-----------|-------------|
| default | (none) |
| tablet | `@media (max-width: 768px)` |
| phone | `@media (max-width: 480px)` |

### Architecture
- Rules accumulated in `self::$rules[breakpoint][selector][property] = value`
- `render()` produces formatted CSS grouped by breakpoint
- `render_file($post_id)` writes to `wp-content/uploads/lz-builder/cache/{post_id}.css`
- `.htaccess` with "Deny from all" + silence `index.php` on activation

### Helper Methods
- `add_rule($selector, $property, $value, $breakpoint)`
- `add_rules($selector, $props, $breakpoint)` — batch add with colon-syntax support
- `border($selector, $settings, $key, $breakpoint)` — compound border output
- `typography($selector, $settings, $key, $breakpoint)` — font-family, weight, size, line-height, letter-spacing, transform, style
- `dimension($selector, $settings, $key, $breakpoint)` — top/right/bottom/left

---

## AJAX & REST API

### 14 AJAX Actions (`wp_ajax_lz_builder_*`)
All require `edit_posts` capability + nonce verification.

| Action | Method | Purpose |
|--------|--------|---------|
| save_layout | POST | Save published layout |
| save_draft | POST | Save draft |
| get_layout | GET | Fetch layout data |
| add_row | POST | Add row with layout preset |
| add_module | POST | Add module to column |
| delete_node | POST | Delete node + children |
| move_node | POST | Reposition node |
| duplicate_node | POST | Deep copy node |
| save_settings | POST | Update node settings + re-render |
| render_node | GET | Render single node HTML |
| render_settings_form | POST | Generate settings form HTML (~300 lines server-side form builder) |
| get_templates | GET | List templates |
| apply_template | POST | Apply template to post |
| search_modules | GET | Filter/search available modules |

### 14 REST Routes (`lz-builder/v1/`)
Same functionality with `_rest` suffix callbacks. Register via `rest_api_init`.

---

## Template System (`LZ_Template_CPT`)

### Custom Post Type
- `lz_template` — non-public, shows in admin under Lz Builder menu
- Supports: title, thumbnail, REST API
- Capabilities: custom `edit_lz_template`, `edit_lz_templates`, etc.

### Meta Fields
- `_lz_template_data` — JSON node tree
- `_lz_required_plan` — WP Ultimo product slug for gating
- `_lz_template_category` — categorization

### Key Methods
- `get_templates($args)` — query with optional category/plan/access filtering
- `get_categories()` — distinct categories from postmeta
- `apply_to_post($template_id, $post_id)` — deep copy with node_id remapping + plan gating
- `get_template_data($post_id)` — decode stored JSON

---

## Admin Interface (`LZ_Admin`)

### Menu Structure
- Parent: `lz-builder`
  - Builder (separator/lead-in)
  - Templates (list table for lz_template)
  - Settings (post types, breakpoints, cache clear)
  - Module Plans (per-module product slug assignment)

### Integrations
- **Gutenberg**: Injects "Edit with Lz Builder" button via JS (`lz-builder-gutenberg.js`)
- **Classic Editor**: Banner above title with Launch Builder link
- **Post List**: Row action "Edit with Lz Builder" for enabled post types
- **Admin Bar**: "Edit with Lz Builder" link on frontend, "Exit Builder" when builder active

### Settings
- Post type selection (checkboxes for public post types)
- Responsive breakpoints (tablet/phone in px)
- Clear cache button

### Module Plans Page
Table of all registered modules with text input for WP Ultimo product slug. Stored as `lz_module_plan_{slug}` options.

---

## React Builder UI (Frontend)

### Architecture
- **iframe-based**: Builder runs in a full-screen iframe via `builder-shell.php`
- **Entry point**: `src/index.js` → webpack → `assets/js/build/lz-builder.js`
- **Framework**: React 18 via `@wordpress/element`
- **Data passing**: PHP `wp_localize_script()` provides `LZBuilderData` object with modules, locked_modules, nonce, ajax_url, rest_url, strings

### Current State
The built JS is **447 lines / 24KB** — this is likely a minimal or incomplete implementation. The SCSS (`lz-builder.scss`, 698 lines) has substantial styling for:
- Toolbar (top bar with save/publish/exit)
- Sidebar (left panel with module list and settings forms)
- Canvas area (main content display)
- Settings forms (field styling)
- Module dragging interactions

### Preview iframe (`builder-preview.php`)
- Renders frontend content with node overlay
- Click-to-select: sends `lz_open_settings` message to parent frame
- Hover outlines with dashed indigo border
- `postMessage` protocol for layout updates and module replacement

### Communication Protocol
| Direction | Action | Purpose |
|-----------|--------|---------|
| Preview → Parent | `lz_open_settings` | Click module, open settings panel |
| Parent → Preview | `lz_render_layout` | Full layout refresh |
| Parent → Preview | `lz_replace_module` | Single module re-render after save |

---

## Build System

### JavaScript
- `@wordpress/scripts` (v27.9.0) with custom webpack config
- Entry: `src/index.js` → Output: `assets/js/build/lz-builder.js`

### CSS
- SCSS compiled via `sass` (v1.101.0)
- 3 entry points → 3 compiled files:
  - `lz-builder.scss` → `lz-builder.css` (builder UI)
  - `lz-builder-frontend.scss` → `lz-builder-frontend.css` (frontend display)
  - `lz-admin.scss` → `lz-admin.css` (admin pages)

### npm Scripts
```bash
npm run build       # wp-scripts build + sass compile
npm run start       # wp-scripts dev server
npm run build:css   # sass only
npm run lint:js     # wp-scripts lint-js
```

---

## Sister Plugin: Lz Funnels

At `/home/olammy/projects/lz-funnels` and `/home/olammy/projects/lz-funnels-v2`:
- A checkout funnel plugin (v2.0.0)
- Same author, same subscription gating pattern via WP Ultimo
- Shares `wu_has_product()` gating approach
- Has analytics tracking (`lz_funnels_events` table, `Tracker`, `Stats` classes)
- The source at `/home/olammy/projects/lz-builder/src/` (Admin/Menu.php, Modules/Checkout, Modules/Thankyou) appears to be an older version or partial cross-contamination

---

## What's Complete vs Needs Work

### Solid / Feature-Complete
- PHP backend engine: loader, builder orchestrator, module base + registry
- All 7 modules with settings forms, CSS, and frontend templates
- Page data CRUD (add, delete, move, duplicate, save, render)
- Settings form system with 16 field templates
- CSS accumulator with 3 breakpoints
- WP Ultimo subscription gating (gate, limitation classes)
- AJAX handlers (14 endpoints)
- REST API (14 endpoints)
- Admin interface (menu, settings, plans, Gutenberg integration)
- Template CPT with apply_to_post
- Activation/deactivation lifecycle
- Compatibility shims

### Needs Work / Likely Incomplete
1. **React builder UI** (`assets/js/build/lz-builder.js` at 447 lines) — likely a stub or very early version. The main drag-and-drop interface needs full implementation.
2. **Tests** (`tests/` is empty) — no test suite exists
3. **Translations** (`languages/` is empty) — no .po/.mo files
4. **Missing field templates** — ~13 field types are registered in `LZ_Field_Types` but may not have template files: spacing, align, font, gradient, shadow, animation, form, raw, ordering, suggest, multiple-photos, video
5. **Documentation** — no README or user docs

---

## Development Quick Reference

### Key File Paths
- Plugin bootstrap: `lz-builder.php`
- Autoloader: `includes/class-lz-loader.php`
- Main orchestrator: `includes/class-lz-builder.php`
- Module base: `includes/class-lz-module-base.php`
- Module registry: `includes/class-lz-module-registry.php`
- Page data: `includes/class-lz-page-data.php`
- CSS engine: `includes/class-lz-css-accumulator.php`
- Subscription gate: `includes/class-lz-subscription-gate.php`
- Settings form: `includes/class-lz-settings-form.php`
- AJAX/REST: `includes/class-lz-ajax-handlers.php`
- Admin: `includes/class-lz-admin.php`
- Templates: `includes/class-lz-template-cpt.php`
- Activation: `includes/class-lz-activation.php`

### Adding a New Module
1. Create `includes/modules/{slug}/class-{slug}.php`
2. Class `\LzBuilder\Modules\{PascalCase} extends LZ_Module_Base`
3. Implement `get_settings_form()` and `render()`
4. Optionally implement `get_css()`, `enqueue_scripts()`
5. Create `includes/modules/{slug}/includes/frontend.php`
6. Create `assets/scss/modules/_{slug}.scss` and import in `lz-builder-frontend.scss`
7. Registry auto-discovers on `init()` — no manual registration needed
8. Assign plan in admin: Lz Builder → Module Plans

### Adding a New Field Type
1. Register in `includes/settings/class-lz-field-types.php` `init()`
2. Create `includes/settings/fields/field-{type}.php`
3. Add sanitization case in `LZ_Settings_Form::sanitize_value()`
