  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
     <strong>Copyright &copy; 2014-2020 <a href="#">Chat app</a>.</strong> All rights
    reserved.
  </footer>
    <!-- <script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script> -->
      
  </div>
 
 
<!-- jQuery 3 -->
<script src="{{ asset('dist/js/jquery.min.js') }}"></script>
<!-- Bootstrap 3.3.7 -->
<script src="{{ asset('dist/js/bootstrap.min.js') }}"></script>
<!-- SlimScroll -->
<script src="{{ asset('dist/js/jquery.slimscroll.min.js') }}"></script>
<!-- FastClick -->
<script src="{{ asset('dist/js/fastclick.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('dist/js/template.min.js') }}"></script>
<script src="{{ asset('src/plugins/toastr/toastr.min.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('dist/js/demo.js') }}"></script>
<!-- <script src="{{ asset('dist/js/custom.js') }}"></script> -->

<script>
  $(document).on('click', '.dropdown-menu', function (e) {
    e.stopPropagation();
  });
  $(document).ready(function () {
    $('.sidebar-menu').tree()
  });
 
 
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
</script>
<!-- <script src="https://unpkg.com/vue-multiselect@2.1.0"></script> -->
 @yield('script')


</body>
</html>