@extends('layout')
@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<div class="row">
    <div class="col-sm-1"></div>
    <div class="col-sm-10">
        <br></br>
        <div class="table-wrapper">
            <div class="table-title">
                <div class="row">
                    <div class="col-sm-8" style="color:white;"><h2>Payment History</h2></div>
                    <div class="col-sm-4">
                    </div>
                </div>
            </div>
            <table class="table table-bordered" style="background-color:lightgray; margin-bottom:50px;">
                <thead>
                    <tr>
                        &nbsp;
                        <th>Payment ID</th>
                        <th>Payment Date & time</th>
                        <th>Amount</th>                           
                    </tr>
                </thead>

                <tbody>

                    @foreach($orders as $order)
                    <tr>
                        <td>{{$order->id}}</td>
                        <td>{{$order->created_at}}</td>                        
                        <td>RM {{$order->amount}}</td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
    <div class="col-sm-1"></div> 
</div>

<div class="row">
    <div class="col-sm-1"></div> 
    <div class="col-sm-10">
        {{ $orders->links('pagination::bootstrap-4') }}
    </div>
</div>

@endsection