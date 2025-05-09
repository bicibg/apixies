@extends('layouts.app')

@section('title','Apixies API Documentation')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        @include('docs._hero')

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="flex border-b overflow-x-auto">
                <button class="tab-btn active px-6 py-3 font-medium text-[#0A2240]" data-tab="endpoints">API Endpoints</button>
                <button class="tab-btn px-6 py-3 font-medium text-gray-500"      data-tab="authentication">Authentication</button>
                <button class="tab-btn px-6 py-3 font-medium text-gray-500"      data-tab="examples">Examples</button>
                <button class="tab-btn px-6 py-3 font-medium text-gray-500"      data-tab="responses">Response Format</button>
                <button class="tab-btn px-6 py-3 font-medium text-gray-500"      data-tab="features">API Features</button>
            </div>

            @include('docs._endpoints')
            @include('docs._authentication')
            @include('docs._examples')
            @include('docs._responses')
            @include('docs._features')
        </div>
    </div>
@endsection
