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
            contentArea.innerHTML = event.data.html;
            bindModuleClickEvents();
            bindColumnDropTargets();
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
