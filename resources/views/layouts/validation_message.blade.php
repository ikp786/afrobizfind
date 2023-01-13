@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
        <div class="font-medium text-red-600">{{-- __('Whoops! Something went wrong') --}}</div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>

@endif


<!-- <div class="alert alert-danger alert-dismissible fade show">
    
    <strong>Error!</strong> {{Session::get('Failed')}}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close">
    </button>
</div> -->