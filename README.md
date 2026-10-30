# Ahmed Laravel DDD

A Laravel package that provides a Domain-Driven Design-oriented project
structure and Artisan generators for creating domains, flows, and their
supporting application layers.

The package helps you organize Laravel applications around business
domains and generate common building blocks through interactive console
commands.

> **Package:** `ahmed/laravel-ddd`\
> **Laravel service provider:** `Ahmed\DDD\Providers\DDDServiceProvider`

------------------------------------------------------------------------

## Table of Contents

-   [Overview](#overview)
-   [Features](#features)
-   [Requirements](#requirements)
-   [Installation](#installation)
-   [Initial Setup](#initial-setup)
-   [Project Structure](#project-structure)
-   [Artisan Commands](#artisan-commands)
-   [Custom Flow Generator](#custom-flow-generator)
-   [Available Custom Flow Modules](#available-custom-flow-modules)
-   [How Generation Works](#how-generation-works)
-   [Published Application Support
    Files](#published-application-support-files)
-   [Development with a Local Path
    Repository](#development-with-a-local-path-repository)
-   [Troubleshooting](#troubleshooting)
-   [License](#license)

------------------------------------------------------------------------

## Overview

Ahmed Laravel DDD is designed to help structure a Laravel application
around business domains instead of placing all application code in
framework-wide folders.

A domain can contain multiple flows. Each flow represents a business
operation or use case and can have its own generated files.

For example:

``` text
app/
└── Domains/
    └── Authorization/
        ├── Entities/
        ├── DTOs/
        ├── UseCases/
        ├── Repositories/
        │   ├── Contracts/
        │   └── Eloquent/
        ├── ValueObjects/
        ├── Http/
        │   ├── Controllers/
        │   ├── Requests/
        │   └── Resources/
        ├── Database/
        │   ├── Migrations/
        │   ├── Factories/
        │   └── Seeders/
        ├── Routes/
        │   ├── api/
        │   └── web/
        ├── Providers/
        └── Exceptions/
```

The exact files created depend on the command and the modules selected
during generation.

## Features

The package currently includes:

-   **Domain generation** --- creates a domain directory structure and
    its route/provider support.
-   **Flow generation** --- interactively generates a flow for an
    existing domain.
-   **Custom flow generation** --- lets the developer select the modules
    needed for a particular flow.
-   **Execution planning** --- orders selected custom-flow modules
    before executing their generators.
-   **Module guidance** --- displays modules commonly used together;
    these are suggestions, not a requirement to generate every related
    module.
-   **Code generators and configurators** --- generates and configures
    common Laravel/DDD files using package stubs.
-   **Event/listener generation command** --- provides a command for
    generating domain events and listeners.
-   **Laravel integration** --- registers the package service provider
    through Composer package discovery.

## Requirements

The package is built for Laravel applications and uses Laravel framework
classes and Artisan.

The package metadata currently does not declare explicit Composer
`require` constraints. The consuming Laravel application therefore needs
to provide the framework and any third-party libraries used by the
generated or published support code.

Before publishing a release, verify the package's actual dependency
requirements against the Laravel versions you intend to support.

## Installation

After the package is available through Packagist, install it in a
Laravel application:

``` bash
composer require ahmed/laravel-ddd
```

Composer package discovery registers:

``` php
Ahmed\DDD\Providers\DDDServiceProvider
```

If package discovery is disabled in the application, register the
provider using the mechanism supported by that Laravel application.

## Initial Setup

Run the package installation command:

``` bash
php artisan ddd:install
```

The installer performs the following actions:

1.  Publishes the package support structure using the `ddd` publish tag.
2.  Appends the generated `DomainsServiceProvider` to the Laravel
    providers configuration.
3.  Prepares the base controller authorization support through the
    package's file manager.
4.  Writes an installation marker at `storage/app/.ddd-installed`.

The installer reports whether the operation succeeds or fails.

You can also publish the support files directly, without running the
installer:

``` bash
php artisan vendor:publish --tag=ddd
```

The installation command is the intended first step before generating
domains and flows.

## Project Structure

The package source is organized under `src/`.

``` text
src/
├── Core/
│   ├── Configurators/
│   ├── Console/
│   │   └── Commands/
│   ├── Contracts/
│   ├── CustomFlow/
│   ├── domain/
│   ├── ExecutionPlanBuilder/
│   ├── Flow/
│   ├── GeneratorExecutor/
│   ├── Make/
│   ├── Rules/
│   └── Support/
├── Providers/
│   └── DDDServiceProvider.php
└── app/
    ├── Common/
    ├── Domains/
    ├── Infrastructure/
    └── Providers/
```

### Core areas

  -----------------------------------------------------------------------
  Area                                Responsibility
  ----------------------------------- -----------------------------------
  `Configurators`                     Collects configuration and naming
                                      information needed by generators.

  `Console/Commands`                  Contains the Artisan commands
                                      exposed by the package.

  `CustomFlow`                        Holds context for custom flow
                                      generation.

  `ExecutionPlanBuilder`              Sorts selected modules into the
                                      configured execution order.

  `Flow`                              Contains the individual generation
                                      operations used by flow generation.

  `GeneratorExecutor`                 Executes configured generators for
                                      selected modules.

  `Make`                              Contains classes responsible for
                                      creating generated files.

  `Rules`                             Defines the available custom-flow
                                      modules and their configurators,
                                      executors, types, and module
                                      guidance.

  `Support`                           Provides file, naming, validation,
                                      installation, and console support
                                      utilities.

  `Providers`                         Contains the Laravel service
                                      provider.

  `app`                               Contains support files that are
                                      published into the consuming
                                      Laravel application.
  -----------------------------------------------------------------------

## Artisan Commands

The following commands are registered by the package service provider in
the current source:

  ------------------------------------------------------------------------
  Command                              Description
  ------------------------------------ -----------------------------------
  `php artisan ddd:install`            Publishes and prepares the DDD
                                       application support structure.

  `php artisan ddd:make:domain`        Interactively creates a domain.

  `php artisan ddd:make:flow`          Interactively generates a flow for
                                       an existing domain.

  `php artisan ddd:make:custom-flow`   Generates a flow from a
                                       developer-selected set of modules.

  `php artisan ddd:flow:event`         Generates domain events and
                                       listeners.
  ------------------------------------------------------------------------

### Create a domain

``` bash
php artisan ddd:make:domain
```

The command asks for a domain name. It creates the domain's base
folders, including areas for entities, DTOs, use cases, repositories,
value objects, HTTP files, database files, routes, providers, and
exceptions.

It also asks whether optional `Services` and `Policies` modules should
be created.

You can provide the domain name as an argument:

``` bash
php artisan ddd:make:domain Authorization
```

### Generate a flow

``` bash
php artisan ddd:make:flow
```

The command guides you through selecting an existing domain and entering
a flow/entity name. It then asks which parts of the flow should be
generated, including whether to enable the API layer and which HTTP
features to include.

The command's prompts determine the generated files and options. Follow
the interactive summary before confirming generation.

## Custom Flow Generator

The custom flow generator is intended for developers who want to choose
the building blocks of a flow instead of generating every possible
component.

Run:

``` bash
php artisan ddd:make:custom-flow
```

The command guides the developer through these steps:

1.  Enter a domain that already exists.
2.  Enter a flow name. If the flow does not exist, the command asks
    whether it should be created.
3.  Select the required modules using the displayed module indexes.
4.  Build a runtime context containing the domain, flow, and selected
    modules.
5.  Build an execution pipeline using the package's execution order.
6.  Display module guidance, including modules commonly used together.
7.  Ask the configurators for the information required by the selected
    modules.
8.  Display a summary and ask for confirmation.
9.  Execute the generators and report created and skipped files.

The module guidance is informational. The command explicitly allows the
developer to continue without generating modules listed as commonly used
with a selected module.

### Example interaction

The exact indexes are displayed by the command at runtime. A typical
interaction follows this shape:

``` text
Domain: Authorization
Flow Name: Register

Select Modules
[0] migration
[1] factory
[2] seeder
[3] entity
...

Select modules (comma separated): 3, ...
```

Use the indexes printed by your installed package version; do not assume
that indexes remain unchanged between releases.

## Available Custom Flow Modules

The current rules configuration defines these modules:

  -----------------------------------------------------------------------
  Module                              Purpose
  ----------------------------------- -----------------------------------
  `migration`                         Generates a database migration.

  `factory`                           Generates a model factory.

  `seeder`                            Generates a database seeder.

  `entity`                            Generates a domain entity.

  `repository_interface`              Provides the repository contract
                                      module.

  `repository`                        Generates the repository
                                      implementation layer.

  `policy`                            Generates a policy.

  `enum`                              Generates an enum.

  `exception`                         Generates an exception.

  `dto`                               Generates a data transfer object.

  `usecase`                           Generates a use case. Supported
                                      types in the current rules are
                                      `create`, `update`, `delete`, and
                                      `custom`.

  `controller`                        Generates a controller. Supported
                                      types in the current rules are
                                      `api`, `invoke`, and `empty`.

  `request`                           Generates a request.

  `resource`                          Generates a resource.

  `query_builder`                     Generates a query builder.
  -----------------------------------------------------------------------

The execution plan currently orders modules as follows:

1.  Migration
2.  Factory
3.  Seeder
4.  Entity
5.  Repository interface
6.  Repository
7.  Enum
8.  Exception
9.  DTO
10. Use case
11. Request
12. Resource
13. Controller
14. Query builder
15. Policy

The order is defined in `ExecutionPlanBuilder`. The `model` module is
currently commented out in that execution order and is not part of the
current `Rules::all()` module list.

## How Generation Works

The custom-flow implementation separates the generation process into
distinct responsibilities:

-   **Rules** describe each module and point to its configurator and
    executor.
-   **Configurators** collect the module-specific inputs from the
    console and store data in the custom-flow context.
-   **Custom flow context** carries the selected domain, flow, modules,
    and runtime data through the generation process.
-   **Execution plan builder** orders the selected modules.
-   **Executors** run the corresponding generation operations.
-   **Make classes and stubs** are used by the generation layer to
    produce files.

This separation allows the custom-flow command to coordinate generation
without putting every module's implementation directly inside the
command.

## Published Application Support Files

The service provider publishes these package directories into the
Laravel application under the `ddd` tag:

  Package source             Application destination
  -------------------------- -------------------------
  `src/app/Common`           `app/Common`
  `src/app/Domains`          `app/Domains`
  `src/app/Infrastructure`   `app/Infrastructure`
  `src/app/Providers`        `app/Providers`

The published files are application support files. They are not the same
as the package's own `src/Core` implementation.

The published support structure includes common exceptions, traits,
value-object base classes and examples, domain/provider support, and
infrastructure support such as the base query builder.

## Development with a Local Path Repository

When developing the package locally alongside a Laravel application,
Composer can load it through a path repository.

For example, if the application and package are sibling directories:

``` text
workspace/
├── laravel-app/
└── laravel-ddd/
```

Add this repository to the Laravel application's `composer.json`:

``` json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-ddd"
        }
    ]
}
```

The application can then require the package:

``` bash
composer require ahmed/laravel-ddd
```

A path repository is useful during local package development. For normal
installation on another machine or server, publish the package to a Git
repository and Packagist, then install it through Composer without
relying on a local sibling directory.

## Troubleshooting

### Artisan commands are not listed

Confirm that Composer installed the package and that Laravel package
discovery is enabled. The package provider registers its currently
exposed commands while the application is running in the console.

You can inspect available commands with:

``` bash
php artisan list
```

### The package support files are missing

Run the installer:

``` bash
php artisan ddd:install
```

Or publish the support files directly:

``` bash
php artisan vendor:publish --tag=ddd
```

### A custom flow cannot find a domain

The custom-flow command validates the entered domain against the
application's existing domains. Create the domain first:

``` bash
php artisan ddd:make:domain
```

Then run:

``` bash
php artisan ddd:make:custom-flow
```

### A generated file is skipped

The custom-flow command reports created and skipped files. Review the
report and the target domain/flow directories before retrying, so
existing files are not unintentionally overwritten.

------------------------------------------------------------------------

## License

This package declares the MIT license in its Composer metadata. Include
the corresponding `LICENSE` file in the repository when publishing the
package.
