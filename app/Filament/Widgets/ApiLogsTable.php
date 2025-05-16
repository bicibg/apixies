<?php

namespace App\Filament\Widgets;

use App\Models\ApiEndpointLog;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

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

            /* User info - Updated to use relationship */
            Tables\Columns\TextColumn::make('user_display')
                ->label('User')
                ->state(function (ApiEndpointLog $record): string {
                    if (!$record->user_id) {
                        // Identify the type of call based on the pattern we established
                        if (!is_null($record->sandbox_token_id)) {
                            return 'Sandbox Call';
                        } else if (str_contains($record->endpoint, 'health')){
                            return 'Health Check';
                        } else if (str_contains($record->endpoint, 'ready')){
                            return 'Readiness Check';
                        }
                        return 'Guest';
                    }

                    // Efficiently fetch user name from the users table
                    $user = DB::table('users')
                        ->select('name')
                        ->where('id', $record->user_id)
                        ->first();

                    return $user
                        ? "{$user->name} ({$record->user_id})"
                        : "Unknown User ({$record->user_id})";
                })
                ->searchable(),

            /* Sandbox Flag - Updated to check only sandbox_token_id */
            Tables\Columns\IconColumn::make('sandbox_indicator')
                ->label('Sandbox')
                ->state(function (ApiEndpointLog $record): bool {
                    // Show sandbox icon if sandbox_token_id is not null
                    return !is_null($record->sandbox_token_id);
                })
                ->boolean()
                ->trueIcon('heroicon-o-beaker')
                ->falseIcon('')
                ->size('sm'),

            /* IP address - now may be null for sandbox/health calls */
            Tables\Columns\TextColumn::make('ip_address')
                ->label('IP')
                ->size('sm')
                ->placeholder('N/A'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            // Environment filter - now with enhanced options
            SelectFilter::make('call_type')
                ->label('Call Type')
                ->query(function (Builder $query, array $data): Builder {
                    if (!isset($data['value']) || $data['value'] === '') {
                        return $query;
                    }

                    return match ($data['value']) {
                        'sandbox' => $query->whereNotNull('sandbox_token_id'),
                        'health' => $query->where(function($q) {
                            $q->where('endpoint', 'like', '%health%')
                                ->orWhere('endpoint', 'like', '%ready%');
                        }),
                        'api' => $query->where(function($q) {
                            $q->whereNull('sandbox_token_id')
                                ->where('endpoint', 'not like', '%health%')
                                ->where('endpoint', 'not like', '%ready%');
                        }),
                        default => $query,
                    };
                })
                ->options([
                    'api' => 'API Calls',
                    'sandbox' => 'Sandbox Calls',
                    'health' => 'Health/Ready Checks',
                ])
                ->placeholder('All Call Types'),

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
