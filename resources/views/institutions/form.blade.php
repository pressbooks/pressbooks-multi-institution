@if (!empty($_POST) && isset($result['success']) && isset($result['message']))
	<div
		id="message"
		class="updated notice {{ $result['success'] ? '' : 'error' }}"
		x-data="{
			data: @js($result),
			show: false,
		}"
		x-init="setTimeout(() => show = true, 200)"
	>
		<template x-if="show">
			<p>
				<template x-if="data.success">
					<strong>{{ __('Success', 'pressbooks-multi-institution') }}:</strong>
				</template>
				<template x-if="! data.success">
					<strong>{{ __('Error', 'pressbooks-multi-institution') }}:</strong>
				</template>
				<template x-if="data.message">
					<span x-text="data.message"></span>
				</template>
			</p>
		</template>

		<template x-if="Object.keys(data.errors).length > 0 && show">
			<ul class="error-list">
				<template x-for="field in Object.keys(data.errors)">
					<li class="padding" x-bind:id="`${field}-errors`">
						<template x-for="error in data.errors[field]">
							<span x-html="error"></span>
						</template>
					</li>
				</template>
			</ul>
		</template>
	</div>
@endif

<div class="wrap">
	<h1>
		@if ($institution->exists)
			{{ __( 'Edit', 'pressbooks-multi-institution') }}
		@else
			{{ __( 'Add', 'pressbooks-multi-institution') }}
		@endif

		{{ __( 'Institution', 'pressbooks-multi-institution') }}
	</h1>
	<a href='{{ $back_url }}' rel='previous'>
		<span aria-hidden='true'>&larr;</span> {{ __( 'Back to Institution List', 'pressbooks-multi-institution') }}
	</a>
	<p class="error">{{ __('An asterisk (*) indicates a required field', 'pressbooks-multi-institution') }}</p>

	<hr class="wp-header-end">

	<form method="post" novalidate>
		{!! wp_nonce_field( 'pb_multi_institution_form' ) !!}

		@if($institution->exists)
			<input type="hidden" name="ID" value="{{ $institution->id }}"/>
		@endif

		<table class="form-table institution" role="none">
			<tr>
				<th>
					<label for="name">
						{{ __('Name', 'pressbooks-multi-institution') }} <span class="error">*</span>
					</label>
				</th>
				<td>
					<input name="name" id="name" type="text" value="{{ $old['name'] ?? $institution->name }}" class="regular-text"
					@isset($result['errors']['name'])
					aria-invalid="true"
					aria-describedby="name-errors"
					@endisset
					required />
				</td>
			</tr>

			<tr>
				<th>
					<label id="domains-label">
						{{ __('Email Domains', 'pressbooks-multi-institution') }}
					</label>
					<p id="domains-description" class="description">
						{{ __('Enter exclusive email domains for this institution (e.g. utopia.edu).', 'pressbooks-multi-institution') }}
					</p>
				</th>
				<td>
					<div
						class="multiple-text-input"
						x-data="{
							count: {{ count($institution->domains) ?? 0 }},
							addNew() {
								this.count++;
								const newItem = this.$refs.template.content.cloneNode(true);
								const newInput = newItem.querySelector('input');
								newInput.setAttribute('id', `${newInput.id}-${this.count + 1}`);
								this.$refs.template.before(newItem);
								newInput.focus();
							}
						}"
					>
						@forelse($old['domains'] ?? $institution->domains as $key => $domain)
							<input
								id="domains-{{ $key + 1 }}"
								name="domains[]"
								type="text"
								value="{{ is_string($domain) ? $domain : $domain->domain }}"
								class="regular-text"
								aria-labelledby="domains-label"
								aria-describedby="domains-description {{ isset($result['errors']['domains']) ? 'domains-errors' : '' }}"
								@isset($result['errors']['domains'])
								aria-invalid="true"
								@endisset
							/>
						@empty
							<input
								id="domains-1"
								name="domains[]"
								type="text"
								value=""
								class="regular-text"
								aria-labelledby="domains-label"
								aria-describedby="domains-description {{ isset($result['errors']['domains']) ? 'domains-errors' : '' }}"
								@isset($result['errors']['domains'])
								aria-invalid="true"
								@endisset
							/>
						@endforelse
						<template x-ref="template">
							<input
								id="domains"
								name="domains[]"
								type="text"
								value=""
								class="regular-text"
								aria-labelledby="domains-label"
								aria-describedby="domains-description {{ isset($result['errors']['domains']) ? 'domains-errors' : '' }}"
								@isset($result['errors']['domains'])
								aria-invalid="true"
								@endisset
							/>
						</template>
						<div>
							<button class="button" type="button" @click="addNew">{{ __('Add New') }}<span class="screen-reader-text"> {{ __('domain', 'pressbooks-multi-institution')}}</span></button>
						</div>
					</div>
				</td>
			</tr>

			@if($isSuperAdmin)
				<tr>
					<th>
						<label for="allow_institutional_managers">
							{{ __('Institutional Managers Allowed', 'pressbooks-multi-institution') }}
						</label>
					</th>
					<td>
						<input
							name="allow_institutional_managers"
							id="allow_institutional_managers"
							type="checkbox"
							value="1"
							@if($institution->allow_institutional_managers)
								checked
							@endif
						/>
					</td>
				</tr>
			@endif

			<tr>
				<th>
					<label id="managers">
						{{ __('Institutional Managers', 'pressbooks-multi-institution') }}
					</label>
					<p id="managers-description" class="description">
						{{ __('Enter username or email to find existing user(s). Limit 3.', 'pressbooks-multi-institution') }}
					</p>
				</th>
				<td class="institutional-managers-component">
					<pressbooks-multiselect
						max="3"
						@if(! $institution->allowsInstitutionalManagers() && ! $isSuperAdmin)
							disabled
						@endif
					>
						<label class="screen-reader-text">Institutional Managers</label>
						<select
							id="managers"
							name="managers[]"
							multiple
							aria-describedby="managers-description {{ isset($result['errors']['managers']) ? 'managers-errors' : '' }}"
						>
							@foreach($users as $user)
								@isset($old['managers'])
									<option
										value="{{ $user->ID }}"
										@if(in_array($user->ID, $old['managers']))
											selected
										@endif
									>
										{{ $user->display_name }} ({{ $user->user_email }})
									</option>
								@else
									<option
										value="{{ $user->ID }}"
										@if($institution->managers->contains(fn (object $institutionUser) => $institutionUser->user_id === $user->ID)))
										selected
										@endif
									>
										{{ $user->display_name }} ({{ $user->user_email }})
									</option>
								@endisset
							@endforeach
						</select>
					</pressbooks-multiselect>
				</td>
			</tr>

			@if($isSuperAdmin)
				<tr>
					<th>
						<label for="buy_in">
							{{ __('Premium Member Buy-in', 'pressbooks-multi-institution') }}
						</label>
					</th>
					<td>
						<input
							name="buy_in"
							id="buy_in"
							type="checkbox"
							value="1"
							@if($institution->buy_in)
								checked
							@endif
						/>
					</td>
				</tr>

				<tr>
					<th>
						<label for="book_limit">
							{{ __('Book Limit', 'pressbooks-multi-institution') }}
						</label>
						<p class="description" id="book_limit-description">
							{{ __('For an unlimited institution, enter 0.', 'pressbooks-multi-institution') }}
						</p>
					</th>
					<td>
						<input
							name="book_limit"
							id="book_limit"
							title="{{ __('Only numbers are allowed', 'pressbooks-multi-institution') }}"
							type="text"
							inputmode="numeric"
							pattern="[0-9]*"
							aria-describedby="book_limit-description"
							value="{{ $old['book_limit'] ?? $institution->book_limit }}"
						/>
					</td>
				</tr>
			@endif
		</table>

		{!! get_submit_button($institution->exists ? __('Save Changes', 'pressbooks-multi-institution') : __('Add Institution')) !!}
	</form>
</div>
