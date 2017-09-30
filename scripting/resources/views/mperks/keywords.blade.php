@extends('layout')

@section('javascript')
@parent
<script>
$(document).ready(function() {
	$('textarea').each(function() {
		$(this).val($(this).val().trim());
	});
});
</script>
@stop

@section('main')
<div class="col-md-8 col-md-offset-2">
	<form method="post" action="{{ route('mperks.keywords.submit') }}">
		{{ csrf_field() }}
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-header">Please list target MPerks Keywords</h3>
			</div>
			<div class="panel-body">
			<p>Please list one-per-line the MPerks Keywords you would like to auto-clip, each keyword separated by a comma:</p>
			<div class="well">
				<p><b>Example:</b></p>
				<p><i>amazon,echo</i></p>
				<p>Would match any coupon that has both "amazon" and "echo" in the name, i.e. "$80.00 off 1 : Amazon Echo Black"</p>
			</div>
			<textarea class="form-control" rows="20" name="keywords">	@foreach($keywords as $keyword){{ trim($keyword->keywords) . "\n" }}@endforeach</textarea>
			<input type="submit" class="btn btn-primary btn-block">
			</div>
		</div>
	</form>
</div>
@stop
