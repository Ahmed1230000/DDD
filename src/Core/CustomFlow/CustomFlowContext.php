<?php

namespace Ahmed\DDD\Core\CustomFlow;

class CustomFlowContext
{
    public string $domain;

    public string $flow;

    public array $selectedModules = [];

    protected array $data = [];

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function remember(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->selectedModules, true);
    }
}
