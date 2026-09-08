@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Dashboard Mascotas</h3>
    <p>Módulo de mascotas en construcción.</p>

    <a href="{{ url()->previous() }}" class="btn btn-secondary">
        Volver
    </a>
</div>
@endsection
