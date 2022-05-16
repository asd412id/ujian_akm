<x-modal.card blur maxWidth="4xl" title="{{ $modalTitle }}" wire:model='showSoal' staticbackdrop
  x-on:close="$dispatch('removeline')">
  <div class="flex flex-col gap-3 relative" wire:key='soal-{{ $sid }}'>
    @if ($soal)
    @php
    $itemSoal = $soal->item_soals()->orderBy('num','asc')->get()
    @endphp
    @forelse ($itemSoal as $k => $v)
    <x-card>
      <div class="flex flex-col gap-2" x-data>
        <div>
          <p class="font-bold italic text-sm text-gray-400">#Jenis: {{ strtoupper($v->type) }}, Skor: {{ $v->score }}
          </p>
          <p class="flex gap-1"><span>{{ $v->num??($k+1) }}.</span> <span>{!! shortcode($v->text) !!}</span></p>
        </div>
        @if ((strtolower($v->type)=='pg' || strtolower($v->type)=='pgk') && is_array($v->options))
        <div class="flex flex-col gap-1">
          @foreach ($v->options as $key => $o)
          <div class="{{ $v->corrects[$key]?'font-bold':'' }} flex flex-wrap items-start">{!! $key.'. '.shortcode($o)
            !!}</div>
          @endforeach
        </div>
        @elseif ((strtolower($v->type)=='is' || strtolower($v->type)=='u') && $v->answer)
        <div class="flex flex-col gap-1">
          <div class="font-bold flex gap-1"><span>Jawaban:</span> {!! shortcode($v->answer) !!}</div>
        </div>
        @elseif (strtolower($v->type)=='jd' && $v->relations)
        <div class="flex gap-48 c`relative">
          <div class="flex flex-col gap-2 relative">
            @if (isset($v->labels[0]))
            <div class="font-bold border-b-2 border-b-gray-600">{{ $v->labels[0] }}</div>
            @endif
            @foreach ($v->relations as $key => $o)
            @if (is_array($o))
            <div class="py-1 px-2 rounded-md text-center shadow-md border border-gray-300" x-ref='start{{ $key }}'>{!!
              shortcode($v->options[$key]) !!}</div>
            @foreach ($o as $r)
            <div class="hidden" x-data='{open: true,lrefresh:null}' @removeline.window='open=false' x-init="
              if($refs.start{{ $key }} && $refs.end{{ $r }}){
                line{{$sid.$k.$key.$r}} = new LeaderLine($refs.start{{ $key }},$refs.end{{ $r }},{startPlug:'disc',endPlug:'disc',color: generateColor('coloradoavocado{{$v->num.$v->jenis.$r.$key}}')});
                lrefresh = setInterval(()=>{
                  if (line{{$sid.$k.$key.$r}}) {
                    line{{$sid.$k.$key.$r}}.position();
                  }
                },10);
                $watch('open', value => {
                  if(!value){
                    line{{$sid.$k.$key.$r}}.remove();
                    delete line{{$sid.$k.$key.$r}};
                    clearInterval(lrefresh);
                  }
                });
              }
            "></div>
            @endforeach
            @endif
            @endforeach
          </div>
          <div class="flex flex-col gap-2 relative">
            @if (isset($v->labels[1]))
            <div class="font-bold border-b-2 border-b-gray-600">{{ $v->labels[1] }}</div>
            @endif
            @foreach ($v->relations as $key => $o)
            @if (!is_array($o))
            <div class="py-1 px-2 rounded-md text-center shadow-md border border-gray-300" x-ref='end{{ $key }}'>{!!
              shortcode($v->options[$key]) !!}</div>
            @endif
            @endforeach
          </div>
        </div>
        @endif
      </div>
    </x-card>
    @empty
    <div class="text-center italic text-negative-600 font-bold">Soal tidak tersedia</div>
    @endforelse
    @endif
  </div>
</x-modal.card>