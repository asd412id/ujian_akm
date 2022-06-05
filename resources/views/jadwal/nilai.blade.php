<x-modal.card fullscreen blur title="{{ $modalTitle }}" wire:model='modal' staticbackdrop
  x-on:close="$dispatch('removeline')" x-on:open="$wire.sid++">
  @if ($login)
  <div class="overflow-auto">
    <table class="w-full">
      <thead>
        <tr>
          <th class="p-2 border border-b-2 border-gray-300">No.</th>
          <th class="p-2 border border-b-2 border-gray-300">Soal</th>
          <th class="p-2 border border-b-2 border-gray-300">Jenis</th>
          <th class="p-2 border border-b-2 border-gray-300">Jawaban Benar</th>
          <th class="p-2 border border-b-2 border-gray-300">Jawaban Peserta</th>
          <th class="p-2 border border-b-2 border-gray-300">Skor</th>
          <th class="p-2 border border-b-2 border-gray-300">Nilai</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($login->tests()->orderBy('item_soal_id','asc')->get() as $k => $v)
        <tr>
          <td class="p-2 text-center align-top border border-gray-300">{{ $k+1 }}</td>
          <td class="p-2 align-top border border-gray-300">{!! shortcode($v->text) !!}</td>
          <td class="p-2 text-center align-top border border-gray-300">{{ strtoupper($v->type) }}</td>
          <td class="p-2 align-top border border-gray-300">
            @if ((strtolower($v->type)=='pg' || strtolower($v->type)=='pgk') && is_array($v->itemSoal->options))
            <div class="flex flex-col gap-1">
              @foreach ($v->itemSoal->options as $key => $o)
              <div class="{{ $v->itemSoal->corrects[$key]?'font-bold':'' }} flex items-start gap-2">{!!
                $key.'.
                <span>'.shortcode($o).'</span>'
                !!}
              </div>
              @endforeach
            </div>
            @elseif ((strtolower($v->type)=='is' || strtolower($v->type)=='u') && $v->answer)
            <div class="flex flex-col gap-1">
              <div class="flex gap-1 font-bold"><span>Jawaban:</span> {!! shortcode(nl2br($v->itemSoal->answer)) !!}
              </div>
            </div>
            @elseif ((strtolower($v->type)=='bs'))
            <table class="w-full">
              <thead>
                <tr>
                  <th class="px-3 py-2 bg-gray-200 border border-b-2 border-gray-400">{{
                    isset($v->itemSoal->labels[0])&&$v->itemSoal->labels[0]?$v->itemSoal->labels[0]:'Pernyataan' }}</th>
                  <th class="px-3 py-2 bg-gray-200 border border-b-2 border-gray-400">{{
                    isset($v->itemSoal->labels[1])&&$v->itemSoal->labels[1]?$v->itemSoal->labels[1]:'Jawaban' }}</th>
                </tr>
              </thead>
              @forelse ($v->itemSoal->options as $key => $o)
              <tr>
                <td class="px-3 py-2 align-top border border-gray-400">{!! shortcode($o) !!}</td>
                <td class="px-3 py-2 text-center align-top border border-gray-400">
                  {!! $v->itemSoal->corrects[$key]?'<span
                    class="px-2 border rounded-md shadow-md bg-positive-50 text-positive-600 border-positive-100">Benar</span>':'<span
                    class="px-2 border rounded-md shadow-md bg-negative-50 text-negative-600 border-negative-100">Salah</span>'
                  !!}
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="text-center">Pilihan jawaban tidak tersedia!</td>
              </tr>
              @endforelse
            </table>
            @elseif (strtolower($v->type)=='jd' && $v->itemSoal->relations)
            <div class="relative flex justify-between gap-32">
              <div class="relative flex flex-col gap-2">
                @if (isset($v->itemSoal->labels[0])&&$v->itemSoal->labels[0])
                <div class="font-bold text-center border-b-2 border-b-gray-600">{{ $v->itemSoal->labels[0] }}</div>
                @endif
                @foreach ($v->itemSoal->relations as $key => $o)
                @if (is_array($o))
                <div class="px-2 py-1 text-center border border-gray-300 rounded-md shadow-md"
                  x-ref='startA{{ $k.$key }}'>
                  {!!
                  shortcode($v->itemSoal->options[$key]) !!}</div>
                @foreach ($o as $r)
                <div class="hidden" x-data='{open: true, lrefresh:null}' @removeline.window='open=false' x-init="
                  if($refs.startA{{ $k.$key }} && $refs.endA{{ $k.$r }}){
                    lineA{{$sid.$k.$key.$r}} = new LeaderLine($refs.startA{{ $k.$key }},$refs.endA{{ $k.$r }},{startPlug:'disc',endPlug:'disc',startSocket: 'right', endSocket: 'left',color: generateColor('coloradoavocado{{'Aa'.$k.$r.$key}}')});
                    lrefresh = setInterval(()=>{
                        if (lineA{{$sid.$k.$key.$r}} != null) {
                          lineA{{$sid.$k.$key.$r}}.position();
                        }
                      },10);
                    $watch('open', value => {
                      if(!value){
                        if(lineA{{$sid.$k.$key.$r}} != null){
                          lineA{{$sid.$k.$key.$r}}.remove();
                          lineA{{$sid.$k.$key.$r}} = null;
                        }
                        clearInterval(lrefresh);
                        open=true;
                      }
                    });
                  }
                "></div>
                @endforeach
                @endif
                @endforeach
              </div>
              <div class="relative flex flex-col gap-2">
                @if (isset($v->itemSoal->labels[1])&&$v->itemSoal->labels[1])
                <div class="font-bold text-center border-b-2 border-b-gray-600">{{ $v->itemSoal->labels[1] }}</div>
                @endif
                @foreach ($v->itemSoal->relations as $key => $o)
                @if (!is_array($o))
                <div class="px-2 py-1 text-center border border-gray-300 rounded-md shadow-md"
                  x-ref='endA{{ $k.$key }}'>
                  {!!
                  shortcode($v->itemSoal->options[$key]) !!}</div>
                @endif
                @endforeach
              </div>
            </div>
            @endif
          </td>
          <td class="p-2 align-top border border-gray-300">
            @if ((strtolower($v->type)=='pg' || strtolower($v->type)=='pgk') && is_array($v->itemSoal->options))
            <div class="flex flex-col gap-1">
              @foreach ($v->itemSoal->options as $key => $o)
              <div class="{{ isset($v->correct[$key])&&$v->correct[$key]?'font-bold':'' }} flex items-start gap-2">{!!
                $key.'.
                <span>'.shortcode($o).'</span>'
                !!}
              </div>
              @endforeach
            </div>
            @elseif ((strtolower($v->type)=='is' || strtolower($v->type)=='u') && $v->answer)
            <div class="flex flex-col gap-1">
              <div class="flex gap-1 font-bold"><span>Jawaban:</span> {!! shortcode($v->answer) !!}</div>
            </div>
            @elseif ((strtolower($v->type)=='bs'))
            <table class="w-full">
              <thead>
                <tr>
                  <th class="px-3 py-2 bg-gray-200 border border-b-2 border-gray-400">{{
                    isset($v->label[0])?$v->label[0]:'Pernyataan' }}</th>
                  <th class="px-3 py-2 bg-gray-200 border border-b-2 border-gray-400">Jawaban</th>
                </tr>
              </thead>
              @forelse ($v->itemSoal->options as $key => $o)
              <tr>
                <td class="px-3 py-2 align-top border border-gray-400">{!! shortcode($o) !!}</td>
                <td class="px-3 py-2 text-center align-top border border-gray-400">
                  {!! isset($v->correct[$key])&&$v->correct[$key]?'<span
                    class="px-2 border rounded-md shadow-md bg-positive-50 text-positive-600 border-positive-100">Benar</span>':'<span
                    class="px-2 border rounded-md shadow-md bg-negative-50 text-negative-600 border-negative-100">Salah</span>'
                  !!}
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="text-center">Pilihan jawaban tidak tersedia!</td>
              </tr>
              @endforelse
            </table>
            @elseif (strtolower($v->type) == 'jd' && $v->itemSoal->options)
            <div class="relative flex justify-between gap-32">
              <div class="relative flex flex-col gap-2">
                @if (isset($v->label[0]))
                <div class="font-bold text-center border-b-2 border-b-gray-600">{{ $v->label[0] }}</div>
                @endif
                @foreach ($v->itemSoal->relations as $key => $o)
                @if (is_array($o))
                <div class="px-2 py-1 text-center border border-gray-300 rounded-md shadow-md"
                  x-ref='startB{{ $k.$key }}'>
                  {!!
                  shortcode($v->itemSoal->options[$key]) !!}</div>
                @foreach ($o as $r)
                <div class="hidden" x-data='{open: true, lrefresh:null}' @removeline.window='open=false' x-init="
                  if($refs.startB{{ $k.$key }} && $refs.endB{{ $k.(isset($v->relation[$key])?$v->relation[$key][0]:'nm') }}){
                    line{{$sid.$k.$key.(isset($v->relation[$key])?$v->relation[$key][0]:'nm')}} = new LeaderLine($refs.startB{{
                    $k.$key
                    }},$refs.endB{{ $k.(isset($v->relation[$key])?$v->relation[$key][0]:'nm')
                    }},{startPlug:'disc',endPlug:'disc',startSocket: 'right', endSocket: 'left',color:
                    generateColor('coloradoavocado{{'Ba'.$k.(isset($v->relation[$key])?$v->relation[$key][0]:'nm').$key}}')});
                    lrefresh = setInterval(()=>{
                      if (line{{$sid.$k.$key.(isset($v->relation[$key])?$v->relation[$key][0]:'nm')}} != null) {
                        line{{$sid.$k.$key.(isset($v->relation[$key])?$v->relation[$key][0]:'nm')}}.position();
                      }
                    },10);
                    $watch('open', value => {
                      if(!value){
                        if(line{{$sid.$k.$key.(isset($v->relation[$key])?$v->relation[$key][0]:'nm')}} != null){
                          line{{$sid.$k.$key.(isset($v->relation[$key])?$v->relation[$key][0]:'nm')}}.remove();
                          line{{$sid.$k.$key.(isset($v->relation[$key])?$v->relation[$key][0]:'nm')}} = null;
                        }
                        clearInterval(lrefresh);
                        open=true;
                      }
                    });
                  }
                "></div>
                @endforeach
                @endif
                @endforeach
              </div>
              <div class="relative flex flex-col gap-2">
                @if (isset($v->label[1]))
                <div class="font-bold text-center border-b-2 border-b-gray-600">{{ $v->label[1] }}</div>
                @endif
                @foreach ($v->itemSoal->options as $key => $o)
                @if (!is_array($v->itemSoal->relations[$key]))
                <div class="px-2 py-1 text-center border border-gray-300 rounded-md shadow-md"
                  x-ref='endB{{ $k.(is_array($v->relation) && array_search([$key],$v->relation)?$key:"not-match") }}'>
                  {!!
                  shortcode($v->itemSoal->options[$key]) !!}</div>
                @endif
                @endforeach
              </div>
            </div>
            @endif
          </td>
          <td class="p-2 text-center align-top border border-gray-300">{{ $v->score }}</td>
          <td class="p-2 text-center align-top border border-gray-300">
            <x-input type='numeric' wire:model.lazy='score.{{ $v->id }}' class="w-16 text-center" />
          </td>
        </tr>
        @empty
        <tr>
          <td class="p-2 text-center align-top border border-gray-300" colspan="7">Tidak ada soal yang dikerja</td>
        </tr>
        @endforelse
        <tr>
          <th colspan="6" class="p-2 text-right align-top border border-gray-300">Total Nilai</th>
          <th colspan="6" class="p-2 text-center align-top border border-gray-300">{{ $totalnilai }}</th>
        </tr>
      </tbody>
    </table>
    <div class="flex justify-end mt-3">
      <x-button primary label="Simpan Nilai" wire:click='updateNilai' />
    </div>
  </div>
  @endif
</x-modal.card>