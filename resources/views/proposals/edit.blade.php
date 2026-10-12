@extends('layouts.app')

@section('title', 'Edit proposal — Research Repository')

@section('content')
    <div class="content-head">
        <button type="button" class="menu-btn" id="menu-btn" aria-expanded="false" aria-controls="sidebar">Menu</button>
        <h1 class="view-title">Edit proposal</h1>
    </div>

    <form class="panel" method="POST" action="{{ route('proposals.update', $proposal->id) }}" novalidate>
        @method('PUT')
        @include('proposals._form', ['proposal' => $proposal, 'cancelUrl' => route('proposals.show', $proposal->id), 'submitLabel' => 'Save changes'])
    </form>
@endsection
