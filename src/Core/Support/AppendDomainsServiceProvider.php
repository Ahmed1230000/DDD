<?php

namespace Ahmed\DDD\Core\Support;


class AppendDomainsServiceProvider
{
    public function append(): void
    {
        $path = base_path('bootstrap/providers.php');

        $provider =
            "App\\Common\\Providers\\DomainsServiceProvider::class,";

        $content = file_get_contents($path);

        if (str_contains($content, $provider)) {
            return;
        }

        $content = str_replace(
            '];',
            "    {$provider}\n];",
            $content
        );

        file_put_contents($path, $content);
    }
}
