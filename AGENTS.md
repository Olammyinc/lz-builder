<!-- Added: 2026-07-17 -->
## React Builder UI

The builder frontend lives in `src/` and compiles to `assets/js/build/lz-builder.js` via `@wordpress/scripts` (webpack).

**Architecture:**
- `src/index.js` — entry point, renders `<App>` into `#lz-builder-root`
- `src/api.js` — `lzFetch()` helper wrapping FormData + fetch with JSON error handling
- `src/store.js` — `useReducer` state: activeTab, editingNodeId, hasUnsaved, notices, layout
- `src/components/App.js` — orchestrator: message event listener, iframe ref, notice dispatch
- `src/components/Toolbar.js` — Save Draft (calls `save_draft` with layout tree), Publish (`save_layout`), Exit
- `src/components/Sidebar.js` — 3-tab layout (Modules/Templates/Settings) with delete button
- `src/components/ModuleList.js` — categorized grid, search, drag-and-drop + click add
- `src/components/TemplateList.js` — fetches templates via AJAX, apply button refreshes iframe
- `src/components/SettingsPanel.js` — loads server-rendered form via `dangerouslySetInnerHTML`, binds auto-save with debounce, color pickers, button groups
- `src/components/Canvas.js` — preview iframe + drop zone (drag activated on `.lz-builder-canvas`)
- `src/components/Notices.js` — dismissible success/error notifications

**Key integrations:**
- `LZBuilderData` global: `{ post_id, modules[], locked_modules[], nonce, ajax_url, rest_url, exit_url, strings }`
- AJAX: `lz_builder_*` actions via FormData, nonce verification
- postMessage: `lz_open_settings` (preview→parent), `lz_render_layout`/`lz_replace_module` (parent→preview)
- CSS classes match `assets/scss/lz-builder.scss` exactly
