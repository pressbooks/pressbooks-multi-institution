<li>
	<p>
		{{ __('Valid email domains can only include letters (a-z), numbers, periods, and dashes. They must include at least one period, which cannot come at the beginning or end of the domain. A period or dash must be followed by one or more letter or number.', 'pressbooks_multi_institution' )}}
	</p>
	<p>
		{{ _n('The following entry contained invalid content:', 'The following entries contained invalid content:', count($domains), 'pressbooks-multi-institution') }}
	</p>
		<ul>
		@foreach($domains as $domain)
			<li class="padding invalid"><strong>{{ $domain }}</strong></li>
		@endforeach
		</ul>
	<p>
		{{ __('Please correct the invalid content and resubmit the form.', 'pressbooks-multi-institution') }}
	</p>
</li>
