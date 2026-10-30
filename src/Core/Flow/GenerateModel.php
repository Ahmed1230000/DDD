<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{
    FileManager,
    NamingEngine
};
use Illuminate\Support\Facades\Artisan;

class GenerateModel
{
    public function __construct(
        public FileManager $fileManager
    ) {}

    public function generate(
        string $entity
    ): array {

        $naming = new NamingEngine('', $entity);

        /*
        |--------------------------------------------------------------------------
        | Model Path
        |--------------------------------------------------------------------------
        */

        $modelPath = app_path(
            "Models/{$naming->entity()}.php"
        );

        /*
        |--------------------------------------------------------------------------
        | Already Exists
        |--------------------------------------------------------------------------
        */

        if ($this->fileManager->exists($modelPath)) {

            return [
                'type' => 'MODEL',
                'name' => $naming->entity(),
                'created' => false
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Create Model
        |--------------------------------------------------------------------------
        */

        $createModel = Artisan::call('make:model', [
            'name' => $naming->entity(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        if ($createModel === 0) {

            return [
                'type' => 'MODEL',
                'name' => $naming->entity(),
                'created' => true
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Failed
        |--------------------------------------------------------------------------
        */

        return [
            'type' => 'MODEL',
            'name' => $naming->entity(),
            'created' => false
        ];
    }
}
