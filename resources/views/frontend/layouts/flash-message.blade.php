@if(session('success'))
    <script>
        Notiflix.Notify.Success("{{ __(session()->get('success')) }}");
    </script>
@endif
@if(session('alert'))
    <script>
        Notiflix.Notify.Failure("{{ __(session()->get('alert')) }}");
    </script>
@endif
@if(session('error'))
	<script>
		Notiflix.Notify.Failure("{{ __(session()->get('error')) }}");
	</script>
@endif


<script>
	$(document).on('click','.removeStatus', function (){
		$.ajax({url: "{{ route('removeStatus') }}", success: function(result){
			}});

	})
</script>
