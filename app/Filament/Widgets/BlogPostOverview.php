<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Forms\Components\Group;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BlogPostOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            //
            Stat::make('Draft', Post::query()->where('status', 'draft')->count())
                ->description('Nomber of draft post'),
            Stat::make('Reviewing', Post::query()->where('status', 'reviewing')->count())
                ->description('Number of post revised')
                ->color('info'),
            Stat::make('Published', Post::query()->where('status', 'published')->count())
                ->description('Number of post published')
                ->color('success'),
        ];
    }
}
