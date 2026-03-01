<?php

namespace Infrastructure\Providers;

use App\Models\Recurso as RecursoModel;
use App\Models\Tag as TagModel;
use App\Models\Grupo as GrupoModel;
use Domain\Contracts\RecursoRepositoryInterface;
use Domain\Contracts\TagRepositoryInterface;
use Domain\Contracts\GrupoRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Persistence\Repositories\EloquentRecursoRepository;
use Infrastructure\Persistence\Repositories\EloquentTagRepository;
use Infrastructure\Persistence\Repositories\EloquentGrupoRepository;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            RecursoRepositoryInterface::class,
            function ($app) {
                return new EloquentRecursoRepository(new RecursoModel());
            }
        );

        $this->app->bind(
            TagRepositoryInterface::class,
            function ($app) {
                return new EloquentTagRepository(new TagModel());
            }
        );

        $this->app->bind(
            GrupoRepositoryInterface::class,
            function ($app) {
                return new EloquentGrupoRepository(new GrupoModel());
            }
        );
    }

    public function boot(): void
    {
        //
    }
}
