<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ env("APP_NAME") }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @auth
   <!-- <meta name="userID" content="{{ auth()->user()->hashid }}"> -->
  @endauth
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="{{ asset('dist/css/bootstrap.min.css') }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('dist/plugins/font-awesome/css/font-awesome.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/template.min.css') }}">
  <link rel="stylesheet" href="{{ asset('dist/css/style.css') }}">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="{{ asset('dist/css/_all-skins.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/toastr/toastr.min.css') }}">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    @yield('css') 
    <script type="text/javascript">
        var APP_URL = "{{ url('')}}"; 
    </script>
</head>
<body class="hold-transition skin-blue sidebar-mini skin-black">
<!-- Site wrapper -->
<div class="wrapper" id="app">
 
  <header class="main-header">
    <!-- Logo -->
    <a href="{{ url('admin/') }}" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>AF</b></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>Afrobizfind</b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu invitation-wrap">
        <ul class="nav navbar-nav">
             <!-- <li><a href="{{ url('admin/changepassword') }}">Change Password</a></li> -->
             <li><a  title="logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();"> Logout  </a>
            </li>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>



        </ul>
      </div>
    </nav>
  </header>

  <!-- =============================================== -->