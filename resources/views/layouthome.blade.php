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
      .navprofile{
        margin-right:1.5rem;
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
  <footer class="site-footer">
    <div class="container py-5">
      <div class="row g-4">

        <div class="col-12 col-lg-7">
          <h2>Additional Information</h2>
          <p class="mb-0">
            <strong>Quick Park</strong> is committed to making a fast and convenient
            parking space reservation system to help all drivers save time in
            finding parking spaces.
          </p>
        </div>

        <div class="col-12 col-lg-5">
          <h6>Contact Us</h6>
          <address class="mb-2">
            1, Jalan Satu 1,<br>
            Taman Satu,<br>
            12345 Johor Bahru,<br>
            Johor.
          </address>
          <p class="mb-2">
            <a href="tel:+6071249806">+ 07 124 9806</a>
          </p>
          <p class="mb-0">
            <a href="#">Facebook Page</a>
          </p>
        </div>

      </div>
    </div>

    <div class="site-footer__bar text-center p-3">
      &copy; 2024 Copyright:
      <a href="https://mdbootstrap.com/">web23c_G6</a>
    </div>
  </footer>


    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    -->
  </body>
</html>