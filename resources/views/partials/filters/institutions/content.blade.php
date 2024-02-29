<div id="institutions-tab" class="table-controls">
    <fieldset>
        <legend>{{ __('Institution', 'pressbooks-multi-institution') }}</legend>
        <div class="grid-container">
            @foreach ($institutions as $institution)
                <label>
                    <input name="institution[]" type="checkbox" value="{{ $institution->id }}" /> {{ $institution->name }}
                </label>
            @endforeach

            <label>
                <input name="institution[]" type="checkbox" value="0" /> {{ __('Unassigned', 'pressbooks-multi-institution') }}
            </label>
        </div>
    </fieldset>
</div>
