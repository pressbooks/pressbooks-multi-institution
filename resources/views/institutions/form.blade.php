@if (!empty($_POST) && isset($result['success']) && isset($result['message']))
	<div id="message" role="status" class="updated {{ $result['success'] ? 'notice' : 'error' }} is-dismissible">
		<p>{{ $result['message'] }}</p>
	</div>
@endif

<div class="wrap">
	<h1>
		@if ($institution->exists)
			{{ __( 'Editing', 'pressbooks-multi-institutions') }}
		@else
			{{ __( 'Adding', 'pressbooks-multi-institutions') }}
		@endif

		{{ __( 'Institution', 'pressbooks-multi-institutions') }}
	</h1>

	<hr class="wp-header-end">

	<p>
		<a href='{{ $back_url }}' rel='previous'>
			<span aria-hidden='true'>&larr;</span> {{ __( 'Back to LTI Institution Listing', 'pressbooks-multi-institutions') }}
		</a>
	</p>

	<form method="post">
		{!! wp_nonce_field( 'pb_lti_platforms' ) !!}

		@if($institution->exists)
			<input type="hidden" name="ID" value="{{ $institution->id }}"/>
		@endif

		<div>
			TODO
		</div>

		{!! get_submit_button() !!}
	</form>
</div>
