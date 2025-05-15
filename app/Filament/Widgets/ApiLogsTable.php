<?php

namespace App\Filament\Widgets;

use App\Models\ApiEndpointLog;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class ApiLogsTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    /**
     * Latest 100 request logs.
     */
    protected function getTableQuery(): Builder|Relation|null
    {
        return ApiEndpointLog::query()
            ->latest()
            ->limit(100);
    }

    protected function getTableColumns(): array
    {
        return [
            /* Timestamp */
            Tables\Columns\TextColumn::make('created_at')
                ->label('When')
                ->dateTime('Y‑m‑d H:i:s')
                ->sortable(),

            /* HTTP verb */
            Tables\Columns\TextColumn::make('method')
                ->label('Verb')
                ->size('sm'),

            /* Endpoint path */
            Tables\Columns\TextColumn::make('endpoint')
                ->wrap()
                ->size('sm'),

            /* Latency */
            Tables\Columns\TextColumn::make('latency_ms')
                ->label('ms')
                ->numeric()
                ->sortable(),

            /* User info */
            Tables\Columns\TextColumn::make('user_display')
                ->label('User')
                ->state(function (ApiEndpointLog $record): string {
                    return $record->user_name
                        ? "{$record->user_name} ({$record->user_id})"
                        : 'Guest';
                })
                ->searchable(),

            /* Sandbox Flag */
            Tables\Columns\IconColumn::make('is_sandbox')
                ->label('Sandbox')
                ->boolean()
                ->trueIcon('heroicon-o-beaker')
                ->falseIcon('')
                ->size('sm'),

            /* IP address */
            Tables\Columns\TextColumn::make('ip_address')
                ->label('IP')
                ->size('sm'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            // Environment filter - production vs sandbox
            SelectFilter::make('is_sandbox')
                ->label('Environment')
                ->options([
                    '0' => 'Production',
                    '1' => 'Sandbox',
                ])
                ->placeholder('All Environments'),

            // Endpoint type filter
            Filter::make('endpoint_type')
                ->label('Endpoint Type')
                ->form([
                    // Use Filament\Forms\Components\Select for form filters
                    Select::make('endpoint_category')
                        ->label('Endpoint Category')
                        ->options([
                            'health_ready' => 'Health & Ready',
                            'inspector' => 'Inspector APIs',
                            'other' => 'Other APIs',
                        ])
                        ->placeholder('All Endpoints'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (empty($data['endpoint_category'])) {
                        return $query;
                    }

                    return match ($data['endpoint_category']) {
                        'health_ready' => $query->where(function ($query) {
                            $query->where('endpoint', 'like', '%health%')
                                ->orWhere('endpoint', 'like', '%ready%');
                        }),
                        'inspector' => $query->where('endpoint', 'like', '%inspect%'),
                        'other' => $query->where(function ($query) {
                            $query->where('endpoint', 'not like', '%health%')
                                ->where('endpoint', 'not like', '%ready%')
                                ->where('endpoint', 'not like', '%inspect%');
                        }),
                        default => $query,
                    };
                }),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;  // Enable pagination for better performance
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [25, 50, 100];
    }
}
