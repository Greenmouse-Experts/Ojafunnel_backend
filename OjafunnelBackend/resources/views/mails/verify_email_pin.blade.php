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

        <p class="mailbody">
            Thank you for creating an account on {{ config('app.name') }}.
            <br/>
            <br/>
            Please enter the PIN below to activate your account
        </p>

        <h1 style="text-align:center;">{{$pin}}</h1>

        <br/>
        <br/>
        <p class="mailbody">
            Please note that this verification PIN will expire in 60 minutes. <br/>
        </p>

        <hr>
    @endslot

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        @endcomponent
    @endslot
@endcomponent
