@extends('layouts.admin.app')
@section('css')
@endsection
@section('content')
    <section class="content">
        <div class="row">
	        <div class="col-lg-3 col-xs-6">
	          <div class="small-box bg-aqua">
	            <div class="inner">
	              <h3>{{ $users }}</h3>
	              <p>Users</p>
	            </div>
	            <div class="icon">
	              <i class="fa fa-user"></i>
	            </div>
	            <!-- <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
	          </div>
	        </div>
	        <div class="col-lg-3 col-xs-6">
	          <div class="small-box bg-green">
	            <div class="inner">
	              <h3>{{ $company }}</h3>
	              <p>Total Companies</p>
	            </div>
	            <div class="icon">
	              <i class="fa fa-bank"></i>
	            </div>
	            <!-- <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
	          </div>
	        </div>
	        <div class="col-lg-3 col-xs-6  ">
	          <div class="small-box bg-yellow">
	            <div class="inner">
	              <h3>{{ $product }}</h3>
	              <p>Total Products</p>
	            </div>
	            <div class="icon">
	              <i class="fa fa-archive"></i>
	            </div>
	           <!--  <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
	          </div>
	        </div>
	        <div class="col-lg-3 col-xs-6  ">
	          <div class="small-box bg-red">
	            <div class="inner">
	              <h3>{{ $offers }}</h3>
	              <p>Total Offers</p>
	            </div>
	            <div class="icon">
	              <i class="fa fa-certificate"></i>
	            </div>
	            <!-- <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
	          </div>
	        </div>
	    </div>
    </section>
@endsection
@section('script')
 
@endsection