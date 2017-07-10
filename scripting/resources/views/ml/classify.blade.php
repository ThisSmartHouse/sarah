@extends('layout')

@section('javascript')
@parent
<script>

	$('#positive').on('click', function(e) {
		e.preventDefault();

		$('#image_result').val(1);
		$('#classifyForm').submit();
		
	});

	$('#negative').on('click', function(e) {
		e.preventDefault();

		$('#image_result').val(0);
		$('#classifyForm').submit();
	});
</script>
@stop

@section('main')
<div class="col-lg-8 col-lg-offset-2">
    <form id="classifyForm" method="post" action="{{ route('ml.classify.submit', ['bucket' => $bucket]) }}">
        {{ csrf_field() }}
        <input type="hidden" name="image_key" value="{{ $image_key }}">
        <input type="hidden" name="image_result" value="-1" id="image_result">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-header">Are there people in this image?</h3>
            </div>
            <div class="panel-body text-center">
                <img src="{{ $image_url }}" style="border: thin solid black;">
            </div>
            <div class="panel-footer clearfix">
                <button type="button" id="positive" class="btn btn-success btn-block">Yes</button>
                <button type="button" id="negative" class="btn btn-danger btn-block">No</button>
            </div>
        </div>
    </form>
</div>
@stop
