@if (session('success'))
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            toastr.options = {
                closeButton: false,
                progressBar: true,
                positionClass: 'toast-top-center',
                timeOut: 5000,
                extendedTimeOut: 1000,
            };
            toastr.success(@json(session('success')), 'SUCCESS!');
        });
    </script>
@endif

@if (session('alert'))
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            toastr.options = {
                closeButton: false,
                progressBar: true,
                positionClass: 'toast-top-center',
                timeOut: 5000,
                extendedTimeOut: 1000,
            };
            toastr.error(@json(session('alert')), 'ALERT!');
        });
    </script>
@endif
