<div class="wrap lz-admin-wrap">
    <h1><?php esc_html_e('Lz Builder Settings', 'lz-builder'); ?></h1>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('lz_builder_settings'); ?>
        <input type="hidden" name="action" value="lz_builder_save_settings">
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Post Types', 'lz-builder'); ?></th>
                <td>
                    <?php
                    $post_types = get_post_types(['public' => true], 'objects');
                    $enabled = get_option('lz_builder_post_types', ['page', 'post']);
                    foreach ($post_types as $pt) {
                        printf(
                            '<label><input type="checkbox" name="lz_post_types[]" value="%s" %s> %s</label><br>',
                            esc_attr($pt->name),
                            checked(in_array($pt->name, $enabled), true, false),
                            esc_html($pt->label)
                        );
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Responsive Breakpoints', 'lz-builder'); ?></th>
                <td>
                    <label><?php esc_html_e('Tablet (px):', 'lz-builder'); ?>
                        <input type="number" name="lz_tablet_breakpoint" value="<?php echo esc_attr(get_option('lz_tablet_breakpoint', 768)); ?>" class="small-text">
                    </label><br>
                    <label><?php esc_html_e('Phone (px):', 'lz-builder'); ?>
                        <input type="number" name="lz_phone_breakpoint" value="<?php echo esc_attr(get_option('lz_phone_breakpoint', 480)); ?>" class="small-text">
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Cache', 'lz-builder'); ?></th>
                <td>
                    <button type="submit" name="lz_clear_cache" class="button">
                        <?php esc_html_e('Clear Builder Cache', 'lz-builder'); ?>
                    </button>
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="submit" name="lz_save_settings" class="button button-primary">
                <?php esc_html_e('Save Settings', 'lz-builder'); ?>
            </button>
        </p>
    </form>
</div>
