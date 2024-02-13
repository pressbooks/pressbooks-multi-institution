@if (isset($result['message']))
	<div id="message" role="status" class="updated {{ $result['success'] ? 'notice' : 'error' }} is-dismissible">
		<p>{{ $result['message'] }}</p>
	</div>
@endif

<div class="wrap">
	<h1 class="wp-heading-inline">{{ __('Assign Users', 'pressbooks-multi-institution') }}</h1>

	@if( !empty($params['s']) && !empty($params['orderby']))
		<div class="filtering">
			<ul>
				<li>{!! sprintf( __( '<strong>Showing results for:</strong> %s', 'pressbooks-multi-institution' ), $params['s'] ) !!}</li>
			</ul>
			<a href="{{ $list_url }}" class="button">{{ __('Clear filters', 'pressbooks-multi-institution') }}</a>
		</div>
	@endif

	<hr class="wp-header-end">

	<form id="pressbooks-multi-institution-assign-users" method="GET">
		<p class="search-box">
			<label class="screen-reader-text" for="search-input">{{ __( 'Search', 'pressbooks-multi-institution') }}:</label>
			<input type="search" id="search-input" name="s" value="">
			<button id="search-apply" class="button" type="submit">{{ __( 'Search', 'pressbooks-multi-institution') }}</button>
		</p>
		<input type="hidden" name="page" value="{{ $page }}" />
		@foreach ($params as $name => $value)
			<input type="hidden" name="{{ $name }}" value="{{ $value }}" />
		@endforeach

		{!! $table->display() !!}
	</form>
</div>
