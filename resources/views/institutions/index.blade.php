@if (isset($result['message']))
	<div id="message" role="status" class="updated {{ $result['success'] ? 'notice' : 'error' }} is-dismissible">
		<p>{{ $result['message'] }}</p>
	</div>
@endif

<div class="wrap">
	<h1 class="wp-heading-inline">{{ __('Institution List', 'pressbooks-multi-institution') }}</h1>

	<a class="page-title-action" href="{{ $add_new_url }}">{{ __('Add new', 'pressbooks-multi-institution') }}</a>

	@if( !empty($params['searchQuery']) && !empty($params['orderBy']))
	<div class="filtering">
		<ul>
			<li>{!! sprintf( __( '<strong>Showing results for:</strong> %s', 'pressbooks-multi-institution' ), $params['searchQuery'] ) !!}</li>
		</ul>
		<a href="{{ $list_url }}" class="button">{{ __('Clear filters', 'pressbooks-multi-institution') }}</a>
	</div>
	@endif

	<hr class="wp-header-end">

	<form id="pressbooks-multi-institution-admin" method="GET">
		<p class="search-box">
			<label class="screen-reader-text" for="search-input">{{ __( 'Search', 'pressbooks-multi-institution') }}:</label>
			<input type="search" id="search-input" name="s" value="">
			<button id="search-apply" class="button" type="submit">{{ __( 'Search', 'pressbooks-multi-institution') }}</button>
		</p>
		<input type="hidden" name="page" value="{{ $page }}" />
		{!! $table->display() !!}
	</form>
</div>
