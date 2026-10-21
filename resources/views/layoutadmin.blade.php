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
@include('partials.header', ['variant' => 'admin'])


<main>
@yield('content')
</main>
  @include('partials.footer')
  </body>
</html>