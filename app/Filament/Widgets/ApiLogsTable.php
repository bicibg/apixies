<?php

namespace App\Filament\Widgets;

use App\Models\ApiEndpointLog;
use Filament\Tables;
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
                ->dateTime('Y‑m‑d H:i:s')
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

            /* ← NEW column */
            Tables\Columns\TextColumn::make('user_display')
                ->label('User')
                ->state(function (ApiEndpointLog $record): string {
                    return $record->user_name
                        ? "{$record->user_name} ({$record->user_id})"
                        : 'Guest';
                })
                ->searchable(),

            /* IP address */
            Tables\Columns\TextColumn::make('ip_address')
                ->label('IP')
                ->size('sm'),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;   // show 100 rows without a paginator
    }
}
