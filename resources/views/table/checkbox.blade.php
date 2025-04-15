<label class="screen-reader-text" for="cb-select-{{ $value }}">
    {{ $label }}
</label>
<input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" aria-label="{{ $label }}" id="cb-select-{{ $value }}" />
