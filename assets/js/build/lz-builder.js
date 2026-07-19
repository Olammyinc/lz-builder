/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/api.js"
/*!********************!*\
  !*** ./src/api.js ***!
  \********************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getPreviewUrl: () => (/* binding */ getPreviewUrl),
/* harmony export */   lzFetch: () => (/* binding */ lzFetch)
/* harmony export */ });
function lzFetch(action, formData = {}) {
  const data = window.LZBuilderData;
  const fd = new FormData();
  fd.append('action', 'lz_builder_' + action);
  fd.append('nonce', data.nonce);
  fd.append('post_id', data.post_id);
  Object.keys(formData).forEach(k => {
    const v = formData[k];
    fd.append(k, typeof v === 'object' ? JSON.stringify(v) : v);
  });
  return fetch(data.ajax_url, {
    method: 'POST',
    credentials: 'same-origin',
    body: fd
  }).then(r => r.text().then(t => {
    try {
      return JSON.parse(t);
    } catch {
      return {
        success: false,
        data: {
          message: 'Invalid server response.'
        }
      };
    }
  })).catch(() => ({
    success: false,
    data: {
      message: 'Network error.'
    }
  }));
}
function getPreviewUrl() {
  const url = new URL(window.location.href);
  url.searchParams.delete('lz_builder');
  url.searchParams.set('lz_builder_preview', '1');
  return url.toString();
}

/***/ },

/***/ "./src/components/App.js"
/*!*******************************!*\
  !*** ./src/components/App.js ***!
  \*******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ App)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../store */ "./src/store.js");
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../api */ "./src/api.js");
/* harmony import */ var _Toolbar__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Toolbar */ "./src/components/Toolbar.js");
/* harmony import */ var _Sidebar__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./Sidebar */ "./src/components/Sidebar.js");
/* harmony import */ var _Canvas__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./Canvas */ "./src/components/Canvas.js");
/* harmony import */ var _Notices__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./Notices */ "./src/components/Notices.js");







function App({
  data
}) {
  const [state, dispatch] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useReducer)(_store__WEBPACK_IMPORTED_MODULE_1__.reducer, _store__WEBPACK_IMPORTED_MODULE_1__.initialState);
  const [isDragging, setIsDragging] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const iframeRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const dragCounter = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(0);
  const showNotice = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)((message, type = 'success', textOnly = false) => {
    const id = Date.now() + Math.random() * 1000;
    dispatch({
      type: 'ADD_NOTICE',
      id,
      message,
      noticeType: type,
      textOnly
    });
    setTimeout(() => {
      dispatch({
        type: 'REMOVE_NOTICE',
        id
      });
    }, type === 'success' ? 3000 : 5000);
  }, []);
  const postToIframe = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(msg => {
    if (iframeRef.current && iframeRef.current.contentWindow) {
      iframeRef.current.contentWindow.postMessage(msg, window.location.origin);
    }
  }, []);
  const updatePreview = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(html => {
    postToIframe({
      action: 'lz_render_layout',
      html
    });
  }, [postToIframe]);
  const refreshLayout = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(() => {
    (0,_api__WEBPACK_IMPORTED_MODULE_2__.lzFetch)('get_layout', {
      status: 'draft'
    }).then(r => {
      const layout = r && r.success && r.data && r.data.data || [];
      dispatch({
        type: 'SET_LAYOUT',
        layout
      });
      dispatch({
        type: 'SET_LAYOUT_LOADED'
      });
    });
  }, []);

  // Global drag-state tracking so the iframe drop overlay works reliably
  // across Chrome and Firefox.
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    function handleDragStart() {
      dragCounter.current += 1;
      setIsDragging(true);
    }
    function handleDragEnd() {
      dragCounter.current = Math.max(0, dragCounter.current - 1);
      if (dragCounter.current === 0) {
        setIsDragging(false);
      }
    }
    document.addEventListener('dragstart', handleDragStart);
    document.addEventListener('dragend', handleDragEnd);
    return () => {
      document.removeEventListener('dragstart', handleDragStart);
      document.removeEventListener('dragend', handleDragEnd);
    };
  }, []);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    refreshLayout();
  }, [refreshLayout]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    function handleMessage(event) {
      if (event.origin !== window.location.origin) return;
      if (!event.data || !event.data.action) return;
      if (event.data.action === 'lz_open_settings' && event.data.node_id) {
        dispatch({
          type: 'EDIT_NODE',
          nodeId: event.data.node_id
        });
      }
      if (event.data.action === 'lz_column_drop' && event.data.module) {
        (0,_api__WEBPACK_IMPORTED_MODULE_2__.lzFetch)('add_module', {
          module: event.data.module,
          parent_id: event.data.parent_id
        }).then(r => {
          if (r && r.success) {
            if (r.data && r.data.html && r.data.parent_id) {
              postToIframe({
                action: 'lz_append_to_column',
                column_id: r.data.parent_id,
                html: r.data.html
              });
            } else if (r.data && r.data.layout) {
              updatePreview(r.data.layout);
            }
            dispatch({
              type: 'SET_UNSAVED',
              value: true
            });
            showNotice('Module added!', 'success');
            refreshLayout();
          } else {
            showNotice(r && r.data && r.data.message || 'Could not add module.', 'error');
          }
        });
      }
    }
    window.addEventListener('message', handleMessage);
    return () => window.removeEventListener('message', handleMessage);
  }, [updatePreview, refreshLayout, showNotice]);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-builder-root-inner'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_Notices__WEBPACK_IMPORTED_MODULE_6__["default"], {
    notices: state.notices,
    dispatch
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_Toolbar__WEBPACK_IMPORTED_MODULE_3__["default"], {
    state,
    dispatch,
    data,
    showNotice
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-builder-workspace'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_Sidebar__WEBPACK_IMPORTED_MODULE_4__["default"], {
    state,
    dispatch,
    data,
    showNotice,
    postToIframe,
    updatePreview,
    refreshLayout
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_Canvas__WEBPACK_IMPORTED_MODULE_5__["default"], {
    data,
    updatePreview,
    showNotice,
    postToIframe,
    iframeRef,
    dispatch,
    refreshLayout,
    isDragging
  })));
}

/***/ },

/***/ "./src/components/Canvas.js"
/*!**********************************!*\
  !*** ./src/components/Canvas.js ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Canvas)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../api */ "./src/api.js");


function Canvas({
  data,
  updatePreview,
  showNotice,
  postToIframe,
  iframeRef,
  dispatch,
  refreshLayout,
  isDragging
}) {
  const previewUrl = (0,_api__WEBPACK_IMPORTED_MODULE_1__.getPreviewUrl)();
  const handleDrop = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(e => {
    e.preventDefault();
    const slug = e.dataTransfer.getData('text/plain');
    if (slug) {
      (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('add_module', {
        module: slug
      }).then(r => {
        if (r && r.success) {
          if (r.data && r.data.html && r.data.parent_id) {
            postToIframe({
              action: 'lz_append_to_column',
              column_id: r.data.parent_id,
              html: r.data.html
            });
          } else if (r.data && r.data.layout) {
            updatePreview(r.data.layout);
          }
          dispatch({
            type: 'SET_UNSAVED',
            value: true
          });
          showNotice('Module added!', 'success');
          refreshLayout();
        } else {
          showNotice(r && r.data && r.data.message || 'Could not add module.', 'error');
        }
      });
    }
  }, [updatePreview, showNotice, postToIframe, dispatch, refreshLayout]);
  const handleDragOver = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(e => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  }, []);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-builder-canvas',
    onDrop: handleDrop,
    onDragOver: handleDragOver
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-drop-zone' + (isDragging ? ' lz-drop-zone--active' : '')
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-drop-zone-text'
  }, 'Drop module here')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('iframe', {
    ref: iframeRef,
    id: 'lz-builder-iframe',
    className: 'lz-builder-frame',
    title: 'Lz Builder Preview',
    src: previewUrl,
    style: isDragging ? {
      pointerEvents: 'none'
    } : undefined
  }));
}

/***/ },

/***/ "./src/components/ModuleList.js"
/*!**************************************!*\
  !*** ./src/components/ModuleList.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ ModuleList)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../api */ "./src/api.js");


const ICONS = {
  heading: '\uD83D\uDD0D',
  'text-editor': '\uD83D\uDCDD',
  photo: '\uD83D\uDDBC',
  button: '\uD83D\uDD17',
  video: '\uD83C\uDFA5',
  row: '\u2B1C',
  column: '\u25AD'
};
const ROW_LAYOUTS = [{
  key: '1-col',
  label: '1 Col',
  cols: 1
}, {
  key: '2-cols',
  label: '2 Cols',
  cols: 2
}, {
  key: '3-cols',
  label: '3 Cols',
  cols: 3
}, {
  key: '4-cols',
  label: '4 Cols',
  cols: 4
}, {
  key: 'left-sidebar',
  label: 'Left SB',
  cols: '1/3-2/3'
}, {
  key: 'right-sidebar',
  label: 'Right SB',
  cols: '2/3-1/3'
}];
function RowIcon({
  cols
}) {
  if (typeof cols === 'number') {
    const blocks = Array.from({
      length: cols
    }, (_, i) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('span', {
      key: i,
      className: 'lz-row-icon-block'
    }));
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('span', {
      className: 'lz-row-icon'
    }, ...blocks);
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('span', {
    className: 'lz-row-icon lz-row-icon--sidebar'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('span', {
    className: 'lz-row-icon-block lz-row-icon-block--wide'
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('span', {
    className: 'lz-row-icon-block'
  }));
}
function ModuleList({
  modules,
  lockedModules,
  showNotice,
  updatePreview,
  postToIframe,
  dispatch,
  refreshLayout,
  handleAddRow
}) {
  const [search, setSearch] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const handleAddModule = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(slug => {
    (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('add_module', {
      module: slug
    }).then(r => {
      if (r && r.success) {
        if (r.data && r.data.html && r.data.parent_id) {
          postToIframe({
            action: 'lz_append_to_column',
            column_id: r.data.parent_id,
            html: r.data.html
          });
        } else if (r.data.layout) {
          updatePreview(r.data.layout);
        }
        dispatch({
          type: 'SET_UNSAVED',
          value: true
        });
        showNotice('Module added!', 'success');
        refreshLayout();
      } else {
        showNotice(r && r.data && r.data.message || 'Could not add module.', 'error');
      }
    });
  }, [showNotice, updatePreview, dispatch, refreshLayout]);
  const handleDragStart = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)((e, slug) => {
    e.dataTransfer.setData('text/plain', slug);
    e.dataTransfer.effectAllowed = 'copy';
  }, []);
  const isLocked = slug => {
    return lockedModules.some(lm => lm.slug === slug);
  };
  const getLockedModule = slug => {
    return lockedModules.find(lm => lm.slug === slug);
  };
  const filtered = modules.map(cat => ({
    ...cat,
    modules: cat.modules.filter(mod => {
      if (!search) return true;
      const q = search.toLowerCase();
      return mod.name.toLowerCase().includes(q) || mod.slug.toLowerCase().includes(q);
    })
  })).filter(cat => cat.modules.length > 0);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-modules-panel'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-search-bar'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'search',
    className: 'lz-search-input',
    placeholder: 'Search modules\u2026',
    value: search,
    onInput: e => setSearch(e.target.value)
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-module-category'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-module-category-title'
  }, 'Rows'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-module-grid'
  }, ...ROW_LAYOUTS.map(rl => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    key: rl.key,
    className: 'lz-module-card',
    tabIndex: 0,
    role: 'button',
    onClick: () => handleAddRow && handleAddRow(rl.key),
    onKeyDown: e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        handleAddRow && handleAddRow(rl.key);
      }
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-module-card-icon'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RowIcon, {
    cols: rl.cols
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-module-card-name'
  }, rl.label))))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-module-list'
  }, ...filtered.map(cat => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    key: cat.slug,
    className: 'lz-module-category'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-module-category-title'
  }, cat.name), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-module-grid'
  }, ...cat.modules.map(mod => {
    const locked = isLocked(mod.slug);
    const icon = ICONS[mod.slug] || '\uD83D\uDCE6';
    const lockedData = getLockedModule(mod.slug);
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      key: mod.slug,
      className: 'lz-module-card' + (locked ? ' lz-module-card--locked' : ''),
      draggable: !locked,
      tabIndex: locked ? -1 : 0,
      role: 'button',
      onDragStart: e => !locked && handleDragStart(e, mod.slug),
      onClick: () => !locked && handleAddModule(mod.slug),
      onKeyDown: e => {
        if (!locked && (e.key === 'Enter' || e.key === ' ')) {
          e.preventDefault();
          handleAddModule(mod.slug);
        }
      }
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      className: 'lz-module-card-icon'
    }, locked ? '\uD83D\uDD12' : icon), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      className: 'lz-module-card-name'
    }, mod.name), locked && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      className: 'lz-locked-overlay'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('span', null, 'Requires Upgrade'), lockedData && lockedData.upgrade_url && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('a', {
      href: lockedData.upgrade_url,
      className: 'lz-upgrade-btn',
      target: '_blank',
      rel: 'noopener noreferrer'
    }, 'Upgrade')));
  }))))));
}

/***/ },

/***/ "./src/components/Notices.js"
/*!***********************************!*\
  !*** ./src/components/Notices.js ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Notices)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function Notices({
  notices,
  dispatch
}) {
  if (!notices || notices.length === 0) return null;
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-notices'
  }, ...notices.map(notice => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    key: notice.id,
    className: 'lz-notice lz-notice--' + (notice.type || 'success')
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('span', {
    className: 'lz-notice-text'
  }, notice.message), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('button', {
    type: 'button',
    className: 'lz-notice-dismiss',
    'aria-label': 'Dismiss',
    onClick: () => dispatch({
      type: 'REMOVE_NOTICE',
      id: notice.id
    })
  }, '\u00D7'))));
}

/***/ },

/***/ "./src/components/SettingsPanel.js"
/*!*****************************************!*\
  !*** ./src/components/SettingsPanel.js ***!
  \*****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ SettingsPanel)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../api */ "./src/api.js");
/* harmony import */ var _fields_registry__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../fields/registry */ "./src/fields/registry.js");



const COMPOUND_TYPES = new Set(['typography', 'border', 'dimension', 'spacing', 'link', 'shadow', 'gradient', 'video', 'form']);
function SettingsPanel({
  nodeId,
  showNotice,
  postToIframe,
  dispatch
}) {
  const [schema, setSchema] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(null);
  const [values, setValues] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)({});
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const autoSaveTimerRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const mountedRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(true);
  const doAutoSave = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(newValues => {
    clearTimeout(autoSaveTimerRef.current);
    autoSaveTimerRef.current = setTimeout(() => {
      if (!mountedRef.current) return;
      (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('save_settings', {
        node_id: nodeId,
        settings: newValues
      }).then(r => {
        if (r && r.success && r.data && r.data.html) {
          dispatch({
            type: 'SET_UNSAVED',
            value: true
          });
          postToIframe({
            action: 'lz_replace_module',
            node_id: nodeId,
            html: r.data.html
          });
        }
      });
    }, 300);
  }, [nodeId, postToIframe, dispatch]);
  const handleChange = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(change => {
    setValues(prev => {
      if (typeof change === 'object' && change !== null) {
        const next = {
          ...prev,
          ...change
        };
        doAutoSave(next);
        return next;
      }
      return prev;
    });
  }, [doAutoSave]);
  const handleSingleChange = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)((key, value) => {
    setValues(prev => {
      const next = {
        ...prev,
        [key]: value
      };
      doAutoSave(next);
      return next;
    });
  }, [doAutoSave]);
  const fetchIdRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(0);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!nodeId) {
      setSchema(null);
      setValues({});
      return;
    }
    const fetchId = ++fetchIdRef.current;
    setLoading(true);
    setSchema(null);
    (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('get_settings_schema', {
      node_id: nodeId
    }).then(r => {
      if (!mountedRef.current || fetchId !== fetchIdRef.current) return;
      setLoading(false);
      if (r && r.success && r.data) {
        setSchema(r.data);
        setValues(r.data.values || {});
      } else {
        const msg = r && r.data && r.data.message || 'Could not load settings.';
        showNotice(msg, 'error');
        setSchema({});
        setValues({});
      }
    });
  }, [nodeId]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    mountedRef.current = true;
    return () => {
      mountedRef.current = false;
      clearTimeout(autoSaveTimerRef.current);
    };
  }, []);
  if (!nodeId) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      className: 'lz-action-panel'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('p', null, 'Select a module on the page to edit its settings.'));
  }
  if (loading || !schema) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      className: 'lz-action-panel'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('p', null, 'Loading settings\u2026'));
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-settings-panel'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-settings-header'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('h3', {
    className: 'lz-settings-title'
  }, schema.title), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('button', {
    type: 'button',
    className: 'lz-btn lz-btn-back',
    onClick: e => {
      e.preventDefault();
      clearTimeout(autoSaveTimerRef.current);
      dispatch({
        type: 'BACK_TO_MODULES'
      });
    }
  }, '\u2190 Back')), ...schema.tabs.map((tab, ti) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    key: ti,
    className: 'lz-settings-tab'
  }, tab.title && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('h4', {
    className: 'lz-settings-tab-title'
  }, tab.title), ...tab.sections.map((section, si) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    key: si
  }, section.title && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('h5', {
    className: 'lz-settings-section-title'
  }, section.title), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-settings-fields'
  }, ...Object.entries(section.fields || {}).map(([fieldKey, field]) => {
    var _field$default;
    const FieldComponent = _fields_registry__WEBPACK_IMPORTED_MODULE_2__["default"][field.type];
    const isCompound = COMPOUND_TYPES.has(field.type);
    const fieldValue = isCompound ? values : values[fieldKey] !== undefined ? values[fieldKey] : (_field$default = field.default) !== null && _field$default !== void 0 ? _field$default : '';
    const changeHandler = val => {
      if (typeof val === 'object' && val !== null) {
        handleChange(val);
      } else {
        handleSingleChange(fieldKey, val);
      }
    };
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      key: fieldKey,
      className: 'lz-field lz-field-' + (field.type || 'text')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
      className: 'lz-field-label'
    }, field.label || fieldKey), FieldComponent ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(FieldComponent, {
      field: {
        ...field,
        key: fieldKey
      },
      value: fieldValue !== undefined ? fieldValue : '',
      onChange: changeHandler
    }) : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      className: 'lz-field-unknown'
    }, 'Unknown field type: ' + (field.type || 'unknown')));
  })))))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-settings-actions'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('button', {
    type: 'button',
    className: 'lz-btn lz-btn-primary lz-btn-save-settings',
    onClick: () => {
      doAutoSave(values);
      setTimeout(() => dispatch({
        type: 'BACK_TO_MODULES'
      }), 400);
    }
  }, 'Save')));
}

/***/ },

/***/ "./src/components/Sidebar.js"
/*!***********************************!*\
  !*** ./src/components/Sidebar.js ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Sidebar)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../api */ "./src/api.js");
/* harmony import */ var _ModuleList__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ModuleList */ "./src/components/ModuleList.js");
/* harmony import */ var _TemplateList__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./TemplateList */ "./src/components/TemplateList.js");
/* harmony import */ var _SettingsPanel__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./SettingsPanel */ "./src/components/SettingsPanel.js");





function Sidebar({
  state,
  dispatch,
  data,
  showNotice,
  postToIframe,
  updatePreview,
  refreshLayout
}) {
  const tabs = [{
    key: 'modules',
    label: 'Modules'
  }, {
    key: 'templates',
    label: 'Templates'
  }, {
    key: 'settings',
    label: 'Settings'
  }];
  const handleTabClick = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)((e, tab) => {
    e.preventDefault();
    dispatch({
      type: 'SET_TAB',
      tab
    });
  }, [dispatch]);
  const handleTabKeyDown = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)((e, tab, index) => {
    let newIndex = index;
    if (e.key === 'ArrowRight') {
      newIndex = (index + 1) % tabs.length;
    } else if (e.key === 'ArrowLeft') {
      newIndex = (index - 1 + tabs.length) % tabs.length;
    } else {
      return;
    }
    e.preventDefault();
    dispatch({
      type: 'SET_TAB',
      tab: tabs[newIndex].key
    });
    const els = e.currentTarget.parentElement.querySelectorAll('.lz-tab');
    if (els[newIndex]) els[newIndex].focus();
  }, [dispatch, tabs]);
  const handleDeleteNode = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(() => {
    const nodeId = state.editingNodeId;
    if (!nodeId) return;
    (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('delete_node', {
      node_id: nodeId
    }).then(r => {
      if (r && r.success) {
        if (r.data && r.data.layout) updatePreview(r.data.layout);
        showNotice('Node deleted.', 'success');
        dispatch({
          type: 'SET_UNSAVED',
          value: true
        });
        dispatch({
          type: 'BACK_TO_MODULES'
        });
        refreshLayout();
      } else {
        showNotice(r && r.data && r.data.message || 'Could not delete node.', 'error');
      }
    });
  }, [state.editingNodeId, showNotice, dispatch, refreshLayout, updatePreview]);
  const handleDuplicateNode = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(() => {
    const nodeId = state.editingNodeId;
    if (!nodeId) return;
    (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('duplicate_node', {
      node_id: nodeId
    }).then(r => {
      if (r && r.success) {
        if (r.data && r.data.layout) updatePreview(r.data.layout);
        showNotice('Node duplicated.', 'success');
        dispatch({
          type: 'SET_UNSAVED',
          value: true
        });
        refreshLayout();
      } else {
        showNotice(r && r.data && r.data.message || 'Could not duplicate.', 'error');
      }
    });
  }, [state.editingNodeId, showNotice, dispatch, refreshLayout, updatePreview]);
  const handleAddRow = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(layout => {
    (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('add_row', {
      layout
    }).then(r => {
      if (r && r.success) {
        if (r.data && r.data.layout) updatePreview(r.data.layout);
        showNotice('Row added!', 'success');
        dispatch({
          type: 'SET_UNSAVED',
          value: true
        });
        refreshLayout();
      } else {
        showNotice(r && r.data && r.data.message || 'Could not add row.', 'error');
      }
    });
  }, [showNotice, dispatch, refreshLayout, updatePreview]);
  const renderContent = () => {
    switch (state.activeTab) {
      case 'modules':
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_ModuleList__WEBPACK_IMPORTED_MODULE_2__["default"], {
          modules: data.modules || [],
          lockedModules: data.locked_modules || [],
          showNotice,
          updatePreview,
          postToIframe,
          dispatch,
          refreshLayout,
          handleAddRow
        });
      case 'templates':
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_TemplateList__WEBPACK_IMPORTED_MODULE_3__["default"], {
          showNotice,
          refreshLayout,
          postToIframe
        });
      case 'settings':
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
          className: 'lz-settings-panel-wrap'
        }, state.editingNodeId && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
          className: 'lz-node-actions'
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('button', {
          className: 'lz-btn lz-btn-danger',
          onClick: handleDeleteNode
        }, 'Delete'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('button', {
          className: 'lz-btn lz-btn-save',
          onClick: handleDuplicateNode
        }, 'Duplicate')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_SettingsPanel__WEBPACK_IMPORTED_MODULE_4__["default"], {
          nodeId: state.editingNodeId,
          showNotice,
          postToIframe,
          dispatch
        }));
      default:
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
          className: 'lz-action-panel'
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('p', null, 'Select a module on the page to edit its settings.'));
    }
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-builder-sidebar'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-tab-bar',
    role: 'tablist'
  }, ...tabs.map((tab, index) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('button', {
    key: tab.key,
    role: 'tab',
    'aria-selected': state.activeTab === tab.key,
    'aria-controls': 'lz-sidebar-content',
    tabIndex: state.activeTab === tab.key ? 0 : -1,
    className: 'lz-tab' + (state.activeTab === tab.key ? ' lz-tab--active' : ''),
    'data-tab': tab.key,
    onClick: e => handleTabClick(e, tab.key),
    onKeyDown: e => handleTabKeyDown(e, tab.key, index)
  }, tab.label))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-sidebar-content',
    id: 'lz-sidebar-content'
  }, renderContent()));
}

/***/ },

/***/ "./src/components/TemplateList.js"
/*!****************************************!*\
  !*** ./src/components/TemplateList.js ***!
  \****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ TemplateList)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../api */ "./src/api.js");


function TemplateList({
  showNotice,
  refreshLayout,
  postToIframe
}) {
  const [templates, setTemplates] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(true);
  const [search, setSearch] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('get_templates', {}).then(r => {
      setLoading(false);
      if (r && r.success && r.data && r.data.templates) {
        setTemplates(r.data.templates);
      }
    });
  }, []);
  const handleApply = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(templateId => {
    showNotice('Applying template\u2026', 'success');
    (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('apply_template', {
      template_id: templateId
    }).then(r => {
      if (r && r.success) {
        showNotice('Template applied!', 'success');
        if (refreshLayout) refreshLayout();
        if (postToIframe) {
          postToIframe({
            action: 'lz_render_layout',
            html: ''
          });
        }
      } else {
        showNotice(r && r.data && r.data.message || 'Could not apply template.', 'error');
      }
    });
  }, [showNotice, refreshLayout, postToIframe]);
  const filtered = search ? templates.filter(tmpl => tmpl.title.toLowerCase().includes(search.toLowerCase())) : templates;
  if (loading) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      className: 'lz-empty-state'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('p', null, 'Loading templates\u2026'));
  }
  if (templates.length === 0) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      className: 'lz-empty-state'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('p', null, 'No templates available.'));
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-templates-panel'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-search-bar'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'search',
    className: 'lz-search-input',
    placeholder: 'Search templates\u2026',
    value: search,
    onInput: e => setSearch(e.target.value)
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-template-list'
  }, ...filtered.map(tmpl => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    key: tmpl.id,
    className: 'lz-template-item'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-template-info'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('strong', null, tmpl.title)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('button', {
    className: 'lz-btn lz-btn-primary',
    onClick: () => handleApply(tmpl.id)
  }, 'Apply')))));
}

/***/ },

/***/ "./src/components/Toolbar.js"
/*!***********************************!*\
  !*** ./src/components/Toolbar.js ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Toolbar)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../api */ "./src/api.js");


function Toolbar({
  state,
  dispatch,
  data,
  showNotice
}) {
  const handleSave = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(() => {
    (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('save_draft', {}).then(r => {
      if (r && r.success) {
        showNotice('Draft saved!', 'success');
        dispatch({
          type: 'SET_UNSAVED',
          value: false
        });
      } else {
        showNotice(r && r.data && r.data.message || 'Could not save.', 'error');
      }
    });
  }, [showNotice, dispatch]);
  const handlePublish = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(() => {
    (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('save_layout', {}).then(r => {
      if (r && r.success) {
        showNotice('Page published!', 'success');
        dispatch({
          type: 'SET_UNSAVED',
          value: false
        });
      } else {
        showNotice(r && r.data && r.data.message || 'Could not publish.', 'error');
      }
    });
  }, [showNotice, dispatch]);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-toolbar'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-toolbar-left'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('span', {
    className: 'lz-toolbar-brand'
  }, 'Lz Builder'), state.hasUnsaved && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('span', {
    className: 'lz-unsaved-badge'
  }, 'Unsaved')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-toolbar-center'
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-toolbar-right'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('button', {
    className: 'lz-btn lz-btn-save',
    onClick: handleSave,
    disabled: state.loadingLayout
  }, 'Save Draft'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('button', {
    className: 'lz-btn lz-btn-primary',
    onClick: handlePublish,
    disabled: state.loadingLayout
  }, 'Publish'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('a', {
    className: 'lz-btn lz-btn-exit',
    href: data.exit_url || '#'
  }, 'Exit Builder')));
}

/***/ },

/***/ "./src/fields/field-align.js"
/*!***********************************!*\
  !*** ./src/fields/field-align.js ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldAlign)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldAlign({
  field,
  value,
  onChange
}) {
  const opts = field.options || [{
    value: 'left',
    label: 'Left'
  }, {
    value: 'center',
    label: 'Center'
  }, {
    value: 'right',
    label: 'Right'
  }];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-btn-group'
  }, ...opts.map(o => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('button', {
    key: o.value,
    type: 'button',
    className: 'lz-btn-group-option' + (value === o.value ? ' lz-btn-group-active' : ''),
    onClick: () => onChange(o.value)
  }, o.label)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'hidden',
    name: field.key,
    value: value || ''
  }));
}

/***/ },

/***/ "./src/fields/field-animation.js"
/*!***************************************!*\
  !*** ./src/fields/field-animation.js ***!
  \***************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldAnimation)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldAnimation({
  field,
  value,
  onChange
}) {
  const opts = field.options || [];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    value: value || '',
    onChange: e => onChange(e.target.value)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    value: ''
  }, 'None'), ...opts.map(o => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: o.value,
    value: o.value
  }, o.label)));
}

/***/ },

/***/ "./src/fields/field-border.js"
/*!************************************!*\
  !*** ./src/fields/field-border.js ***!
  \************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldBorder)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

const STYLES = ['', 'solid', 'dashed', 'dotted', 'double'];
const WIDTH_UNITS = ['px', 'em'];
const RADIUS_UNITS = ['px', '%', 'em'];
function FieldBorder({
  field,
  value,
  onChange
}) {
  const k = field.key;
  function sub(suffix, val) {
    onChange({
      [k + suffix]: val
    });
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-border'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Style'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    value: value[k + '_style'] || '',
    onChange: e => sub('_style', e.target.value)
  }, ...STYLES.map(s => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: s,
    value: s
  }, s || 'None'))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Width'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-unit-wrap'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_width'] || '',
    onInput: e => sub('_width', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    value: value[k + '_width_unit'] || 'px',
    onChange: e => sub('_width_unit', e.target.value)
  }, ...WIDTH_UNITS.map(u => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: u,
    value: u
  }, u)))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Color'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-color-field'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input lz-field-color-text',
    value: value[k + '_color'] || '',
    placeholder: '#000000',
    onInput: e => sub('_color', e.target.value)
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Radius'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-unit-wrap'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_radius'] || '',
    onInput: e => sub('_radius', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    value: value[k + '_radius_unit'] || 'px',
    onChange: e => sub('_radius_unit', e.target.value)
  }, ...RADIUS_UNITS.map(u => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: u,
    value: u
  }, u)))));
}

/***/ },

/***/ "./src/fields/field-button-group.js"
/*!******************************************!*\
  !*** ./src/fields/field-button-group.js ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldButtonGroup)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldButtonGroup({
  field,
  value,
  onChange
}) {
  const options = field.options || [];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-btn-group'
  }, ...options.map(opt => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('button', {
    key: opt.value,
    type: 'button',
    className: 'lz-btn-group-option' + (value === opt.value ? ' lz-btn-group-active' : ''),
    'data-value': opt.value,
    onClick: () => onChange(opt.value)
  }, opt.label)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'hidden',
    name: field.key,
    value: value || ''
  }));
}

/***/ },

/***/ "./src/fields/field-checkbox.js"
/*!**************************************!*\
  !*** ./src/fields/field-checkbox.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldCheckbox)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldCheckbox({
  field,
  value,
  onChange
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'checkbox',
    className: 'lz-field-checkbox',
    name: field.key,
    checked: !!value,
    onChange: e => onChange(e.target.checked ? '1' : '')
  });
}

/***/ },

/***/ "./src/fields/field-code.js"
/*!**********************************!*\
  !*** ./src/fields/field-code.js ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldCode)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldCode({
  field,
  value,
  onChange
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('textarea', {
    className: 'lz-field-code',
    name: field.key,
    rows: field.rows || 8,
    style: {
      fontFamily: 'monospace'
    },
    onInput: e => onChange(e.target.value)
  }, value || '');
}

/***/ },

/***/ "./src/fields/field-color.js"
/*!***********************************!*\
  !*** ./src/fields/field-color.js ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldColor)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldColor({
  field,
  value,
  onChange
}) {
  const v = value || '#000000';
  const swatchRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-color-field'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input lz-field-color-text',
    name: field.key,
    value: v,
    placeholder: '#000000',
    onInput: e => onChange(e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('span', {
    ref: swatchRef,
    className: 'lz-color-swatch',
    style: {
      backgroundColor: v
    },
    tabIndex: 0,
    role: 'button',
    onClick: () => {
      const native = swatchRef.current?.querySelector('input[type="color"]');
      if (native) native.click();
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'color',
    className: 'lz-field-color-native',
    value: v,
    onInput: e => onChange(e.target.value)
  })));
}

/***/ },

/***/ "./src/fields/field-dimension.js"
/*!***************************************!*\
  !*** ./src/fields/field-dimension.js ***!
  \***************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldDimension)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

const SIDES = ['top', 'right', 'bottom', 'left'];
const UNITS = ['px', 'em', '%', 'rem', 'vw', 'vh'];
function FieldDimension({
  field,
  value,
  onChange
}) {
  const k = field.key;
  function sub(suffix, val) {
    onChange({
      [k + suffix]: val
    });
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-dimension'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-inline-label'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'checkbox',
    className: 'lz-field-checkbox',
    checked: !!value[k + '_linked'],
    onChange: e => sub('_linked', e.target.checked ? '1' : '')
  }), ' Link all sides'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-dimension-grid'
  }, ...SIDES.map(side => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    key: side,
    className: 'lz-dimension-item'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('span', {
    className: 'lz-dimension-label'
  }, side), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_' + side] || '',
    onInput: e => sub('_' + side, e.target.value)
  })))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select lz-dimension-unit',
    value: value[k + '_unit'] || 'px',
    onChange: e => sub('_unit', e.target.value)
  }, ...UNITS.map(u => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: u,
    value: u
  }, u))));
}

/***/ },

/***/ "./src/fields/field-font.js"
/*!**********************************!*\
  !*** ./src/fields/field-font.js ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldFont)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldFont({
  field,
  value,
  onChange
}) {
  const fonts = field.options || [{
    value: '',
    label: 'Default'
  }];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    name: field.key,
    value: value || '',
    onChange: e => onChange(e.target.value)
  }, ...fonts.map(f => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: f.value,
    value: f.value
  }, f.label)));
}

/***/ },

/***/ "./src/fields/field-form.js"
/*!**********************************!*\
  !*** ./src/fields/field-form.js ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldForm)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldForm({
  field,
  value,
  onChange
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input',
    value: typeof value === 'object' ? JSON.stringify(value) : value || '',
    placeholder: 'Form config',
    onInput: e => {
      try {
        onChange(JSON.parse(e.target.value));
      } catch {
        onChange(e.target.value);
      }
    }
  });
}

/***/ },

/***/ "./src/fields/field-gradient.js"
/*!**************************************!*\
  !*** ./src/fields/field-gradient.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldGradient)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldGradient({
  field,
  value,
  onChange
}) {
  const k = field.key;
  function sub(suffix, val) {
    onChange({
      [k + suffix]: val
    });
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-gradient'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Type'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    value: value[k + '_type'] || 'linear',
    onChange: e => sub('_type', e.target.value)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    value: 'linear'
  }, 'Linear'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    value: 'radial'
  }, 'Radial')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Angle'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_angle'] || '',
    onInput: e => sub('_angle', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Color 1'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input',
    value: value[k + '_color_1'] || '',
    placeholder: '#000000',
    onInput: e => sub('_color_1', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Stop 1'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_stop_1'] || '',
    onInput: e => sub('_stop_1', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Color 2'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input',
    value: value[k + '_color_2'] || '',
    placeholder: '#000000',
    onInput: e => sub('_color_2', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Stop 2'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_stop_2'] || '',
    onInput: e => sub('_stop_2', e.target.value)
  }));
}

/***/ },

/***/ "./src/fields/field-hidden.js"
/*!************************************!*\
  !*** ./src/fields/field-hidden.js ***!
  \************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldHidden)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldHidden({
  field,
  value
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'hidden',
    name: field.key,
    value: value || ''
  });
}

/***/ },

/***/ "./src/fields/field-icon.js"
/*!**********************************!*\
  !*** ./src/fields/field-icon.js ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldIcon)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldIcon({
  field,
  value,
  onChange
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input',
    name: field.key,
    value: value || '',
    placeholder: 'dashicons-admin-site',
    onInput: e => onChange(e.target.value)
  });
}

/***/ },

/***/ "./src/fields/field-link.js"
/*!**********************************!*\
  !*** ./src/fields/field-link.js ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldLink)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldLink({
  field,
  value,
  onChange
}) {
  const k = field.key;
  function sub(suffix, val) {
    onChange({
      [k + suffix]: val
    });
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-link'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'URL'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input',
    value: value[k + '_url'] || '',
    placeholder: 'https://',
    onInput: e => sub('_url', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Target'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    value: value[k + '_target'] || '',
    onChange: e => sub('_target', e.target.value)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    value: ''
  }, 'Same Window'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    value: '_blank'
  }, 'New Window')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-inline-label'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'checkbox',
    className: 'lz-field-checkbox',
    checked: !!value[k + '_nofollow'],
    onChange: e => sub('_nofollow', e.target.checked ? '1' : '')
  }), ' nofollow'));
}

/***/ },

/***/ "./src/fields/field-multiple-photos.js"
/*!*********************************************!*\
  !*** ./src/fields/field-multiple-photos.js ***!
  \*********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldMultiplePhotos)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldMultiplePhotos({
  field,
  value,
  onChange
}) {
  const ids = Array.isArray(value) ? value : [];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-multiple-photos'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input',
    value: ids.join(','),
    placeholder: 'Attachment IDs (comma-separated)',
    onInput: e => onChange(e.target.value.split(',').filter(Boolean))
  }));
}

/***/ },

/***/ "./src/fields/field-ordering.js"
/*!**************************************!*\
  !*** ./src/fields/field-ordering.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldOrdering)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldOrdering({
  field,
  value,
  onChange
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input',
    value: value || '',
    placeholder: 'Ordering (JSON array)',
    onInput: e => onChange(e.target.value)
  });
}

/***/ },

/***/ "./src/fields/field-photo.js"
/*!***********************************!*\
  !*** ./src/fields/field-photo.js ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldPhoto)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldPhoto({
  field,
  value,
  onChange
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    name: field.key,
    value: value || '',
    placeholder: 'Attachment ID',
    onInput: e => onChange(e.target.value)
  });
}

/***/ },

/***/ "./src/fields/field-raw.js"
/*!*********************************!*\
  !*** ./src/fields/field-raw.js ***!
  \*********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldRaw)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldRaw({
  field,
  value,
  onChange
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('textarea', {
    className: 'lz-field-code',
    rows: field.rows || 6,
    style: {
      fontFamily: 'monospace'
    },
    onInput: e => onChange(e.target.value)
  }, value || '');
}

/***/ },

/***/ "./src/fields/field-select.js"
/*!************************************!*\
  !*** ./src/fields/field-select.js ***!
  \************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldSelect)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldSelect({
  field,
  value,
  onChange
}) {
  const options = field.options || [];
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    name: field.key,
    value: value || '',
    onChange: e => onChange(e.target.value)
  }, ...options.map(opt => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: opt.value,
    value: opt.value
  }, opt.label)));
}

/***/ },

/***/ "./src/fields/field-shadow.js"
/*!************************************!*\
  !*** ./src/fields/field-shadow.js ***!
  \************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldShadow)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldShadow({
  field,
  value,
  onChange
}) {
  const k = field.key;
  function sub(suffix, val) {
    onChange({
      [k + suffix]: val
    });
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-shadow'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Color'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input',
    value: value[k + '_color'] || '',
    placeholder: '#000000',
    onInput: e => sub('_color', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Horizontal'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_horizontal'] || '',
    onInput: e => sub('_horizontal', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Vertical'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_vertical'] || '',
    onInput: e => sub('_vertical', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Blur'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_blur'] || '',
    onInput: e => sub('_blur', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Spread'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_spread'] || '',
    onInput: e => sub('_spread', e.target.value)
  }));
}

/***/ },

/***/ "./src/fields/field-spacing.js"
/*!*************************************!*\
  !*** ./src/fields/field-spacing.js ***!
  \*************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldSpacing)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

const SIDES = ['top', 'right', 'bottom', 'left'];
const UNITS = ['px', 'em', '%'];
function FieldSpacing({
  field,
  value,
  onChange
}) {
  const k = field.key;
  function sub(suffix, val) {
    onChange({
      [k + suffix]: val
    });
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-spacing-wrap'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-spacing-inputs'
  }, ...SIDES.map(side => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    key: side
  }, side.charAt(0).toUpperCase() + side.slice(1), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-spacing',
    step: 'any',
    value: value[k + '_' + side] || '',
    onInput: e => sub('_' + side, e.target.value)
  })))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-spacing-unit',
    value: value[k + '_unit'] || 'px',
    onChange: e => sub('_unit', e.target.value)
  }, ...UNITS.map(u => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: u,
    value: u
  }, u))));
}

/***/ },

/***/ "./src/fields/field-suggest.js"
/*!*************************************!*\
  !*** ./src/fields/field-suggest.js ***!
  \*************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldSuggest)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldSuggest({
  field,
  value,
  onChange
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input',
    value: value || '',
    placeholder: 'Type to search...',
    onInput: e => onChange(e.target.value)
  });
}

/***/ },

/***/ "./src/fields/field-text.js"
/*!**********************************!*\
  !*** ./src/fields/field-text.js ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldText)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldText({
  field,
  value,
  onChange
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field-input',
    name: field.key,
    value: value || '',
    placeholder: field.placeholder || '',
    onInput: e => onChange(e.target.value)
  });
}

/***/ },

/***/ "./src/fields/field-textarea.js"
/*!**************************************!*\
  !*** ./src/fields/field-textarea.js ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldTextarea)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldTextarea({
  field,
  value,
  onChange
}) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('textarea', {
    className: 'lz-field-textarea',
    name: field.key,
    rows: field.rows || 4,
    onInput: e => onChange(e.target.value)
  }, value || '');
}

/***/ },

/***/ "./src/fields/field-typography.js"
/*!****************************************!*\
  !*** ./src/fields/field-typography.js ***!
  \****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldTypography)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

const FONT_FAMILIES = [{
  value: '',
  label: 'Default'
}, {
  value: 'Arial, Helvetica, sans-serif',
  label: 'Arial'
}, {
  value: 'Helvetica, Arial, sans-serif',
  label: 'Helvetica'
}, {
  value: 'Georgia, serif',
  label: 'Georgia'
}, {
  value: 'Times New Roman, serif',
  label: 'Times New Roman'
}, {
  value: 'Verdana, Geneva, sans-serif',
  label: 'Verdana'
}, {
  value: 'Courier New, monospace',
  label: 'Courier New'
}, {
  value: 'Open Sans, sans-serif',
  label: 'Open Sans'
}, {
  value: 'Roboto, sans-serif',
  label: 'Roboto'
}, {
  value: 'Lato, sans-serif',
  label: 'Lato'
}, {
  value: 'Montserrat, sans-serif',
  label: 'Montserrat'
}, {
  value: 'Inter, sans-serif',
  label: 'Inter'
}, {
  value: 'Poppins, sans-serif',
  label: 'Poppins'
}, {
  value: 'Nunito, sans-serif',
  label: 'Nunito'
}, {
  value: 'Raleway, sans-serif',
  label: 'Raleway'
}, {
  value: 'Ubuntu, sans-serif',
  label: 'Ubuntu'
}, {
  value: 'Merriweather, serif',
  label: 'Merriweather'
}, {
  value: 'Playfair Display, serif',
  label: 'Playfair Display'
}, {
  value: 'system-ui, sans-serif',
  label: 'System UI'
}];
const WEIGHTS = [{
  value: '',
  label: 'Default'
}, ...Array.from({
  length: 9
}, (_, i) => ({
  value: String((i + 1) * 100),
  label: String((i + 1) * 100)
}))];
const SIZE_UNITS = ['px', 'em', 'rem', 'vw'];
const LINE_UNITS = ['', 'em', 'px', '%'];
const TRANSFORMS = ['', 'uppercase', 'lowercase', 'capitalize'];
const LS_UNITS = ['px', 'em'];
function FieldTypography({
  field,
  value,
  onChange
}) {
  const k = field.key;
  const lines = value && value[k + '_line_height_unit'] !== undefined ? '' : '';
  function sub(suffix, val) {
    onChange({
      [k + suffix]: val
    });
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-typography'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Font Family'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    onChange: e => sub('_font_family', e.target.value),
    value: value[k + '_font_family'] || ''
  }, ...FONT_FAMILIES.map(f => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: f.value,
    value: f.value
  }, f.label))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Weight'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    onChange: e => sub('_font_weight', e.target.value),
    value: value[k + '_font_weight'] || ''
  }, ...WEIGHTS.map(w => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: w.value,
    value: w.value
  }, w.label))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Font Size'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-unit-wrap'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_font_size'] || '',
    onInput: e => sub('_font_size', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    value: value[k + '_font_size_unit'] || 'px',
    onChange: e => sub('_font_size_unit', e.target.value)
  }, ...SIZE_UNITS.map(u => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: u,
    value: u
  }, u)))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Line Height'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-unit-wrap'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_line_height'] || '',
    onInput: e => sub('_line_height', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    value: value[k + '_line_height_unit'] || lines,
    onChange: e => sub('_line_height_unit', e.target.value)
  }, ...LINE_UNITS.map(u => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: u,
    value: u
  }, u || 'None')))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Text Transform'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    value: value[k + '_text_transform'] || '',
    onChange: e => sub('_text_transform', e.target.value)
  }, ...TRANSFORMS.map(t => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: t,
    value: t
  }, t || 'None'))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-sub-label'
  }, 'Letter Spacing'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-unit-wrap'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input',
    step: 'any',
    value: value[k + '_letter_spacing'] || '',
    onInput: e => sub('_letter_spacing', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select',
    value: value[k + '_letter_spacing_unit'] || 'px',
    onChange: e => sub('_letter_spacing_unit', e.target.value)
  }, ...LS_UNITS.map(u => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: u,
    value: u
  }, u)))));
}

/***/ },

/***/ "./src/fields/field-unit.js"
/*!**********************************!*\
  !*** ./src/fields/field-unit.js ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldUnit)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldUnit({
  field,
  value,
  onChange
}) {
  const units = field.units || ['px', 'em', '%'];
  const numValue = value !== '' ? value : field.default || '';
  const unitKey = field.key + '_unit';
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-unit-wrap'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'number',
    className: 'lz-field-input lz-field-unit-value',
    name: field.key,
    value: numValue,
    step: 'any',
    onInput: e => onChange({
      [field.key]: e.target.value
    })
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-select lz-field-unit-select',
    name: unitKey,
    value: field.unit || units[0],
    onChange: e => onChange({
      [unitKey]: e.target.value
    })
  }, ...units.map(u => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    key: u,
    value: u
  }, u))));
}

/***/ },

/***/ "./src/fields/field-video.js"
/*!***********************************!*\
  !*** ./src/fields/field-video.js ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldVideo)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function FieldVideo({
  field,
  value,
  onChange
}) {
  const k = field.key;
  function sub(suffix, val) {
    onChange({
      [k + suffix]: val
    });
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    className: 'lz-field-video-wrap'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('select', {
    className: 'lz-field-video-type',
    value: value[k + '_type'] || 'embed',
    onChange: e => sub('_type', e.target.value)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    value: 'embed'
  }, 'Embed URL'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('option', {
    value: 'file'
  }, 'Media File')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field lz-field-video-url',
    value: value[k + '_url'] || '',
    placeholder: 'Video URL',
    onInput: e => sub('_url', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'text',
    className: 'lz-field lz-field-video-poster',
    value: value[k + '_poster'] || '',
    placeholder: 'Poster image URL',
    onInput: e => sub('_poster', e.target.value)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-checkbox-label'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'checkbox',
    className: 'lz-field-video-autoplay',
    checked: !!value[k + '_autoplay'],
    onChange: e => sub('_autoplay', e.target.checked ? '1' : '')
  }), ' Autoplay'), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('label', {
    className: 'lz-field-checkbox-label'
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('input', {
    type: 'checkbox',
    className: 'lz-field-video-loop',
    checked: !!value[k + '_loop'],
    onChange: e => sub('_loop', e.target.checked ? '1' : '')
  }), ' Loop'));
}

/***/ },

/***/ "./src/fields/registry.js"
/*!********************************!*\
  !*** ./src/fields/registry.js ***!
  \********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _field_text__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./field-text */ "./src/fields/field-text.js");
/* harmony import */ var _field_textarea__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./field-textarea */ "./src/fields/field-textarea.js");
/* harmony import */ var _field_select__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./field-select */ "./src/fields/field-select.js");
/* harmony import */ var _field_color__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./field-color */ "./src/fields/field-color.js");
/* harmony import */ var _field_checkbox__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./field-checkbox */ "./src/fields/field-checkbox.js");
/* harmony import */ var _field_button_group__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./field-button-group */ "./src/fields/field-button-group.js");
/* harmony import */ var _field_unit__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./field-unit */ "./src/fields/field-unit.js");
/* harmony import */ var _field_typography__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./field-typography */ "./src/fields/field-typography.js");
/* harmony import */ var _field_border__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./field-border */ "./src/fields/field-border.js");
/* harmony import */ var _field_dimension__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./field-dimension */ "./src/fields/field-dimension.js");
/* harmony import */ var _field_spacing__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./field-spacing */ "./src/fields/field-spacing.js");
/* harmony import */ var _field_link__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./field-link */ "./src/fields/field-link.js");
/* harmony import */ var _field_photo__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./field-photo */ "./src/fields/field-photo.js");
/* harmony import */ var _field_icon__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./field-icon */ "./src/fields/field-icon.js");
/* harmony import */ var _field_code__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./field-code */ "./src/fields/field-code.js");
/* harmony import */ var _field_hidden__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ./field-hidden */ "./src/fields/field-hidden.js");
/* harmony import */ var _field_align__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! ./field-align */ "./src/fields/field-align.js");
/* harmony import */ var _field_font__WEBPACK_IMPORTED_MODULE_17__ = __webpack_require__(/*! ./field-font */ "./src/fields/field-font.js");
/* harmony import */ var _field_shadow__WEBPACK_IMPORTED_MODULE_18__ = __webpack_require__(/*! ./field-shadow */ "./src/fields/field-shadow.js");
/* harmony import */ var _field_gradient__WEBPACK_IMPORTED_MODULE_19__ = __webpack_require__(/*! ./field-gradient */ "./src/fields/field-gradient.js");
/* harmony import */ var _field_animation__WEBPACK_IMPORTED_MODULE_20__ = __webpack_require__(/*! ./field-animation */ "./src/fields/field-animation.js");
/* harmony import */ var _field_multiple_photos__WEBPACK_IMPORTED_MODULE_21__ = __webpack_require__(/*! ./field-multiple-photos */ "./src/fields/field-multiple-photos.js");
/* harmony import */ var _field_video__WEBPACK_IMPORTED_MODULE_22__ = __webpack_require__(/*! ./field-video */ "./src/fields/field-video.js");
/* harmony import */ var _field_ordering__WEBPACK_IMPORTED_MODULE_23__ = __webpack_require__(/*! ./field-ordering */ "./src/fields/field-ordering.js");
/* harmony import */ var _field_suggest__WEBPACK_IMPORTED_MODULE_24__ = __webpack_require__(/*! ./field-suggest */ "./src/fields/field-suggest.js");
/* harmony import */ var _field_raw__WEBPACK_IMPORTED_MODULE_25__ = __webpack_require__(/*! ./field-raw */ "./src/fields/field-raw.js");
/* harmony import */ var _field_form__WEBPACK_IMPORTED_MODULE_26__ = __webpack_require__(/*! ./field-form */ "./src/fields/field-form.js");



























const registry = {
  text: _field_text__WEBPACK_IMPORTED_MODULE_0__["default"],
  textarea: _field_textarea__WEBPACK_IMPORTED_MODULE_1__["default"],
  editor: _field_textarea__WEBPACK_IMPORTED_MODULE_1__["default"],
  select: _field_select__WEBPACK_IMPORTED_MODULE_2__["default"],
  color: _field_color__WEBPACK_IMPORTED_MODULE_3__["default"],
  checkbox: _field_checkbox__WEBPACK_IMPORTED_MODULE_4__["default"],
  'button-group': _field_button_group__WEBPACK_IMPORTED_MODULE_5__["default"],
  unit: _field_unit__WEBPACK_IMPORTED_MODULE_6__["default"],
  typography: _field_typography__WEBPACK_IMPORTED_MODULE_7__["default"],
  border: _field_border__WEBPACK_IMPORTED_MODULE_8__["default"],
  dimension: _field_dimension__WEBPACK_IMPORTED_MODULE_9__["default"],
  spacing: _field_spacing__WEBPACK_IMPORTED_MODULE_10__["default"],
  link: _field_link__WEBPACK_IMPORTED_MODULE_11__["default"],
  photo: _field_photo__WEBPACK_IMPORTED_MODULE_12__["default"],
  icon: _field_icon__WEBPACK_IMPORTED_MODULE_13__["default"],
  code: _field_code__WEBPACK_IMPORTED_MODULE_14__["default"],
  hidden: _field_hidden__WEBPACK_IMPORTED_MODULE_15__["default"],
  align: _field_align__WEBPACK_IMPORTED_MODULE_16__["default"],
  font: _field_font__WEBPACK_IMPORTED_MODULE_17__["default"],
  shadow: _field_shadow__WEBPACK_IMPORTED_MODULE_18__["default"],
  gradient: _field_gradient__WEBPACK_IMPORTED_MODULE_19__["default"],
  animation: _field_animation__WEBPACK_IMPORTED_MODULE_20__["default"],
  'multiple-photos': _field_multiple_photos__WEBPACK_IMPORTED_MODULE_21__["default"],
  video: _field_video__WEBPACK_IMPORTED_MODULE_22__["default"],
  ordering: _field_ordering__WEBPACK_IMPORTED_MODULE_23__["default"],
  suggest: _field_suggest__WEBPACK_IMPORTED_MODULE_24__["default"],
  raw: _field_raw__WEBPACK_IMPORTED_MODULE_25__["default"],
  form: _field_form__WEBPACK_IMPORTED_MODULE_26__["default"]
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (registry);

/***/ },

/***/ "./src/store.js"
/*!**********************!*\
  !*** ./src/store.js ***!
  \**********************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initialState: () => (/* binding */ initialState),
/* harmony export */   reducer: () => (/* binding */ reducer)
/* harmony export */ });
const initialState = {
  activeTab: 'modules',
  editingNodeId: null,
  hasUnsaved: false,
  notices: [],
  layout: [],
  loadingLayout: true
};
function reducer(state, action) {
  switch (action.type) {
    case 'SET_TAB':
      return {
        ...state,
        activeTab: action.tab,
        editingNodeId: null
      };
    case 'EDIT_NODE':
      return {
        ...state,
        activeTab: 'settings',
        editingNodeId: action.nodeId
      };
    case 'BACK_TO_MODULES':
      return {
        ...state,
        activeTab: 'modules',
        editingNodeId: null
      };
    case 'SET_UNSAVED':
      return {
        ...state,
        hasUnsaved: action.value
      };
    case 'SET_LAYOUT':
      return {
        ...state,
        layout: action.layout
      };
    case 'SET_LAYOUT_LOADED':
      return {
        ...state,
        loadingLayout: false
      };
    case 'ADD_NOTICE':
      return {
        ...state,
        notices: [...state.notices, {
          id: action.id,
          message: action.message,
          type: action.noticeType,
          textOnly: action.textOnly
        }]
      };
    case 'REMOVE_NOTICE':
      return {
        ...state,
        notices: state.notices.filter(n => n.id !== action.id)
      };
    default:
      return state;
  }
}

/***/ },

/***/ "@wordpress/element"
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["element"];

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			const e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			const getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter/value functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			if(Array.isArray(definition)) {
/******/ 				var i = 0;
/******/ 				while(i < definition.length) {
/******/ 					var key = definition[i++];
/******/ 					var binding = definition[i++];
/******/ 					if(!__webpack_require__.o(exports, key)) {
/******/ 						if(binding === 0) {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, value: definition[i++] });
/******/ 						} else {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, get: binding });
/******/ 						}
/******/ 					} else if(binding === 0) { i++; }
/******/ 				}
/******/ 			} else {
/******/ 				for(var key in definition) {
/******/ 					if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 						Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 					}
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.hasOwn(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
let __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_App__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/App */ "./src/components/App.js");


const root = document.getElementById('lz-builder-root');
if (root && window.LZBuilderData) {
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.render)((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_App__WEBPACK_IMPORTED_MODULE_1__["default"], {
    data: window.LZBuilderData
  }), root);
}
})();

/******/ })()
;
//# sourceMappingURL=lz-builder.js.map