{{-- TODO: extract css to file --}}
<style>
    :root {
        --pb-input-width: 25em;
    }

    .description {
        font-weight: normal;
    }

    .form-table td select[hidden] {
        display: none;
    }

    .multiple-text-input {
        display: grid;
        grid-template-columns: 1fr;
        gap: .5rem;
    }
</style>

@if (!empty($_POST) && isset($result['success']) && isset($result['message']))
    <div id="message" role="status" class="updated {{ $result['success'] ? 'notice' : 'error' }} is-dismissible">
        <p>
            <strong>{{ $result['success'] ? __('Success', 'pressbooks-multi-institution') : __('Error', 'pressbooks-multi-institution') }}:</strong>
            {{ $result['message'] }}
        </p>

        @if(isset($result['errors']))
            <ul>
                @foreach($result['errors'] as $fieldErrors)
                    @foreach($fieldErrors as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                @endforeach
            </ul>
        @endif
    </div>
@endif

<div class="wrap">
    <h1>
        @if ($institution->exists)
            {{ __( 'Editing', 'pressbooks-multi-institution') }}
        @else
            {{ __( 'Adding', 'pressbooks-multi-institution') }}
        @endif

        {{ __( 'Institution', 'pressbooks-multi-institution') }}
    </h1>

    <hr class="wp-header-end">

    <p>
        <a href='{{ $back_url }}' rel='previous'>
            <span aria-hidden='true'>&larr;</span> {{ __( 'Back to Institution List', 'pressbooks-multi-institution') }}
        </a>
    </p>

    <form method="post">
        {!! wp_nonce_field( 'pb_multi_institution' ) !!}

        @if($institution->exists)
            <input type="hidden" name="ID" value="{{ $institution->id }}"/>
        @endif

        <table class="form-table" role="none">
            <tr>
                <th>
                    <label for="name">
                        {{ __('Name', 'pressbooks-multi-institution') }}
                    </label>
                </th>
                <td>
                    <input name="name" id="name" type="text" value="{{ $old['name'] ?? $institution->name }}" class="regular-text" />
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

            <tr>
                <th>
                    <label id="managers">
                        {{ __('Institutional Managers', 'pressbooks-multi-institution') }}
                    </label>
                    <p id="managers-description" class="description">
                        {{ __('Enter username or email to find existing user(s)', 'pressbooks-multi-institution') }}
                    </p>
                </th>
                <td>
                    <pressbooks-multiselect>
                        <label class="screen-reader-text">Test</label>
                        <select
                            id="managers"
                            name="managers[]"
                            multiple
                            aria-labelledby="managers-label"
                            aria-describedby="managers-description"
                        >
                            @foreach($users as $user)
                                <option
                                    value="{{ $user->ID }}"
                                    @if($old['managers'] ? in_array($user->ID, $old['managers']) : $institution->managers->contains($user->ID))
                                        selected
                                    @endif
                                >
                                    {{ $user->display_name }} ({{ $user->user_email }})
                                </option>
                            @endforeach
                        </select>
                    </pressbooks-multiselect>
                </td>
            </tr>

            <tr>
                <th>
                    <label for="book_limit">
                        {{ __('Book Limit', 'pressbooks-multi-institution') }}
                    </label>
                    <p class="description">
                        {{ __('As defined in contract. Contact Pressbooks for adjustments.', 'pressbooks-multi-institution') }}
                    </p>
                </th>
                <td>
                    <input
                        name="book_limit"
                        id="book_limit"
                        type="number"
                        min="0"
                        value="{{ $old['book_limit'] ?? $institution->book_limit }}"
                        @if(! $canUpdateLimits)
                            disabled
                        @endif
                    />
                </td>
            </tr>

            <tr>
                <th>
                    <label for="user_limit">
                        {{ __('User Limit', 'pressbooks-multi-institution') }}
                    </label>
                    <p class="description">
                        {{ __('As defined in contract. Contact Pressbooks for adjustments.', 'pressbooks-multi-institution') }}
                    </p>
                </th>
                <td>
                    <input
                        name="user_limit"
                        id="user_limit"
                        type="number"
                        min="0"
                        value="{{ $old['user_limit'] ?? $institution->user_limit }}"
                        @if(! $canUpdateLimits)
                            disabled
                        @endif
                    />
                </td>
            </tr>
        </table>

        {!! get_submit_button() !!}
    </form>
</div>
