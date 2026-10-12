@extends('layouts.app')

@section('title', 'Add proposal — Research Repository')

@section('content')
    <div class="content-head">
        <button type="button" class="menu-btn" id="menu-btn" aria-expanded="false" aria-controls="sidebar">Menu</button>
        <h1 class="view-title">Add proposal</h1>
    </div>

    <form class="panel" method="POST" action="{{ route('proposals.store') }}" novalidate>
        @include('proposals._form', ['cancelUrl' => route('proposals.index'), 'submitLabel' => 'Save proposal'])
    </form>
@endsection
