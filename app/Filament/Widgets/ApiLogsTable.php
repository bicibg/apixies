<?php

namespace App\Filament\Widgets;

use App\Models\ApiEndpointLog;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
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

            /* HTTP Status Code - Simple numerical display */
            Tables\Columns\TextColumn::make('response_code')
                ->label('Response code')
                ->sortable()
                ->color(fn (int $state): string => match(true) {
                    $state >= 200 && $state < 300 => 'success',
                    $state >= 300 && $state < 400 => 'warning',
                    $state >= 400 => 'danger',
                    default => 'gray',
                }),

            /* Latency */
            Tables\Columns\TextColumn::make('latency_ms')
                ->label('ms')
                ->numeric()
                ->sortable()
                ->color(fn (int $state): string => match(true) {
                    $state < 100 => 'success',
                    $state < 500 => 'primary',
                    $state < 1000 => 'warning',
                    default => 'danger',
                }),

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
            // Date Range Filter with two separate date pickers
            Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from')
                        ->label('From Date'),
                    DatePicker::make('created_until')
                        ->label('To Date'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                }),

            // Status filter - added for filtering by HTTP status codes
            SelectFilter::make('response_code')
                ->label('Status')
                ->options([
                    '2xx' => '2xx (Success)',
                    '3xx' => '3xx (Redirection)',
                    '4xx' => '4xx (Client Error)',
                    '5xx' => '5xx (Server Error)',
                    '200' => '200 (OK)',
                    '201' => '201 (Created)',
                    '400' => '400 (Bad Request)',
                    '401' => '401 (Unauthorized)',
                    '403' => '403 (Forbidden)',
                    '404' => '404 (Not Found)',
                    '422' => '422 (Unprocessable)',
                    '500' => '500 (Server Error)',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (!isset($data['value']) || $data['value'] === '') {
                        return $query;
                    }

                    return match ($data['value']) {
                        '2xx' => $query->whereBetween('response_code', [200, 299]),
                        '3xx' => $query->whereBetween('response_code', [300, 399]),
                        '4xx' => $query->whereBetween('response_code', [400, 499]),
                        '5xx' => $query->whereBetween('response_code', [500, 599]),
                        default => $query->where('response_code', $data['value']),
                    };
                })
                ->placeholder('All Status Codes'),

            // Latency Filter
            SelectFilter::make('latency')
                ->label('Response Time')
                ->options([
                    'fast' => 'Fast (<100ms)',
                    'medium' => 'Medium (100-500ms)',
                    'slow' => 'Slow (500-1000ms)',
                    'very_slow' => 'Very Slow (>1000ms)',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (!isset($data['value']) || $data['value'] === '') {
                        return $query;
                    }

                    return match ($data['value']) {
                        'fast' => $query->where('latency_ms', '<', 100),
                        'medium' => $query->whereBetween('latency_ms', [100, 500]),
                        'slow' => $query->whereBetween('latency_ms', [500, 1000]),
                        'very_slow' => $query->where('latency_ms', '>', 1000),
                        default => $query,
                    };
                })
                ->placeholder('All Response Times'),

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
                        'errors' => $query->where('response_code', '>=', 400),
                        default => $query,
                    };
                })
                ->options([
                    'api' => 'API Calls',
                    'sandbox' => 'Sandbox Calls',
                    'health' => 'Health/Ready Checks',
                    'errors' => 'Error Responses',
                ])
                ->placeholder('All Call Types'),

            // User Filter
            SelectFilter::make('user_id')
                ->label('User')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->placeholder('All Users'),

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

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view_details')
                ->label('Details')
                ->color('secondary')
                ->icon('heroicon-s-eye')
                ->modalHeading(fn (ApiEndpointLog $record) => "Request Details: {$record->endpoint}")
                ->modalContent(function (ApiEndpointLog $record) {
                    $details = [];

                    // Request details
                    $details[] = "<div class='font-semibold mb-1'>Request</div>";
                    $details[] = "<div class='grid grid-cols-2 gap-1 mb-3'>";
                    $details[] = "  <div class='text-gray-500'>Endpoint:</div><div>{$record->endpoint}</div>";
                    $details[] = "  <div class='text-gray-500'>Method:</div><div>{$record->method}</div>";
                    $details[] = "  <div class='text-gray-500'>Time:</div><div>{$record->created_at}</div>";

                    if ($record->request_params) {
                        try {
                            $params = json_decode($record->request_params, true);
                            $paramHtml = "<pre class='bg-gray-100 p-2 rounded text-xs max-h-40 overflow-auto'>" .
                                json_encode($params, JSON_PRETTY_PRINT) .
                                "</pre>";
                            $details[] = "  <div class='text-gray-500'>Parameters:</div><div class='col-span-2 mt-1'>{$paramHtml}</div>";
                        } catch (\Exception $e) {
                            $details[] = "  <div class='text-gray-500'>Parameters:</div><div>{$record->request_params}</div>";
                        }
                    }
                    $details[] = "</div>";

                    // Response details
                    $details[] = "<div class='font-semibold mb-1'>Response</div>";
                    $details[] = "<div class='grid grid-cols-2 gap-1 mb-3'>";
                    $details[] = "  <div class='text-gray-500'>Status:</div><div>{$record->response_code}</div>";
                    $details[] = "  <div class='text-gray-500'>Latency:</div><div>{$record->latency_ms}ms</div>";
                    $details[] = "</div>";

                    // Client details
                    $details[] = "<div class='font-semibold mb-1'>Client</div>";
                    $details[] = "<div class='grid grid-cols-2 gap-1'>";

                    if ($record->user_id) {
                        $user = DB::table('users')
                            ->select('name', 'email')
                            ->where('id', $record->user_id)
                            ->first();

                        $userName = $user ? $user->name : 'Unknown User';
                        $userEmail = $user ? $user->email : '';

                        $details[] = "  <div class='text-gray-500'>User:</div><div>{$userName}</div>";
                        if ($userEmail) {
                            $details[] = "  <div class='text-gray-500'>Email:</div><div>{$userEmail}</div>";
                        }
                    } else if ($record->sandbox_token_id) {
                        $details[] = "  <div class='text-gray-500'>Client:</div><div>Sandbox</div>";
                    } else {
                        $details[] = "  <div class='text-gray-500'>Client:</div><div>Anonymous</div>";
                    }

                    if ($record->ip_address) {
                        $details[] = "  <div class='text-gray-500'>IP Address:</div><div>{$record->ip_address}</div>";
                    }

                    if ($record->user_agent) {
                        $details[] = "  <div class='text-gray-500'>User Agent:</div><div class='col-span-2 mt-1 text-xs'>{$record->user_agent}</div>";
                    }

                    $details[] = "</div>";

                    return new \Illuminate\Support\HtmlString(implode("\n", $details));
                })
                ->modalWidth('md')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
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
