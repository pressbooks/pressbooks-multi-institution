<li>
	<span>{{ _n('Email domains can only contain alphanumeric characters, periods, and dashes. The following entry contained invalid content:', 'Email domains can only contain alphanumeric characters, periods, and dashes. The following entries contained invalid content:', count($domains), 'pressbooks-multi-institution') }}</span>
	<span class="indented">
		@foreach($domains as $domain)
			<strong>{{ $domain }}</strong>
		@endforeach
	</span>
	<span class="indented">
		{{ __('Please correct the invalid content and resubmit the form.', 'pressbooks-multi-institution') }}
	</span>
</li>
