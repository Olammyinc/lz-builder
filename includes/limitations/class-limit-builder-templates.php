<?php
namespace LzBuilder\Limitations;

use WP_Ultimo\Limitations\Limit;

class Limit_Builder_Templates extends Limit {
    protected string $id = 'lz_builder_templates';

    public function default_state(): array {
        return [
            'enabled' => false,
            'limit' => null,
        ];
    }

    public function get_available_categories(): array {
        $limit = $this->get_limit();
        if (!is_object($limit)) {
            return [];
        }
        $available = [];
        $categories = get_object_vars($limit);
        foreach ($categories as $category => $data) {
            if (($data->visibility ?? 'visible') !== 'hidden') {
                $available[] = $category;
            }
        }
        return $available;
    }

    public function is_category_enabled(string $category): bool {
        $limit = $this->get_limit();
        return !isset($limit->{$category}) || ($limit->{$category}->visibility ?? 'visible') !== 'hidden';
    }
}
