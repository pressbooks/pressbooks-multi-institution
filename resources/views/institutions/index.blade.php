@if (isset($result['message']))
	<div id="message" role="status" class="updated {{ $result['success'] ? 'notice' : 'error' }} is-dismissible">
		<p>{{ $result['message'] }}</p>
	</div>
@endif

<div class="wrap">
	<h1 class="wp-heading-inline">{{ __('Institution List', 'pressbooks-multi-institution') }}</h1>

	<a class="page-title-action" href="{{ $add_new_url }}">{{ __('Add new', 'pressbooks-multi-institution') }}</a>

	<hr class="wp-header-end">
</div>
