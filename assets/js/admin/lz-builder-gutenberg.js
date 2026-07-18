(function($) {
    if (!$('#editor').length || !window.LZBuilderGutenberg) return;
    var btnHtml = $('#lz-builder-gutenberg-button-tmpl').html();
    if (!btnHtml) return;
    var editUrl = LZBuilderGutenberg.editUrl;
    wp.data.subscribe(function() {
        setTimeout(function() {
            var toolbar = $('.edit-post-header-toolbar, .editor-header-toolbar').first();
            if (!toolbar.length) return;
            if (toolbar.find('#lz-builder-gutenberg-button').length) return;
            var btn = $(btnHtml);
            btn.find('button').on('click', function(e) {
                e.preventDefault();
                window.location.href = editUrl;
            });
            toolbar.append(btn);
        }, 1);
    });
})(jQuery);
