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
        background-image:url("{{ asset('images/bg_admin.jpg') }}");
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: 100% 100%;
    }

    main button {
        background-color: green;
        border: 1px solid green;
        color: white;
        border-radius: 10px;
        width: 200px;
        height: 40px;
        transition: background-color 0.3s;
    }
    main button:hover {
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
    </style>
  </head>
  <body>
@include('partials.header', ['variant' => 'admin'])


<main>
@yield('content')
</main>
  @include('partials.footer')
  </body>
</html>