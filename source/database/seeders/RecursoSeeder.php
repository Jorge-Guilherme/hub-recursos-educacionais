<?php

namespace Database\Seeders;

use App\Models\Recurso;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class RecursoSeeder extends Seeder
{
    public function run(): void
    {
        $phpTag = Tag::create(['nome' => 'PHP', 'slug' => 'php']);
        $laravelTag = Tag::create(['nome' => 'Laravel', 'slug' => 'laravel']);
        $dockerTag = Tag::create(['nome' => 'Docker', 'slug' => 'docker']);
        $angularTag = Tag::create(['nome' => 'Angular', 'slug' => 'angular']);
        $cleanTag = Tag::create(['nome' => 'Clean Architecture', 'slug' => 'clean-architecture']);

        $recurso1 = Recurso::create([
            'titulo' => 'Tutorial Completo de Laravel 11',
            'descricao' => 'Aprenda Laravel do zero ao avançado com este tutorial completo em vídeo.',
            'tipo' => 'video',
            'url' => 'https://youtube.com/watch?v=exemplo1',
        ]);
        $recurso1->tags()->attach([$phpTag->id, $laravelTag->id]);

        $recurso2 = Recurso::create([
            'titulo' => 'Guia de Clean Architecture em PHP',
            'descricao' => 'PDF detalhado explicando como implementar Clean Architecture em projetos PHP.',
            'tipo' => 'pdf',
            'url' => 'https://example.com/clean-architecture-guide.pdf',
        ]);
        $recurso2->tags()->attach([$phpTag->id, $cleanTag->id]);

        $recurso3 = Recurso::create([
            'titulo' => 'Docker para Desenvolvedores',
            'descricao' => 'Curso online sobre Docker, containerização e orquestração.',
            'tipo' => 'link',
            'url' => 'https://example.com/docker-course',
        ]);
        $recurso3->tags()->attach([$dockerTag->id]);

        $recurso4 = Recurso::create([
            'titulo' => 'Angular 17 - Novidades e Recursos',
            'descricao' => 'Vídeo apresentando as novidades do Angular 17.',
            'tipo' => 'video',
            'url' => 'https://youtube.com/watch?v=exemplo2',
        ]);
        $recurso4->tags()->attach([$angularTag->id]);

        $recurso5 = Recurso::create([
            'titulo' => 'Princípios SOLID Explicados',
            'descricao' => 'Artigo detalhado sobre os 5 princípios SOLID com exemplos práticos.',
            'tipo' => 'link',
            'url' => 'https://example.com/solid-principles',
        ]);
        $recurso5->tags()->attach([$phpTag->id, $cleanTag->id]);
    }
}
