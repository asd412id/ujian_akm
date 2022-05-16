@props([
'height' => null,
'placeholder' => null,
'er' => 0,
'extraplugins' => '',
'plugins' =>
'align,charCounter,codeBeautifier,codeView,colors,draggable,embedly,emoticons,entities,fontAwesome,fontFamily,fontSize,fullscreen,inlineStyle,inlineClass,lineBreaker,lineHeight,link,lists,paragraphFormat,paragraphStyle,quickInsert,quote,table,url,wordPaste'
])

@php
$plugins .= $extraplugins?','.$extraplugins:'';
@endphp

<div wire:ignore wire:key='editor-{{ $er }}'>
  <textarea {{ $attributes->whereStartsWith('wire') }} class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200
  focus:ring-opacity-50" x-data="{data: @entangle($attributes->wire('model'))}" x-init="
    new FroalaEditor($el, {
      height: '{{$height}}',
      placeholderText: '{{$placeholder}}',
      attribution: false,
      pluginsEnabled: '{{ $plugins??null }}',
      events: {
        'contentChanged': function () {
          data = this.html.get();
        }
      }
    });
    "></textarea>
</div>