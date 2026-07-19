=== Lz Builder ===
Contributors: olammyinc
Tags: page builder, drag and drop, frontend editor, subscription gating, wp ultimo
Requires at least: 5.9
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A frontend drag-and-drop page builder with subscription-based module gating for WaaS platforms.

== Description ==

Lz Builder is a WordPress page builder that lets you create layouts visually in real frontend. Drop modules onto columns, edit settings inline, and publish — all without leaving the frontend.

**Key features:**

- Real frontend drag-and-drop editing
- 7 module types: Heading, Button, Photo, Text Editor, Video, Row, Column
- 28 field types for module settings (text, color, border, typography, dimension, etc.)
- Draft isolation — edits are saved to a draft until you Publish
- WP Ultimo integration — gate modules and templates by subscription plan
- Template system — create reusable page templates with `lz_template` CPT
- CSS accumulation and caching for all three breakpoints (desktop, tablet, mobile)
- React 18 builder UI with search, drag-and-drop, and inline auto-save

== Installation ==

1. Upload the `lz-builder` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Edit any post or page — click "Edit with Lz Builder" in the admin bar

== Frequently Asked Questions ==

= Does this require WP Ultimo? =

No. The builder works standalone. WP Ultimo integration is optional and automatically detected.

= How do I gate modules by plan? =

Set the `required_plan` property on your module class extending `LZ_Module_Base`. When WP Ultimo is active, modules are automatically gated.

= Where are the templates stored? =

Templates are `lz_template` custom posts. They appear in the Templates tab inside the builder and can be applied to any page.

== Changelog ==

= 1.7.2 =
* Notifications are now fixed-position toast popups (no more layout push)
* Row layout icons show column-count visual blocks
* Add-module uses instant append-to-column (single DOM op, no full re-render)
* Drag-and-drop now sends modules to the correct column in preview

= 1.7.1 =
* Draft isolation — all edits write to draft, Publish promotes server-side
* Per-column drop targets with parent_id/position tracking
* Fixed overlay positioning on scroll
* Security: per-post capability checks on all AJAX mutators
* Fixed empty-draft fallback (no longer resurrects published data)
* Shared settings-form HTML builder for AJAX and REST
* Prevented unauthenticated draft preview
* Row layout picker in Modules tab
* Duplicate node button in Settings tab
* Clone positioning fix (max sibling position + 1)
* Compound field recursive sanitization

= 1.7.0 =
* Initial release
* Full React 18 builder UI replacing vanilla JS stub
* 28 field templates with complete sanitization pipeline
* AJAX + REST API endpoints
* WP Ultimo subscription gating
