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
        background-color:black;
      }
.category button{
        background-color:green;
        border:1px solid green;
        color:white; 
        border-radius: 20px; 
        width:300px; 
        height:40px; 
        font-size:25px;
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