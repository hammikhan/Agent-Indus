@if(@$outbond)
    @foreach ($outbond as $rule)
        @php
            $ruleText = $rule['Text'];
            $ruleText = str_replace('CHANGES', '<strong>CHANGES</strong>', $ruleText);
            $ruleText = str_replace('CANCELLATIONS', '<strong>CANCELLATIONS</strong>', $ruleText);

            $ruleText = nl2br($ruleText);
        @endphp
        @if (@$rule['attributes'])
            @if ($rule['attributes']['RPH'] == 16)
            
                <h6>{{ $rule['attributes']['RPH'] }}. {{ $rule['attributes']['Title'] }}</h6>
                <p>{!! $ruleText !!}</p>
            @endif
        @else
            <p>{!! $ruleText !!}</p>
        @endif
    @endforeach
@endif
@if(@$inbound)
    @foreach ($inbound as $rule)
        @php
            $ruleText = $rule['Text'];
            $ruleText = str_replace('CHANGES', '<strong>CHANGES</strong>', $ruleText);
            $ruleText = str_replace('CANCELLATIONS', '<strong>CANCELLATIONS</strong>', $ruleText);

            $ruleText = nl2br($ruleText);
        @endphp
        @if (@$rule['attributes'])    
            @if ($rule['attributes']['RPH'] == 16)
            
                <h6>{{ $rule['attributes']['RPH'] }}. {{ $rule['attributes']['Title'] }}</h6>
                <p>{!! $ruleText !!}</p>
            @endif
        @else
            <p>{!! $ruleText !!}</p>
        @endif
    @endforeach
@endif