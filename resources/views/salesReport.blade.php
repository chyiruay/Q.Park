<!DOCTYPE html>
<html>
<head>
    <title>Car Park Reservstion System</title>
    <style>
        table {
        border-collapse: collapse;
        width: 100%;
        }

        th, td {
            border: 1px solid #000; 
            padding: 8px; 
            text-align: left; 
        }

    </style>
</head>

<body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<div class="row">
    <div class="col-sm-1"></div>
    <div class="col-sm-10">
        <br></br>
        <div class="table-wrapper">
            <div class="table-title">
                <div class="row">
                    <div class="col-sm-8"><h2>Sales Report</h2></div>
                    <div class="col-sm-4">
                    </div>
                </div>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Date time</th>
                        <th>Amount</th>                           
                    </tr>
                </thead>

                <tbody>

                    @foreach($orders as $order)
                    <tr>
                        <td>{{$order->id}}</td>
                        <td>{{$order->userID}}</td>
                        <td>{{$order->created_at}}</td>                        
                        <td>RM{{$order->amount}}</td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
    <div class="col-sm-1"></div> 
</div>

</body>
</html>