@extends('layouts.admin.app')
@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="row clearfix">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="box">
          <div class="box-header with-border">
            <h3 >Version Detail</h3>
          </div>
          <div class="box-body">
            <div class="row clearfix">
              <div class="col-lg-2 col-md-2 col-sm-12">
                <b>Name : </b>
              </div>
              <div class="col-lg-9 col-md-9 col-sm-12">
                {{ $version->version }}
              </div>
              <div class="clearfix"></div>
              <br>
              <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
                <b> Description : </b> 
              </div>
              <div class="col-lg-9 col-md-9 col-sm-6 col-xs-6">
                <p style="white-space: pre-line;">
                  {{ trim($version->description) }}
                </p>
              </div>
              <div class="col-lg-12 text-center" >
                <a href="{{route('admin.versions') }}" class="btn btn-primary">Back</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection