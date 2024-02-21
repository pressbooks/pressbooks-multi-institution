<div id="institutions-label">{{ __( 'Institutions', 'pressbooks-multi-institution') }}:
	<select id="institutions-dropdown" aria-labelledby="institutions-label">
		<option value="">&nbsp;</option>
		@foreach($institutions as $institution)
			<option value="{{ $institution->name }}">{{ $institution->name }}</option>
		@endforeach
	</select>
</div>
