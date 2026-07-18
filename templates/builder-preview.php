<?php if (!defined('ABSPATH')) exit;

add_filter('show_admin_bar', '__return_false');

add_filter('body_class', function ($classes) {
    $classes[] = 'lz-builder-preview';
    return $classes;
});

// Ensure frontend module CSS is loaded in the preview iframe.
add_action('wp_enqueue_scripts', function () {
    if (wp_style_is('lz-builder-frontend', 'registered')) {
        wp_enqueue_style('lz-builder-frontend');
    }
}, 999);

get_header(); ?>
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
                }
            }
        }
    });

    // Click on any module node to open its settings.
    function bindModuleClickEvents() {
        var nodes = contentArea.querySelectorAll('[data-node-id]');
        for (var i = 0; i < nodes.length; i++) {
            (function(el) {
                // Prevent duplicate listeners.
                if (el._lzBound) return;
                el._lzBound = true;

                el.style.cursor = 'pointer';
                el.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var nodeId = this.getAttribute('data-node-id');
                    selectedNodeId = nodeId;

                    // Show selection overlay.
                    var rect = this.getBoundingClientRect();
                    if (overlay) {
                        overlay.style.display = 'block';
                        overlay.style.top = rect.top + 'px';
                        overlay.style.left = rect.left + 'px';
                        overlay.style.width = rect.width + 'px';
                        overlay.style.height = rect.height + 'px';
                    }

                    window.parent.postMessage({
                        action: 'lz_open_settings',
                        node_id: nodeId,
                        rect: {
                            top: rect.top,
                            left: rect.left,
                            width: rect.width,
                            height: rect.height
                        }
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

    // Initial binding.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindModuleClickEvents);
    } else {
        bindModuleClickEvents();
    }
})();
</script>
<?php get_footer(); ?>
