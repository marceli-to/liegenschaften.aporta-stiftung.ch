<div class="form-group @if ($errors->has($name)) has-error @endif">
  @if($label ?? null)
    <label class="{{ ($required ?? false) ? 'is-required' : '' }}" for="{{ $name }}">
      {{ $label }} {{ ($required ?? false) ? '*' : '' }}
    </label>
  @endif
  <textarea
    class="{{ $css ?? '' }}"
    name="{{ $name }}"
    rows="10"
    columns="25"
    {{ ($required ?? false) ? 'required' : '' }}
  ></textarea>

</div>