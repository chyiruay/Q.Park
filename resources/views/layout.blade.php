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
    .navprofile{
        margin-right:100px;
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
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  
  
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav me-auto">
      <li class="nav-item active">
        <a class="nav-link" style="color:lightgreen; font-family:Georgia,serif;"href="/"><strong>Q . Park</strong><span class="visually-hidden">(current)</span></a>
      </li>
    </ul>
    <ul class="navbar-nav ms-auto">
    <li class="nav-item">
      <a class="nav-link" href="{{route('Help')}}">Help</a>
    </li>
    </ul>
    &nbsp;
    &nbsp;
  </div>
  <li class="navprofile dropdown navbar-nav ">
    <a class="dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown" >
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTQGgDOty0QTPEFo5rtlgRxKNSyISnPTgzjnA&usqp=CAU" class="rounded-circle" alt="Profile picture" width="40">
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
      <a class="dropdown-item" href="{{route('viewProfile')}}">My Profile</a>
      <a class="dropdown-item" href="{{route('myCart')}}">My Cart</a>
      <a class="dropdown-item" href="{{route('myOrder')}}">Payment Histoy</a>
      <a class="dropdown-item" href="{{route('insertFeedback')}}">Feedback</a>
      <a class="dropdown-item" href="{{route('login')}}">Login/out</a>
    </div>
  </li>
</nav>

@yield('content')
  <!-- Grid container -->
  <footer class="bg-dark text-center text-white">
  <!-- Grid container -->
  <div class="container p-4" style="text-align:left;">
    <div class="row">
      <!--Grid column-->
      <div class="col-auto" style="font-size:30px;">
        <p class="pt-2">
          <strong>Additional Information</strong>
        </p>
      </div>
      <!--Grid column-->
    </div>
        <!--Grid row-->
    <!-- Section: Text -->
      <p>
      <strong>Quick Park</strong> is committed to making a fast and convenient parking space <br>reservation system to 
      help all drivers save time in finding parking spaces.
      </p>
      <div class="content" style="margin-left:900px; margin-top:-100px;">
        <strong><h6 class="Contact">Contact Us</h6></strong>
          <div>
            <img class="map"src="https://mdland.com.vn/upload/images/pin.png" width="20px" height="22px">
            1,<br><div class="address">JALAN SATU 1,<br>TAMAN SATU,<br>12345 JOHOR BAHRU,<br>JOHOR.</div>
            <p><img class="phone"src="https://www.pngitem.com/pimgs/m/156-1568270_blue-phone-icon-png-clipart-png-download-transparent.png" width="22px" height="22px">+ 07 124 9806</p>
            <p><a class="icon" href="#"><img class="fbicon"src="https://www.akademibursa.com/wp-content/uploads/fbicon-300x300.png" width="30px"height="30px">Facebook Page</a></p>
          </div>
      </div>
  </div>
  <!-- Copyright -->
  <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
    © 2024 Copyright:
    <a class="text-white" href="https://mdbootstrap.com/">web23c_G6</a>
  </div>
  <!-- Copyright -->
</footer>


    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    -->
  </body>
</html>