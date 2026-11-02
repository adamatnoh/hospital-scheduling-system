@extends('layouts.hss')
@section('heading', $department->name.' rules')
@section('content')
    <section class="hss-card">
        <livewire:rules-form :department="$department" />
    </section>
@endsection
