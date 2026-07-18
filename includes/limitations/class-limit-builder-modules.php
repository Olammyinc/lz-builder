<?php
namespace LzBuilder\Limitations;

use WP_Ultimo\Limitations\Limit;

class Limit_Builder_Modules extends Limit {
    protected string $id = 'lz_builder_modules';

    public function __construct($data = []) {
        $data = is_array($data) ? $data : [];
        $default_modules = [];

        if (class_exists('\\LzBuilder\\LZ_Module_Registry')) {
            $registry = \LzBuilder\LZ_Module_Registry::get_instance();
            foreach ($registry->get_all_modules() as $slug => $module) {
                $default_modules[$slug] = [
                    'visibility' => 'visible',
                    'behavior' => 'available',
                ];
            }
        }

        parent::__construct($data);

        foreach ($default_modules as $slug => $default) {
            if (!isset($this->{$slug})) {
                $this->{$slug} = $default;
            }
        }
    }

    public function default_state(): array {
        return [
            'enabled' => false,
            'limit' => new \stdClass(),
        ];
    }

    public function get_by_type(string $behavior = '', string $visibility = ''): array {
        $result = [];
        foreach ($this->get_available() as $slug => $data) {
            if ($behavior && ($data['behavior'] ?? '') !== $behavior) {
                continue;
            }
            if ($visibility && ($data['visibility'] ?? '') !== $visibility) {
                continue;
            }
            $result[$slug] = $data;
        }
        return $result;
    }

    public function get_available(): array {
        $available = [];
        $modules = array_keys(get_object_vars($this->get_limit()));
        foreach ($modules as $slug) {
            if (($this->{$slug}['visibility'] ?? '') !== 'hidden') {
                $available[$slug] = $this->{$slug};
            }
        }
        return $available;
    }

    public function is_module_enabled(string $module_slug): bool {
        return isset($this->{$module_slug}) && ($this->{$module_slug}['visibility'] ?? '') !== 'hidden';
    }
}
