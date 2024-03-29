@if (!empty($_POST) && isset($result['success']) && isset($result['message']))
	<div id="message" role="status" class="updated notice is-dismissible {{ $result['success'] ? '' : 'error' }}">
		<p>
			<strong>{{ $result['success'] ? __('Success', 'pressbooks-multi-institution') : __('Error', 'pressbooks-multi-institution') }}:</strong>
			{{ $result['message'] }}
		</p>

		@if(isset($result['errors']))
			<ul class="error-list">
				@foreach($result['errors'] as $fieldErrors)
					@foreach($fieldErrors as $error)
						{!! $error !!}
					@endforeach
				@endforeach
			</ul>
		@endif
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

	<form method="post">
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
					<input name="name" id="name" type="text" value="{{ $old['name'] ?? $institution->name }}" class="regular-text" required />
				</td>
			</tr>

			<tr>
				<th>
					<label id="domains-label">
						{{ __('Email Domains', 'pressbooks-multi-institution') }}
					</label>
					<p id="domains-description" class="description">
						{{ __('Enter exclusive email domains for this institution. One per line (e.g. utopia.edu).', 'pressbooks-multi-institution') }}
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
								aria-describedby="domains-description"
							/>
						@empty
							<input
								id="domains-1"
								name="domains[]"
								type="text"
								value=""
								class="regular-text"
								aria-labelledby="{{ $name }}-label"
								aria-describedby="{{ $name }}-description"
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
								aria-describedby="domains-description"
							/>
						</template>
						<div>
							<button class="button" type="button" @click="addNew">{{ __('Add New') }}</button>
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
						<label class="screen-reader-text">Test</label>
						<select
							id="managers"
							name="managers[]"
							multiple
							aria-labelledby="managers-label"
							aria-describedby="managers-description"
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
						<p class="description">
							{{ __('For an unlimited institution, enter 0.', 'pressbooks-multi-institution') }}
						</p>
					</th>
					<td>
						<input
							name="book_limit"
							id="book_limit"
							type="number"
							min="0"
							value="{{ $old['book_limit'] ?? $institution->book_limit }}"
						/>
					</td>
				</tr>
			@endif
		</table>

		{!! get_submit_button() !!}
	</form>
</div>
