<?php

namespace Ahmed\DDD\Core\Make\MakeRepository;

class MakeRepository
{
    public function __construct(
        protected MakeRepositoryContract $contract,
        protected MakeRepositoryEloquent $eloquent,
        protected MakeRepositoryBinding $binding,
    ) {}

    public function handle(
        string $domain,
        string $entity
    ): array {

        return [
            $this->contract
                ->generate(
                    $domain,
                    $entity
                ),

            $this->eloquent
                ->generate(
                    $domain,
                    $entity
                ),

            $this->binding
                ->generate(
                    $domain,
                    $entity
                ),
        ];
    }
}
