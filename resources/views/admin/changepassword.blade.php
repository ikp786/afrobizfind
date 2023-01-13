@extends('layouts.admin.app')
@section('css')
<style type="text/css">
span.error{
    color: red;
}
</style>
@endsection

@section('content')
 
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Change Password
      <!-- <small>Optional description</small> -->
    </h1>
</section>

<section class="content container-fluid">
  <div class="col-md-12">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title"> </h3>
      </div>
  <div class="col-md-12">
     <div class="alert alert-success" style="display:none"></div>
 </div>
      <form class="form-horizontal " id="createform">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="box-body">
          <div class="col-md-6">
            <div class="form-group">
              <label for="inputEmail3" class="col-sm-4 control-label">Current Password</label>
              
              <div class="col-sm-8">
                <input type="password" name="current_password" class="form-control" id="current_password" placeholder="Current password">
              </div>
            </div>
            <div class="form-group">
              <label for="inputPassword3" class="col-sm-4 control-label">New Password</label>

              <div class="col-sm-8">
                <input type="password"  name="password"class="form-control" id="password" placeholder="Password">
              </div>
            </div>
            <div class="form-group">
              <label for="inputPassword3" class="col-sm-4 control-label">Confirm New Password</label>

              <div class="col-sm-8">
                <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Confirm New Password">
              </div>
            </div>
            <div class="form-group">
              <div class=" col-sm-12">
                <div class=" pull-right">
                  <button id="submit" class="btn btn-info  ">Sign in</button>
                  <a href="{{ url('admin/') }}" class="btn btn-default">Cancel</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /.box-body -->
      </form>
    </div>
  </div>
</section>
<!-- /.content -->
 
@stop

@section('script')

<script type="text/javascript">
  $(document).ready(function(){
      $('#submit').click(function(e){
      e.preventDefault();
      //$('#preloader').show();
      $(".valerror").remove();
      $('.alert-success').html('').hide();
      $.ajax({
        url: "{{ url('/admin/changePassword') }}",
        method: 'post',
        data:  $("#createform").serialize(),
        success: function(data){
         // console.log(data);
            if(data.errors){ 
            $.each(data.errors, function(key, value){
                 $( "input[name='"+key+"']" ).after("<span class='error valerror'>"+value+"</span>")
             });
            }
            if(data.error){
                $('.alert-danger').show();
                 $("#password").after("<span class='error valerror'>"+data.error+"</span>");
            }
            if(data.error1){
                $('.alert-danger').show();
                 $("#current_password").after("<span class='error valerror'>"+data.error1+"</span>");
            }
            if(data.success){
                $('.alert-success').show();
                $('.alert-success').html('<p>'+data.success+'</p>');
                $('#createform').trigger("reset");
            }
           // $('#preloader').hide();
        }
    });
   });
  });

</script>
@stop