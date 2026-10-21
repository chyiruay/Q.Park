<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <title>Quick Park</title>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <style>
      body{
        background-image: url("{{ asset('images/parking_1.webp') }}");
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: 100% 100%;
      }
      .hometitle{
        color:white;
        font-size:60px;
        text-align:center;
        margin-top:-400px;
        font-family:arial black;
      }
    .parkingbutton button{
        border:1px solid green;
        background:green;
        border-radius: 10px;
        color:white;
        width:150px;
        height:40px;
        font-size:18px;
    }
.inline{
        display:inline-block;
    }
    .edit button{
      border:1px solid green;
      background:green;
      border-radius: 10px;
      color:white;
      width:55px;
      height:35px;
      font-size:18px;
      margin-top:10px;
    }
    .changepassword button{
      border:1px solid green;
      background:green;
      border-radius: 10px;
      color:white;
      width:170px;
      height:35px;
      font-size:18px;
      margin-top:10px;
    }
    .floorPlan:hover{
      transform: scale(1.8);
    }
    </style>
  </head>
  <body>
@include('partials.header')

@yield('content')
  <!-- Grid container -->
@include('partials.footer')


    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    -->
  </body>
</html>