@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Chat') }}</div>

                <div class="card-body">
                    <!-- Include the app element where Vue will mount -->
                    <div id="app">
                        <!-- Use the chat-component tag to render the component -->
                        <chat-component :current-user-id="{{ auth()->id() }}"></chat-component>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
