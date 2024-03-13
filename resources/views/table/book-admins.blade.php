{{--TODO: add styles to css file--}}
@foreach($admins as $admin)
	<div style="margin-bottom: .5rem">
		<strong>{{ $admin->fullname ?: $admin->user_login }}</strong>
		<a href="mailto:{{ $admin->user_email }}">{{ $admin->user_email }}</a>
		<br />
		<span>{{ $admin->institution ?? __('Unassigned', 'pressbooks-multi-institution') }}</span>
	</div>
@endforeach
