<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">

    <title>Quick Park</title>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <style>
    body{
        background-image:url("{{ asset('images/bg_admin.jpg') }}");
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: 100% 100%;
    }

    button {
        background-color: green;
        border: 1px solid green;
        color: white;
        border-radius: 10px;
        width: 200px;
        height: 40px;
        transition: background-color 0.3s;
    }
    button:hover {
        background-color: darkgreen;
    }
    .category{
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        height: 50vh;
    }
    .category a {
        margin: 0 10px; 
    }
    .category button{
        background-color:green;
        border:1px solid green;
        color:white;
        border-radius: 10px;
        width:200px;
        height:40px;
        font-size:22px;
    }
    .category button:hover {
        background-color: darkgreen;
        color: lightgreen;
    }
    .btn-add-new {
        background: #28a745;
        color: white !important;
        border-radius: 12px;                       
        padding: 10px 25px;
        margin-bottom: 10px;
    }
    .btn-add-new:hover {
        border-color: #90ee90;
        background: #218838;
    }
    .admin-btn {
        display: block;
        background-color: #2c2c2c; 
        border: 2px solid #444;   
        border-radius: 15px;     
        padding: 40px 20px;
        text-align: center;
        text-decoration: none !important;
        transition: all 0.3s ease; 
        color: white;
        height: 100%;
    }

    .admin-btn:hover {
        background-color: #28a745; 
        border-color: #90ee90;     
    }

    .btn-content strong {
        display: block;
        font-size: 22px;
        letter-spacing: 1.5px;
        margin-bottom: 8px;
        text-transform: uppercase;
        color: #fff;
    }

    .btn-content small {
        display: block;
        color: #bbb; /* 默认灰色文字 */
        font-size: 13px;
    }

    .admin-btn:hover small {
        color: rgba(255, 255, 255, 0.9);
    }
    </style>
  </head>
  <body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
 
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
 
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
            <a class="nav-link" style="color:lightgreen; font-family:Georgia,serif;"href="admin"><strong>Q . Park</strong><span class="sr-only">(current)</span></a>
            </li>
        </ul>
        <ul class="navbar-nav mr-right">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('viewUser') }}">User</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('viewProduct') }}">Parking</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('viewCategory') }}">Category</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('allOrder') }}">Payment</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('viewFeedback') }}">Feedback</a>
            </li>
        </ul>
    </div>
</nav>


@yield('content')
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
  </body>
</html>