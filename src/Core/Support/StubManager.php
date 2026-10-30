<?php

namespace Ahmed\DDD\Core\Support;

final class StubManager
{
    public function __construct(
        private string $basePath = __DIR__ . '/../Stubs/'
    ) {}

    public function get(string $name): string
    {
        $path = $this->basePath . $name . '.stub';

        
        if (!file_exists($path)) {
            throw new \Exception("Stub not found: {$name}");
            }

        return file_get_contents($path);
    }

    public function replace(array $data, string $stub): string
    {
        foreach ($data as $key => $value) {
            $stub = str_replace(['{{' . $key . '}}', '{{ ' . $key . ' }}'], $value, $stub);
        }

        return $stub;
    }

    public function make(string $stubName, array $data): string
    {
        return $this->replace(
            $data,
            $this->get($stubName)
        );
    }
}
