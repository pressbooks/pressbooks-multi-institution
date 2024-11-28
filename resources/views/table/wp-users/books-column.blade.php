@foreach ($books as $book)
	<span class="site-1">
		<a href="{{ $book->siteurl }}/wp-admin">
			{{ $book->domain . $book->path }}
		</a>
		<small class="row-actions">
			<span class="edit">
				<a href="{{ $book->siteurl }}/wp-admin">{{ __('Dashboard', 'pressbooks-multi-institution') }}</a> |
			</span>
			<span class="view">
				<a href="{{ $book->siteurl }}">{{ __('View', 'pressbooks-multi-institution') }}</a>
			</span>
		</small>
	</span>
	<br />
@endforeach
