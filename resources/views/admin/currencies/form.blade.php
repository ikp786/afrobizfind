@extends('layouts.admin.app')
@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="row clearfix">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="box ">
          <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
               @include('layouts.validation_message')
                @include('layouts.auth_message')

              <form method="post" role="form" action="{{ route('currencies.store') }}">
                <input type="hidden" name="id" value="{{ @$currency->id}}" >
                @csrf
                <div class="box-body">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Currency name</label>
                    <input name="name" type="text" class="form-control" id="exampleInputEmail1" placeholder="Enter Currency name here" required="" value="{{ @$currency->name }}">
                  </div>

                  <div class="form-group">
                    <label for="exampleInputPassword1">Currency code</label>
                     <input name="currency_code" type="text" class="form-control" id="country_price" placeholder="Enter country code here" required="" value="{{ @$currency->currency_code }}">

                  </div>


                  <div class="form-group">
                    <label for="exampleInputEmail1">Currency Sign</label>
                    <input name="currency_sign" type="text" class="form-control" id="exampleInputEmail1" placeholder="Enter Currency Sign here" required="" value="{{ @$currency->currency_sign }}">
                  </div>

                  <div class="form-group">
                    <label for="exampleInputPassword1">Country Name</label>
                     <input name="country_name" type="text" class="form-control" id="exampleInputEmail1" placeholder="Enter country name here" required="" value="{{ @$currency->country_name }}">

                  </div>


                  <div class="form-group">
                    <label for="exampleInputPassword1">Country Price</label>
                     <input name="country_price" type="number" class="form-control" id="country_price" placeholder="Enter country price here" required="" value="{{ @$currency->country_price }}">
                  </div>

                  <div class="form-group">
                    <label for="exampleInputPassword1">Price In UK code</label>
                     <input name="price_in_uk" type="text" class="form-control" id="price_in_uk" placeholder="Enter Price In UK code here" required="" value="{{ @$currency->price_in_uk }}">

                  </div>

                  <div class="form-group">
                    <label for="exampleInputPassword1">Price Per Ticket</label>
                     <input name="price_per_ticket" type="text" class="form-control" id="price_per_ticket" placeholder="Enter Price Per Ticket" required="" value="{{ @$currency->price_per_ticket }}">
                  </div>

                  <div class="form-group">
                    <label for="exampleInputPassword1">Processing Fee</label>
                     <input name="processing_fee" type="text" class="form-control" id="processing_fee" placeholder="Processing Fee" required="" value="{{ @$currency->processing_fee }}">
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
