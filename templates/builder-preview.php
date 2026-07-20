<?php if (!defined('ABSPATH')) exit;

add_filter('show_admin_bar', '__return_false');

add_filter('body_class', function ($classes) {
    $classes[] = 'lz-builder-preview';
    return $classes;
});

add_action('wp_enqueue_scripts', function () {
    if (wp_style_is('lz-builder-frontend', 'registered')) {
        wp_enqueue_style('lz-builder-frontend');
    }
}, 999);

get_header(); ?>
<style>
.lz-node-overlay {
    pointer-events: none;
    position: absolute;
    z-index: 999;
    border: 2px solid #6366f1;
    border-radius: 4px;
    display: none;
}
.lz-column-drop {
    transition: outline 0.15s ease;
}
.lz-column-drop--active {
    outline: 2px dashed #6366f1;
    outline-offset: 2px;
}
</style>
<div id="lz-builder-node-overlay" class="lz-node-overlay"></div>
<div class="lz-builder-content-area" id="lz-builder-content-area">
    <?php
    while (have_posts()) :
        the_post();
        the_content();
    endwhile;
    ?>
</div>
<script>
(function() {
    var contentArea = document.getElementById('lz-builder-content-area');
    var overlay = document.getElementById('lz-builder-node-overlay');
    var selectedNodeId = null;
    var parentOrigin = window.location.origin;

    window.addEventListener('message', function(event) {
        if (event.origin !== parentOrigin) return;
        if (!event.data || !event.data.action) return;

        if (event.data.action === 'lz_render_layout' && contentArea && event.data.html) {
            console.log('[lz preview] lz_render_layout — setting innerHTML, length:', event.data.html.length);
            contentArea.innerHTML = event.data.html;
            bindModuleClickEvents();
            bindColumnDropTargets();
        }

        if (event.data.action === 'lz_append_to_column' && event.data.column_id && event.data.html) {
            console.log('[lz preview] lz_append_to_column — column_id:', event.data.column_id, 'html length:', event.data.html.length, 'has layout:', !!event.data.layout);
            var col = contentArea.querySelector('[data-node="' + event.data.column_id + '"]');
            if (col) {
                console.log('[lz preview] column found — appending module');
                var wrapper = document.createElement('div');
                wrapper.innerHTML = event.data.html.trim();
                var newEl = wrapper.firstChild;
                if (newEl) {
                    col.appendChild(newEl);
                    bindModuleClickEvents();
                    bindColumnDropTargets();
                    var appCs = window.getComputedStyle(newEl);
                    console.log('[lz preview] appended module — tag:', newEl.tagName, 'display:', appCs.display, 'height:', appCs.height, 'rect top:', newEl.getBoundingClientRect().top, 'visible:', appCs.visibility);
                    console.log('[lz preview] parent column — innerHTML length:', col.innerHTML.length, 'height:', window.getComputedStyle(col).height, 'rect top:', col.getBoundingClientRect().top);
                }
            } else if (event.data.layout) {
                console.log('[lz preview] column NOT found — falling back to full layout render, layout length:', event.data.layout.length);
                contentArea.innerHTML = event.data.layout;
                bindModuleClickEvents();
                bindColumnDropTargets();
                var moduleCount = contentArea.querySelectorAll('[data-node-id]').length;
                var colCount = contentArea.querySelectorAll('[data-node]').length;
                var rowCount = contentArea.querySelectorAll('.lz-row').length;
                console.log('[lz preview] after innerHTML — modules:', moduleCount, 'columns:', colCount, 'rows:', rowCount);
                console.log('[lz preview] contentArea firstChild:', contentArea.firstChild ? contentArea.firstChild.outerHTML.substring(0, 200) : 'EMPTY');
                var firstMod = contentArea.querySelector('[data-node-id]');
                if (firstMod) {
                    var cs = window.getComputedStyle(firstMod);
                    console.log('[lz preview] first module computed — display:', cs.display, 'height:', cs.height, 'width:', cs.width, 'visibility:', cs.visibility, 'overflow:', cs.overflow);
                    console.log('[lz preview] first module outerHTML:', firstMod.outerHTML.substring(0, 200));
                } else {
                    console.log('[lz preview] NO data-node-id elements found in DOM');
                }
                var frontCSS = document.querySelector('link[href*="lz-builder-frontend"]');
                console.log('[lz preview] frontend stylesheet loaded:', !!frontCSS, frontCSS ? frontCSS.href : 'NONE');
                // Check parent chain heights
                var el = contentArea.firstChild;
                var chain = [];
                while (el) {
                    var ecs = window.getComputedStyle(el);
                    chain.push((el.className || el.tagName) + ' h:' + ecs.height + ' d:' + ecs.display + ' o:' + ecs.overflow);
                    el = el.firstElementChild;
                }
                console.log('[lz preview] element chain:', chain.join(' > '));
                console.log('[lz preview] contentArea computed height:', window.getComputedStyle(contentArea).height, 'overflow:', window.getComputedStyle(contentArea).overflow);
            } else {
                console.error('[lz preview] column NOT found AND no layout fallback — module dropped silently!');
            }
        }

        if (event.data.action === 'lz_replace_module' && event.data.node_id && event.data.html) {
            var oldModule = contentArea.querySelector('[data-node-id="' + event.data.node_id + '"]');
            if (oldModule) {
                var wrapper = document.createElement('div');
                wrapper.innerHTML = event.data.html.trim();
                var newEl = wrapper.firstChild;
                if (newEl) {
                    oldModule.parentNode.replaceChild(newEl, oldModule);
                    bindModuleClickEvents();
                    bindColumnDropTargets();
                }
            }
        }
    });

    // Per-column drop targets so the parent sends parent_id/position.
    function bindColumnDropTargets() {
        var cols = contentArea.querySelectorAll('.lz-column');
        for (var i = 0; i < cols.length; i++) {
            (function(col) {
                if (col._lzDropBound) return;
                col._lzDropBound = true;
                col.classList.add('lz-column-drop');

                col.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.dataTransfer.dropEffect = 'copy';
                    col.classList.add('lz-column-drop--active');
                });
                col.addEventListener('dragleave', function(e) {
                    if (!col.contains(e.relatedTarget)) {
                        col.classList.remove('lz-column-drop--active');
                    }
                });
                col.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    col.classList.remove('lz-column-drop--active');
                    var slug = e.dataTransfer.getData('text/plain');
                    var colId = col.getAttribute('data-node');
                    if (slug && colId) {
                        window.parent.postMessage({
                            action: 'lz_column_drop',
                            module: slug,
                            parent_id: colId,
                        }, parentOrigin);
                    }
                });
            })(cols[i]);
        }
    }

    function bindModuleClickEvents() {
        var nodes = contentArea.querySelectorAll('[data-node-id]');
        for (var i = 0; i < nodes.length; i++) {
            (function(el) {
                if (el._lzBound) return;
                el._lzBound = true;

                el.style.cursor = 'pointer';

                el.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var nodeId = this.getAttribute('data-node-id');
                    selectedNodeId = nodeId;

                    if (overlay) {
                        var r = this.getBoundingClientRect();
                        overlay.style.display = 'block';
                        overlay.style.top  = (r.top  + window.scrollY) + 'px';
                        overlay.style.left = (r.left + window.scrollX) + 'px';
                        overlay.style.width  = r.width  + 'px';
                        overlay.style.height = r.height + 'px';
                    }

                    window.parent.postMessage({
                        action: 'lz_open_settings',
                        node_id: nodeId,
                    }, parentOrigin);
                });

                el.addEventListener('mouseenter', function() {
                    if (this.getAttribute('data-node-id') !== selectedNodeId) {
                        this.style.outline = '1px dashed rgba(99,102,241,0.5)';
                    }
                });
                el.addEventListener('mouseleave', function() {
                    if (this.getAttribute('data-node-id') !== selectedNodeId) {
                        this.style.outline = '';
                    }
                });
            })(nodes[i]);
        }
    }

    function init() {
        bindModuleClickEvents();
        bindColumnDropTargets();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
<?php get_footer(); ?>
