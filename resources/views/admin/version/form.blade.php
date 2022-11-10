@extends('layouts.admin.app')
@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="row clearfix">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="box ">
          <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
              <form method="post" role="form" action="{{ route('version.store') }}">
                <input type="hidden" name="id" value="{{ $version->id}}" >
                @csrf
                <div class="box-body">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Version name</label>
                    <input name="name" type="text" class="form-control" id="exampleInputEmail1" placeholder="Enter version name here" required="" value="{{ $version->version }}">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">Description</label>
                    <textarea class="form-control" name="description" >{{ $version->description }}</textarea>
                  </div>
                </div>
                <div class="box-footer">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
