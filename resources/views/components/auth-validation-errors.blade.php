@props(['errors'])

@if ($errors->any())
<div {{ $attributes }}>
    <div class="font-medium text-red-600">
        {{ __($errors->all()[0]) }}
    </div>
</div>
@endif