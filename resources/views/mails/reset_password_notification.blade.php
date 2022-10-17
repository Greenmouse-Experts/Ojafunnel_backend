@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.front_end_url')])
            <img src="https://res.cloudinary.com/greenmouse-tech/image/upload/v1660217514/OjaFunnel-Images/Logo_s0wfpp.png" width="60%"/>
        @endcomponent
    @endslot

    {{-- Body --}}

    @slot('subcopy')
        <h1 style="text-align:justify"> Hi {{$name}}, </h1>

        <p class="mailbody">
            Please be informed that your reset password was successfully done on {{ config('app.name') }} Account at {{date('F j, Y : h:i:s A',strtotime($date))}}.
        </p>
        <br>
        
        <ul style="list-style: none">

            <li> IP Address : {{$ip}} </li>
            <li> Channel : {{$channel}}</li>
            @if($channel == "Mobile")
                <li> Operating System : {{$os}}</li>
                <li> Version : {{$version}}</li>
            @endif
            @if($channel == "Web")
                <li> Browser Name : {{$browserName}}</li>
                <li> Browser Version : {{$version}}</li>
            @endif
        </ul>

        <br/>
        <br/>
        <p class="mailbody">
           If you did not request for password reset in to your {{ config('app.name') }} Account at the time detailed above, please call <a href="tel:{{ config('settings.customer_support.phone') }}"> {{ config('settings.customer_support.phone') }} </a>; or <a href="mailto:{{ config('settings.customer_support.email') }}">email {{ config('settings.customer_support.email') }} </a>immediately. <br/>
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
