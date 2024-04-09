<h1 class="wp-heading-inline">{{ __('Book and User Totals', 'pressbooks-multi-institution') }}</h1>

<table class="wp-list-table widefat fixed striped">
	<thead>
		<tr>
			<th>{{ __('Name', 'pressbooks-multi-institution') }}</th>
			<th>{{ __('Books', 'pressbooks-multi-institution') }}</th>
			<th>{{ __('Users', 'pressbooks-multi-institution') }}</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($totals as $institution)
			<tr>
				<td>{{ $institution['name'] }}</td>
				<td>{{ $institution['book_total'] }}</td>
				<td>{{ $institution['user_total'] }}</td>
			</tr>
		@endforeach
	</tbody>
</table>
