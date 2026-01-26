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
        background-color:black;
      }
      .navprofile{
        margin-right:100px;
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
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  
  <a class="navbar-brand" href="#"></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
      <a class="nav-link" style="color:lightgreen; font-family:Georgia,serif;"href="/"><strong>Q . Park</strong><span class="sr-only">(current)</span></a>
      </li>
    </ul>
    <form class="form-inline my-2 my-lg-0" action="{{route('search.product')}}" method="POST">
      @csrf
      <input class="form-control mr-sm-2" name="keyword" type="search" placeholder="Search" aria-label="Search">
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form>
    <ul class="navbar-nav mr-right ">
      <li class="nav-item">
        <a class="nav-link" href="{{route('Help')}}">Help</a>
      </li>
    </ul>
    &nbsp;
    
    &nbsp;
    
  </div>
  <li class="navprofile dropdown navbar-nav">
    <a class="dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" >
    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTQGgDOty0QTPEFo5rtlgRxKNSyISnPTgzjnA&usqp=CAU" class="rounded-circle" alt="Profile picture" width="40">
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
    <a class="dropdown-item" href="{{route('viewProfile')}}">My Profile</a>
    <a class="dropdown-item" href="{{route('myCart')}}">My Cart</a>
    <a class="dropdown-item" href="{{route('myOrder')}}">Payment History</a>
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
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js" integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous"></script>
    -->
  </body>
</html>