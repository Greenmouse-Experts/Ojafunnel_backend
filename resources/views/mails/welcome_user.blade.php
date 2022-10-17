@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.front_end_url')])
            <img src="{{config('settings.customer_support.logo')}}" width="60%"/>
        @endcomponent
    @endslot

    {{-- Body --}}

    @slot('subcopy')
        <h1 style="text-align:justify"> Hi {{$name}}, </h1>

        <div class="mailbody" style="color:#000">
            <h4>Welcome to Ojafunnel,</h4>
            <p>
                Your Domain: {{$name}}.{{config('settings.customer_support.domain')}}
            </p> 
        </div>
        <p>Start enjoying full control of your business all in one place .</p>
        <p style="text-align: center"><a href="https://{{$name}}.{{config('settings.customer_support.domain')}}" target="_blank">visit</a></p>

        <hr>
    @endslot

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        @endcomponent
    @endslot
@endcomponent
