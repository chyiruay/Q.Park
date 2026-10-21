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
.inline{
        display:inline-block;
      }
      .center-container {
      display: flex;
      justify-content: center;
      align-items: center;
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