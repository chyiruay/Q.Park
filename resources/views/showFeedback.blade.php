@extends('layoutadmin')
@section('content')
<div class="row">
    <div class="col-sm-1"></div>
    <div class="col-sm-10">
        <br></br>
        <div class="panel panel-default">
            <div class="table-wrapper">
                <div class="table-title">
                    <div class="row">
                        <div class="col-sm-8" style="color:white;"><h2>Feedback <b>List</b></h2></div>
                    </div>
                </div>
                <div class="panel-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <table class="table table-bordered" style="background-color:lightgray;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Content</th>   
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feedback as $feedback)
                            <tr>
                                <td>{{$feedback->id}}</td>
                                <td>{{$feedback->content}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
    <div class="col-sm-1"></div>
</div>


@endsection