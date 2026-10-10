<?php

declare(strict_types=1);

use App\Presentation\Api;
use App\Presentation\Api\Shared\Access\CheckTaskOwner;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Auth\Middleware\Authentication;

return [
    Route::post('/login')
        ->action(Api\Login\Action::class)
        ->name('api.login'),

    Route::post('/register')
        ->action(Api\Register\Action::class)
        ->name('api.register'),

    Route::post('/refresh')
        ->action(Api\Auth\Refresh\Action::class)
        ->name('api.refresh'),

    Route::post('/logout')
        ->action(Api\Auth\Logout\Action::class)
        ->name('api.logout'),

    Group::create()
        ->middleware(Authentication::class)
        ->routes(
            Route::get('/tasks')
                ->action(Api\Task\ListTasks\Action::class)
                ->name('tasks.list'),
            Route::post('/tasks')
                ->action(Api\Task\CreateTask\Action::class)
                ->name('tasks.create'),
            Group::create()
                ->middleware(CheckTaskOwner::class)
                ->routes(
                    Route::delete('/tasks/{id}')
                        ->action(Api\Task\DeleteTask\Action::class)
                        ->name('tasks.delete'),
                    Route::put('/tasks/{id}')
                        ->action(Api\Task\UpdateTask\Action::class)
                        ->name('tasks.update'),
                ),
        )
];
