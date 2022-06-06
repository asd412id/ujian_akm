<x-modal.card blur maxWidth="4xl" title="{{ $modalTitle }}" wire:model='showSoal' staticbackdrop
  x-on:close="$dispatch('removeline')">
  <div class="relative flex flex-col gap-3" wire:key='soal-{{ $sid }}'>
    @if ($soal)
    @php
    $itemSoal = $soal->item_soals()->orderBy('num','asc')->get()
    @endphp
    @forelse ($itemSoal as $k => $v)
    <x-card>
      <div class="flex flex-col gap-2" x-data>
        <div>
          <div class="text-sm italic font-bold text-gray-400">#Jenis: {{ strtoupper($v->type) }}, Skor: {{ $v->score }}
          </div>
          <div class="flex gap-1">
            <div>{{ ($k+1) }}.</div>
            <div class="w-full">{!! shortcode($v->text) !!}</div>
          </div>
        </div>
        @if ((strtolower($v->type)=='pg' || strtolower($v->type)=='pgk') && is_array($v->options))
        <div class="flex flex-col gap-1">
          @foreach ($v->options as $key => $o)
          <div class="{{ $v->corrects[$key]?'font-bold':'' }} flex items-start gap-2">{!! $key.'.
            <div>'.shortcode(nl2br($o)).'</div>'
            !!}
          </div>
          @endforeach
        </div>
        @elseif ((strtolower($v->type)=='is' || strtolower($v->type)=='u') && $v->answer)
        <div class="flex flex-col gap-1">
          <div class="flex gap-1 font-bold">
            <div>Jawaban:</div> {!! shortcode(nl2br($v->answer)) !!}
          </div>
        </div>
        @elseif ((strtolower($v->type)=='bs'))
        <table class="w-full">
          <thead>
            <tr>
              <th class="px-3 py-2 bg-gray-200 border border-b-2 border-gray-400">{{
                isset($v->labels[0])&&$v->labels[0]?$v->labels[0]:'Pernyataan' }}</th>
              <th class="px-3 py-2 bg-gray-200 border border-b-2 border-gray-400">{{
                isset($v->labels[1])&&$v->labels[1]?$v->labels[1]:'Jawaban' }}</th>
            </tr>
          </thead>
          @forelse ($v->options as $key => $o)
          <tr>
            <td class="px-3 py-2 border border-gray-400">{!! shortcode(nl2br($o)) !!}</td>
            <td class="px-3 py-2 text-center border border-gray-400">
              {!! $v->corrects[$key]?'<div
                class="px-2 border rounded-md shadow-md bg-positive-50 text-positive-600 border-positive-100">Benar
              </div>':'<div
                class="px-2 border rounded-md shadow-md bg-negative-50 text-negative-600 border-negative-100">Salah
              </div>'
              !!}
            </td>
          </tr>
          @empty
          <tr>
            <td coldiv="3" class="text-center">Pilihan jawaban tidak tersedia!</td>
          </tr>
          @endforelse
        </table>
        @elseif (strtolower($v->type)=='jd' && $v->relations)
        <div class="relative grid grid-cols-2 gap-48">
          <div class="relative flex flex-col gap-2">
            @if (isset($v->labels[0])&&$v->labels[0])
            <div class="flex justify-end">
              <div class="font-bold text-center border-b-2 border-b-gray-600">{{ $v->labels[0] }}</div>
            </div>
            @endif
            @foreach ($v->relations as $key => $o)
            @if (is_array($o))
            <div class="flex justify-end">
              <div class="px-2 py-1 text-center border border-gray-300 rounded-md shadow-md" x-ref='start{{ $key }}'>{!!
                shortcode(nl2br($v->options[$key])) !!}</div>
            </div>
            @foreach ($o as $r)
            <div class="hidden" x-data='{open: true,lrefresh:null}' @removeline.window='open=false' x-init="
              if($refs.start{{ $key }} && $refs.end{{ $r }}){
                line{{$sid.$k.$key.$r}} = new LeaderLine($refs.start{{ $key }},$refs.end{{ $r }},{startPlug:'disc',endPlug:'disc',color: generateColor('coloradoavocado{{$v->num.$v->jenis.$r.$key}}'),startSocket: 'right', endSocket: 'left'});
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
          <div class="relative flex flex-col gap-2">
            @if (isset($v->labels[1])&&$v->labels[1])
            <div class="flex">
              <div class="font-bold text-center border-b-2 border-b-gray-600">{{ $v->labels[1] }}</div>
            </div>
            @endif
            @foreach ($v->relations as $key => $o)
            @if (!is_array($o))
            <div class="flex">
              <div class="px-2 py-1 text-center border border-gray-300 rounded-md shadow-md" x-ref='end{{ $key }}'>{!!
                shortcode(nl2br($v->options[$key])) !!}</div>
            </div>
            @endif
            @endforeach
          </div>
        </div>
        @endif
      </div>
    </x-card>
    @empty
    <div class="italic font-bold text-center text-negative-600">Soal tidak tersedia</div>
    @endforelse
    @endif
  </div>
</x-modal.card>