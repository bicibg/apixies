@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Apixies')
<img src="{{asset('logo.png')}}" class="logo" alt="Apixies Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
