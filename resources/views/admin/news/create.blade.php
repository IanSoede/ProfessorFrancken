@extends('admin.layout')
@section('page-title', 'News / create')

@section('content')
    {!!
       Form::model(
           $news,
           [
               'url' => action([\Francken\Association\News\Http\AdminNewsController::class, 'store']),
               'method' => 'post'
           ]
       )
    !!}
    <div class="row">
        <div class="col-md-8">
            @include('admin.news._form', ['action' => 'Save'])
        </div>

        <div class="col">
            @include('admin.news._author')
        </div>
    </div>
    {!! Form::close() !!}

@endsection
