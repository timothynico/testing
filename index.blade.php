@extends('layouts.app')

@section('title', __('Feedback & Support'))

@section('header-left')
    <div>
        <h2 class="h5 fw-semibold mb-0">{{ __('Feedback & Support') }}</h2>
        <span class="text-muted small">{{ __('Customer support conversations and feedback management') }}</span>
    </div>
@endsection

@section('content')
    @livewire('chat.room')
@endsection

@push('scripts')
    {{-- Any additional JavaScript if needed --}}
@endpush
