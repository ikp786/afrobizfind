@extends('layouts.admin.app')
@section('content')
<style type="text/css">
    .btn-circle-md{
        border: none;
        outline: none !important;
        overflow: hidden;
        width: 40px;
        height: 40px;
        -webkit-border-radius: 50% !important;
        -moz-border-radius: 50% !important;
        -ms-border-radius: 50% !important;
        border-radius: 50% !important;
    }
    .btn-circle-md i {
        font-size: 24px !important;
        position: relative !important;
        right: 4px !important;
        top: 2px !important;
    }

</style>
<section class="content">
  <div class="container-fluid">
    <div class="row clearfix">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="box">
          <div class="box-header with-border">
            <span class="font-20 box-title">Versions</span>
            <a href="{{ route('version.add')}}" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add</a>
          </div>
          <div class="box-body">
            @if(session()->has('message'))
            <div class="alert alert-success alert-dismissible fade in">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              {{ session()->get('message') }}
            </div>
            @endif
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-hover dataTable js-exportable">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Option</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
@section('script')

<script src="{{ asset('src/plugins/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('src/plugins/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript" id="rendered-js">

        $(document).on("click",".deleteversion",function() {
           return confirm("are you sure you want to delete this version?");
        });

      var table111 =  $('.js-exportable').DataTable({
            "dom": 'Bfrtip',
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "columnDefs": [ { orderable: false, targets: [2]},{ "width": "20%", "targets": 2 }],
            "order": [[ 0, "desc" ]],
            "ajax":{
                     "url": "{{ url('admin/version/getall') }}",
                     "dataType": "json",
                     "type": "POST",
                   },
            "columns": [
                // { "data": "id" },
                { "data": "version" },
                { "data": "description" },
                // { "data": "created_at" },
                {
                    mRender: function ( data, type, row ) {
                        var URL = '{{url("/")}}';
                        var mRL = '{{url("/")}}';
                        return '<a href="'+mRL+'/admin/version/show/'+row['id']+'" title="View" class="btn btn-primary mr-1 font-15"><i class="fa fa-eye"></i></a>'+
                            '<a href="'+URL+'/admin/version/edit/'+row['id']+'" title="Edit" class="btn btn-success mr-1 font-15"><i class="fa fa-pencil"></i></a>'+
                            '<a href="'+URL+'/admin/version/delete/'+row['id']+'" title="Edit" class="btn btn-danger mr-1 font-15 deleteversion"><i class="fa fa-trash-o"></i></a>';
                    }
                }
            ]

        });



</script>
@endsection
@section('css')
<style type="text/css">
    .mr-1 {
        margin-right: 3px;
    }
</style>
@endsection
