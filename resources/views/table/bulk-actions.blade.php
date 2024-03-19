<label for="bulk-action-selector-{{ esc_attr($which) }}" class="screen-reader-text">
	{{ __('Select bulk action') }}
</label>
<select name="action{{ $two }}" id="bulk-action-selector-{{ esc_attr($which) }}">
	<option value="-1">{{ __('- Set Institution -', 'pressbooks-multi-institution') }}</option>

	@foreach($actions as $id => $value)
		<option value="{{ esc_attr($id) }}">{{ $value }}</option>
	@endforeach
</select>

{{ submit_button(__('Apply'), 'action', '', false, ['id' => "doaction{$two}"]) }}
