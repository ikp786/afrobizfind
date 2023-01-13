<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AdminLTE 2 | Log in</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
 <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('dist/css/bootstrap.min.css') }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('dist/css/font-awesome.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/template.min.css') }}">
  <link rel="stylesheet" href="{{ asset('dist/css/style.css') }}">
  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

   @yield('css')

</head>
<body class="hold-transition login-page"> 

 
    @yield('content')


<!-- Jquery Core Js -->
<!-- <script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script> -->
<!-- Bootstrap Core Js -->
<!-- <script src="{{ asset('js/bootstrap.min.js') }}"></script> -->

    @yield('script')
</body>

</html>