<!-- 
<div class="error-bg mb-2" style="color: red;">
    <div class="font-medium text-red-600">{{ __('Whoops! Something went wrong') }}</div>
    <ul class="mt-3 list-disc list-inside text-sm text-red-600">
        <li>{{Session::get('Failed')}}</li>
    </ul>
</div> 

-->

@if(Session::has('Failed'))
<div class="alert alert-danger alert-dismissible fade show">
    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-2">
        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>
    <strong>Error!</strong> {{Session::get('Failed')}}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close">
    </button>
</div>


@endif

@if(Session::has('Success'))

<div class="alert alert-success alert-dismissible fade show">
    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-2">
        <polyline points="9 11 12 14 22 4"></polyline>
        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
    </svg>
    <strong>Success!</strong> {{Session::get('Success')}}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close">
    </button>
</div>
<!-- <div class="success-bg mb-2">
        <div class="font-medium text-green-600">{{Session::get('Success')}}</div> -->
<!-- </div> -->
@endif