@if (isset($result['message']))
	<div id="message" role="status" class="updated {{ $result['success'] ? 'notice' : 'error' }} is-dismissible">
		<p>{{ $result['message'] }}</p>
	</div>
@endif

<div class="wrap">
	<h1 class="wp-heading-inline">{{ $title }}</h1>

	@if(!empty($params['s']))
		<div class="filtering">
			<ul>
				<li>
					<strong>{!! sprintf( __( 'Showing results for: %s', 'pressbooks-multi-institution' ), $params['s'] ) !!}</strong>
				</li>
			</ul>
			<a href="{{ $list_url }}" class="button">{{ __('Clear filters', 'pressbooks-multi-institution') }}</a>
		</div>
	@endif

	<hr class="wp-header-end">

	<form id="pressbooks-multi-institution-assign-table" method="GET">
		<p class="search-box">
			<label class="screen-reader-text" for="search-input">{{ __( 'Search', 'pressbooks-multi-institution') }}:</label>
			<input type="search" id="search-input" name="s" value="{{ $params['s'] ?? '' }}">
			<button id="search-apply" class="button" type="submit">{{ __( 'Search', 'pressbooks-multi-institution') }}</button>
		</p>
		<input type="hidden" name="page" value="{{ $page }}" />
		@foreach ($params as $name => $value)
			@if($name !== 's')
				<input type="hidden" name="{{ $name }}" value="{{ $value }}" />
			@endif
		@endforeach

		<div>
			<ul class="subsubsub">
				<li class="all">
					<a href="?page=pb_multi_institutions_users" class="{{ ! empty($params['unassigned']) ? '' : 'current' }}">
						{{ __('All', 'pressbooks-multi-institution') }}
						<span class="count">({{ $all_count }})</span>
					</a> |
				</li>
				<li class="unassigned">
					<a href="?page=pb_multi_institutions_users&unassigned=1" class="{{ empty($params['unassigned']) ? '' : 'current' }}">
						{{ __('Unassigned', 'pressbooks-multi-institution') }}
						<span class="count">({{ $unassigned_count }})</span>
					</a>
				</li>
			</ul>
		</div>

		{!! $table->display() !!}
	</form>
</div>
