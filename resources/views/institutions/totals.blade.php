<h1 class="wp-heading-inline institutions-totals-heading">{{ __('Book and User Totals', 'pressbooks-multi-institution') }}</h1>

<table class="wp-list-table widefat fixed striped institutions-totals-table">
	<thead>
		<tr>
			<th colspan="2">{{ __('Type', 'pressbooks-multi-institution') }}</th>
			<th colspan="1">{{ __('Books', 'pressbooks-multi-institution') }}</th>
			<th colspan="1">{{ __('Users', 'pressbooks-multi-institution') }}</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($totals as $total)
			<tr>
				<td colspan="2">{{ $total['type'] }}</td>
				<td colspan="1">{{ $total['book_total'] }}</td>
				<td colspan="1">{{ $total['user_total'] }}</td>
			</tr>
		@endforeach
	</tbody>
</table>
