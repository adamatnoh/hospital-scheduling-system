@extends('layouts.hss')
@section('heading', $department->name.' rules')
@section('content')
    <x-ui.page-header :title="$department->name.' roster rules'" icon="bi-sliders" subtitle="Limits the generator follows when it builds this department's month.">
        <x-slot:actions>
            <x-ui.button variant="outline" icon="bi-arrow-left" :href="route('generate.show', 'on_call').'?department_id='.$department->id">Back to generator</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>
    <x-ui.card>
        <livewire:rules-form :department="$department" />
    </x-ui.card>
@endsection
