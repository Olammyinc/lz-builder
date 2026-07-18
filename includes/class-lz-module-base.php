<?php
namespace LzBuilder;

abstract class LZ_Module_Base {
    protected string $slug = '';
    protected string $name = '';
    protected string $description = '';
    protected string $category = 'content';
    protected string $icon = '';
    protected string $group = '';
    protected ?array $required_plan = null;
    protected bool $partial_refresh = true;
    protected bool $include_wrapper = true;
    protected string $dir = '';
    protected string $url = '';

    final public function __construct(array $args = []) {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    final public function set_dir(string $dir): void {
        $this->dir = trailingslashit($dir);
        $this->url = trailingslashit(LZ_BUILDER_URL . 'includes/modules/' . $this->slug);
    }

    abstract public function get_settings_form(): array;
    abstract public function render(\stdClass $node, \stdClass $settings): string;

    public function get_css(\stdClass $node, \stdClass $settings): array {
        return [];
    }

    public function enqueue_scripts(): void {}

    public function enqueue_ui_scripts(): void {}

    public function update(\stdClass $settings): \stdClass {
        return $settings;
    }

    public function delete(): void {}

    public function filter_settings(\stdClass $settings): \stdClass {
        return $settings;
    }

    final public function get_slug(): string {
        return $this->slug;
    }

    final public function get_name(): string {
        return $this->name;
    }

    final public function get_description(): string {
        return $this->description;
    }

    final public function get_category(): string {
        return $this->category;
    }

    final public function get_icon(): string {
        return $this->icon;
    }

    final public function get_required_plan(): ?array {
        return $this->required_plan ? (array) $this->required_plan : null;
    }

    final public function get_dir(): string {
        return $this->dir;
    }

    final public function get_url(): string {
        return $this->url;
    }

    final public function path(string $path = ''): string {
        return trailingslashit($this->dir) . $path;
    }

    final public function url(string $path = ''): string {
        return trailingslashit($this->url) . $path;
    }
}
