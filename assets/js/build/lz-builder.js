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


function bindColorFields(container) {
  const colorFields = container.querySelectorAll('.lz-color-field');
  colorFields.forEach(cf => {
    const swatch = cf.querySelector('.lz-color-swatch');
    const textInput = cf.querySelector('.lz-field-color-text');
    const nativeInput = cf.querySelector('.lz-field-color-native');
    function updateColor(val) {
      const v = val || 'transparent';
      if (swatch) swatch.style.backgroundColor = v;
      if (textInput) {
        textInput.value = v;
      }
      if (nativeInput && nativeInput.value !== v) nativeInput.value = v;
    }
    if (swatch && nativeInput) {
      swatch.addEventListener('click', () => nativeInput.click());
    }
    if (nativeInput) {
      nativeInput.addEventListener('input', e => updateColor(e.target.value));
    }
    if (textInput) {
      textInput.addEventListener('input', e => updateColor(e.target.value));
    }
  });
}
function bindButtonGroups(container, doAutoSave) {
  const btnGroups = container.querySelectorAll('.lz-field-btn-group');
  btnGroups.forEach(group => {
    const options = group.querySelectorAll('.lz-btn-group-option');
    const hiddenInput = group.querySelector('input[type="hidden"]');
    options.forEach(opt => {
      opt.addEventListener('click', function () {
        options.forEach(o => o.classList.remove('lz-btn-group-active'));
        this.classList.add('lz-btn-group-active');
        if (hiddenInput) hiddenInput.value = this.getAttribute('data-value');
        doAutoSave();
      });
    });
  });
}
function SettingsPanel({
  nodeId,
  showNotice,
  postToIframe,
  dispatch
}) {
  const [html, setHtml] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const formRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const autoSaveTimerRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  const boundRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(false);
  const handlersRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)([]);
  const doAutoSave = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(() => {
    clearTimeout(autoSaveTimerRef.current);
    if (!formRef.current) return;
    autoSaveTimerRef.current = setTimeout(() => {
      if (!formRef.current) return;
      const form = formRef.current.querySelector('#lz-settings-form');
      if (!form) return;
      const inputs = form.querySelectorAll('input[name], select[name], textarea[name]');
      const settings = {};
      inputs.forEach(inp => {
        const name = inp.getAttribute('name');
        if (!name) return;
        settings[name] = inp.type === 'checkbox' ? inp.checked : inp.value;
      });
      (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('save_settings', {
        node_id: nodeId,
        settings
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
    }, 120);
  }, [nodeId, postToIframe, dispatch]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!nodeId) {
      setHtml('');
      return;
    }
    setLoading(true);
    boundRef.current = false;
    (0,_api__WEBPACK_IMPORTED_MODULE_1__.lzFetch)('render_settings_form', {
      node_id: nodeId
    }).then(r => {
      setLoading(false);
      if (r && r.success && r.data && r.data.html) {
        setHtml(r.data.html);
      } else {
        const msg = r && r.data && r.data.message || 'Could not load settings.';
        setHtml('');
        showNotice(msg, 'error');
      }
    });
  }, [nodeId]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!html || !formRef.current || boundRef.current) return;
    boundRef.current = true;
    const container = formRef.current;
    const h = [];
    bindColorFields(container);
    bindButtonGroups(container, doAutoSave);
    const inputs = container.querySelectorAll('input[name], select[name], textarea[name]');
    inputs.forEach(inp => {
      const handler = () => doAutoSave();
      inp.addEventListener('input', handler);
      inp.addEventListener('change', handler);
      h.push({
        el: inp,
        type: 'input',
        handler
      });
      h.push({
        el: inp,
        type: 'change',
        handler
      });
    });
    const backBtn = container.querySelector('#lz-settings-back');
    if (backBtn) {
      const handler = e => {
        e.preventDefault();
        clearTimeout(autoSaveTimerRef.current);
        dispatch({
          type: 'BACK_TO_MODULES'
        });
      };
      backBtn.addEventListener('click', handler);
      h.push({
        el: backBtn,
        type: 'click',
        handler
      });
    }
    const form = container.querySelector('#lz-settings-form');
    if (form) {
      const handler = e => {
        e.preventDefault();
        doAutoSave();
        setTimeout(() => dispatch({
          type: 'BACK_TO_MODULES'
        }), 400);
      };
      form.addEventListener('submit', handler);
      h.push({
        el: form,
        type: 'submit',
        handler
      });
    }
    handlersRef.current = h;
    return () => {
      h.forEach(({
        el,
        type,
        handler
      }) => {
        el.removeEventListener(type, handler);
      });
      boundRef.current = false;
    };
  }, [html, doAutoSave]);
  if (!nodeId) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      className: 'lz-action-panel'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('p', null, 'Select a module on the page to edit its settings.'));
  }
  if (loading) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
      className: 'lz-action-panel'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('p', null, 'Loading settings\u2026'));
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)('div', {
    ref: formRef,
    className: 'lz-settings-panel',
    dangerouslySetInnerHTML: {
      __html: html
    }
  });
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