<div class="wrap lz-admin-wrap">
    <h1><?php esc_html_e('Module Plan Requirements', 'lz-builder'); ?></h1>
    <p><?php esc_html_e('Set which subscription plan is required for each builder module. Leave empty for free modules available to all.', 'lz-builder'); ?></p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('lz_builder_plans'); ?>
        <input type="hidden" name="action" value="lz_builder_save_plans">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Module', 'lz-builder'); ?></th>
                    <th><?php esc_html_e('Slug', 'lz-builder'); ?></th>
                    <th><?php esc_html_e('Category', 'lz-builder'); ?></th>
                    <th><?php esc_html_e('Required Plan (UM Product Slug)', 'lz-builder'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $modules = LzBuilder\LZ_Module_Registry::get_instance()->get_all_modules();
                foreach ($modules as $slug => $module) :
                    $current_plan = get_option("lz_module_plan_{$slug}", '');
                ?>
                <tr>
                    <td><?php echo esc_html($module->get_name()); ?></td>
                    <td><code><?php echo esc_html($slug); ?></code></td>
                    <td><?php echo esc_html($module->get_category()); ?></td>
                    <td>
                        <input type="text" name="module_plans[<?php echo esc_attr($slug); ?>]" 
                               value="<?php echo esc_attr($current_plan); ?>" 
                               placeholder="<?php esc_attr_e('e.g., pro, agency', 'lz-builder'); ?>"
                               class="regular-text">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="submit">
            <button type="submit" name="lz_save_plans" class="button button-primary">
                <?php esc_html_e('Save Plan Assignments', 'lz-builder'); ?>
            </button>
        </p>
    </form>
</div>
