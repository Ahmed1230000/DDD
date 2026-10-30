<?php

namespace Ahmed\DDD\Core\Make;

use Ahmed\DDD\Core\Support\{
    FileManager,
    StubManager
};

class MakePolicy
{
    public function __construct(
        protected FileManager $fileManager,
        protected StubManager $stubManager
    ) {}

    public function generate(
        string $domain,
        string $flow = null,
        string $policy,
        string $authModel,
        string $model
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Names
        |--------------------------------------------------------------------------
        */

        $className = ucwords($policy);
        $authModel = ucwords($authModel);
        $model     = ucwords($model);

        $className = str_ends_with($policy, 'Policy')
            ? $policy
            : $policy . 'Policy';

        /*
        |--------------------------------------------------------------------------
        | Namespace
        |--------------------------------------------------------------------------
        */

        $namespace = "App\\Domains\\{$domain}\\Policies\\{$flow}";

        $authModelNamespace = "App\\Models\\{$authModel}";

        $modelNamespace = "App\\Models\\{$model}";

        /*
        |--------------------------------------------------------------------------
        | Variables
        |
        |--------------------------------------------------------------------------
        */


        $authModel = "{$authModel}";
        $model     = "{$model}";

        $authModelVariable = lcfirst($authModel);
        $modelVariable     = lcfirst($model);




        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        */

        $path = app_path(
            "Domains/{$domain}/Policies/{$flow}/{$className}.php"
        );


        /*
        |--------------------------------------------------------------------------
        | Exists
        |--------------------------------------------------------------------------
        */

        if ($this->fileManager->exists($path)) {

            return [
                'type'    => 'Policy',
                'name'    => $className,
                'created' => false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Content
        |--------------------------------------------------------------------------
        */

        $content = $this->stubManager->make(
            'MakePolicy',
            [
                'namespace'            => $namespace,
                'class'                => $className,
                'auth_model_namespace' => $authModelNamespace,
                'model_namespace'      => $modelNamespace,
                'auth_model'           => $authModel,
                'model'                => $model,
                'authModelVariable'    => $authModelVariable,
                'modelVariable'        => $modelVariable,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Generate
        |--------------------------------------------------------------------------
        */

        $this->fileManager->put(
            $path,
            $content
        );

        /*
        |--------------------------------------------------------------------------
        | Register Policy
        |--------------------------------------------------------------------------
        */

        $providerPath = app_path(
            "Domains/{$domain}/Providers/{$domain}DomainServiceProvider.php"
        );

        $policyNamespace =
            "App\\Domains\\{$domain}\\Policies\\{$flow}\\{$className}";

        $this->fileManager->appendPolicyToDomainServiceProvider(
            $providerPath,
            $modelNamespace,
            $policyNamespace,
            $model,
            $className
        );

        return [
            'type'    => 'Policy',
            'name'    => $className,
            'created' => true,
        ];
    }
}
