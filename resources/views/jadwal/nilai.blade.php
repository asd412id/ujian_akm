<x-modal.card fullscreen blur title="{{ $modalTitle }}" wire:model='modal' staticbackdrop
  x-on:close="$dispatch('removeline')" x-on:open="$wire.sid++">
  @if ($login)
  <div class="overflow-auto">
    <table class="w-full">
      <thead>
        <tr>
          <th class="p-2 border border-gray-300 border-b-2">No.</th>
          <th class="p-2 border border-gray-300 border-b-2">Soal</th>
          <th class="p-2 border border-gray-300 border-b-2">Jenis</th>
          <th class="p-2 border border-gray-300 border-b-2">Jawaban Benar</th>
          <th class="p-2 border border-gray-300 border-b-2">Jawaban Peserta</th>
          <th class="p-2 border border-gray-300 border-b-2">Skor</th>
          <th class="p-2 border border-gray-300 border-b-2">Nilai</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($login->tests()->orderBy('item_soal_id','asc')->get() as $k => $v)
        <tr>
          <td class="align-top p-2 border border-gray-300 text-center">{{ $k+1 }}</td>
          <td class="align-top p-2 border border-gray-300">{!! shortcode($v->text) !!}</td>
          <td class="align-top p-2 border border-gray-300 text-center">{{ strtoupper($v->type) }}</td>
          <td class="align-top p-2 border border-gray-300">
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
              <div class="font-bold flex gap-1"><span>Jawaban:</span> {!! shortcode($v->itemSoal->answer) !!}</div>
            </div>
            @elseif ((strtolower($v->type)=='bs'))
            <table class="w-full">
              <thead>
                <tr>
                  <th class="py-2 px-3 border border-gray-400 border-b-2 bg-gray-200">{{
                    isset($v->itemSoal->labels[0])?$v->itemSoal->labels[0]:'Pernyataan' }}</th>
                  <th class="py-2 px-3 border border-gray-400 border-b-2 bg-gray-200">Jawaban</th>
                </tr>
              </thead>
              @forelse ($v->itemSoal->options as $key => $o)
              <tr>
                <td class="align-top py-2 px-3 border border-gray-400">{!! shortcode($o) !!}</td>
                <td class="align-top py-2 px-3 border border-gray-400 text-center">
                  {!! $v->itemSoal->corrects[$key]?'<span
                    class="bg-positive-50 text-positive-600 border border-positive-100 px-2 shadow-md rounded-md">Benar</span>':'<span
                    class="bg-negative-50 text-negative-600 border border-negative-100 px-2 shadow-md rounded-md">Salah</span>'
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
            <div class="flex justify-between gap-32 relative">
              <div class="flex flex-col gap-2 relative">
                @if (isset($v->itemSoal->labels[0]))
                <div class="font-bold border-b-2 border-b-gray-600 text-center">{{ $v->itemSoal->labels[0] }}</div>
                @endif
                @foreach ($v->itemSoal->relations as $key => $o)
                @if (is_array($o))
                <div class="py-1 px-2 rounded-md text-center shadow-md border border-gray-300"
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
              <div class="flex flex-col gap-2 relative">
                @if (isset($v->itemSoal->labels[1]))
                <div class="font-bold border-b-2 border-b-gray-600 text-center">{{ $v->itemSoal->labels[1] }}</div>
                @endif
                @foreach ($v->itemSoal->relations as $key => $o)
                @if (!is_array($o))
                <div class="py-1 px-2 rounded-md text-center shadow-md border border-gray-300"
                  x-ref='endA{{ $k.$key }}'>
                  {!!
                  shortcode($v->itemSoal->options[$key]) !!}</div>
                @endif
                @endforeach
              </div>
            </div>
            @endif
          </td>
          <td class="align-top p-2 border border-gray-300">
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
              <div class="font-bold flex gap-1"><span>Jawaban:</span> {!! shortcode($v->answer) !!}</div>
            </div>
            @elseif ((strtolower($v->type)=='bs'))
            <table class="w-full">
              <thead>
                <tr>
                  <th class="py-2 px-3 border border-gray-400 border-b-2 bg-gray-200">{{
                    isset($v->label[0])?$v->label[0]:'Pernyataan' }}</th>
                  <th class="py-2 px-3 border border-gray-400 border-b-2 bg-gray-200">Jawaban</th>
                </tr>
              </thead>
              @forelse ($v->itemSoal->options as $key => $o)
              <tr>
                <td class="align-top py-2 px-3 border border-gray-400">{!! shortcode($o) !!}</td>
                <td class="align-top py-2 px-3 border border-gray-400 text-center">
                  {!! isset($v->correct[$key])&&$v->correct[$key]?'<span
                    class="bg-positive-50 text-positive-600 border border-positive-100 px-2 shadow-md rounded-md">Benar</span>':'<span
                    class="bg-negative-50 text-negative-600 border border-negative-100 px-2 shadow-md rounded-md">Salah</span>'
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
            <div class="flex justify-between gap-32 relative">
              <div class="flex flex-col gap-2 relative">
                @if (isset($v->label[0]))
                <div class="font-bold border-b-2 border-b-gray-600 text-center">{{ $v->label[0] }}</div>
                @endif
                @foreach ($v->itemSoal->relations as $key => $o)
                @if (is_array($o))
                <div class="py-1 px-2 rounded-md text-center shadow-md border border-gray-300"
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
              <div class="flex flex-col gap-2 relative">
                @if (isset($v->label[1]))
                <div class="font-bold border-b-2 border-b-gray-600 text-center">{{ $v->label[1] }}</div>
                @endif
                @foreach ($v->itemSoal->options as $key => $o)
                @if (!is_array($v->itemSoal->relations[$key]))
                <div class="py-1 px-2 rounded-md text-center shadow-md border border-gray-300"
                  x-ref='endB{{ $k.(is_array($v->relation) && array_search([$key],$v->relation)?$key:"not-match") }}'>
                  {!!
                  shortcode($v->itemSoal->options[$key]) !!}</div>
                @endif
                @endforeach
              </div>
            </div>
            @endif
          </td>
          <td class="align-top p-2 border border-gray-300 text-center">{{ $v->score }}</td>
          <td class="align-top p-2 border border-gray-300 text-center">
            <x-input type='numeric' wire:model.lazy='score.{{ $v->id }}' class="w-16 text-center" />
          </td>
        </tr>
        @empty
        <tr>
          <td class="align-top p-2 border border-gray-300 text-center" colspan="7">Tidak ada soal yang dikerja</td>
        </tr>
        @endforelse
        <tr>
          <th colspan="6" class="align-top p-2 border border-gray-300 text-right">Total Nilai</th>
          <th colspan="6" class="align-top p-2 border border-gray-300 text-center">{{ $totalnilai }}</th>
        </tr>
      </tbody>
    </table>
    <div class="mt-3 flex justify-end">
      <x-button primary label="Simpan Nilai" wire:click='updateNilai' />
    </div>
  </div>
  @endif
</x-modal.card>