<div
    style="height: 100vh; width: 100vw; padding:10px; display: flex; justify-content:center; align-items: center; background-color: #f0f0f0;">

    {{-- Main Card --}}
    <div
        style="width:70vw; min-width: 300px; padding:20px; border:1px solid #ccc; border-radius: 10px; background-color: #ffffff; box-shadow: 0 4px 5px rgba(0,0,0,0,0.1);">

        <div style="width: 100%; margin-bottom: 15px;">
            @if ($image_banner)
                <img src="{{ $image_banner }}" style="width:100%; border-radius:8px;" alt="">
            @endif
        </div>

        {{-- Title Card --}}
        <div style="width: 100%; margin-bottom: 25px; margin-top: 15px;  text-align:center;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 600; color: #333;">
                LUCAS - {{ $title }}
            </h2>
        </div>

        {{-- Text Content --}}
        <p style="font-size: 16px; margin-bottom: 20px;">Hello <strong>{{ $name }}</strong>,</p>

        @if ($expiryType === 'account')
            <p
                style="text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
                This is a friendly reminder that your {{ $expiryType }} is set to expire in
                <strong>{{ $days }}</strong>. To request an extension of your {{ $expiryType }}'s validity,
                please contact <strong>SAPS</strong> directly.
            </p>
        @endif

        @if ($expiryType === 'password')
            <p
                style="text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
                This is a friendly reminder that your {{ $expiryType }} is set to expire in
                <strong>{{ $days }}</strong>. It is recommended to update your {{ $expiryType }} for security
                reasons.
            </p>

            <p
                style="margin: 20px 20px 0; text-align: justify; color: #000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
                To update your password, please follow the steps below:
            </p>

            <div
                style="display: flex; justify-content: center; margin: 10px 20px 0; padding: 5px; background-color: #f9f9f9; border-radius: 6px;">
                <ol style="font-size: 14px;">
                    <li style="font-size: 14px;">Login to LUCAS</li>
                    <li style="font-size: 14px;">On the lower-left corner, click the icon and go to
                        <strong>Settings</strong>
                    </li>
                    <li style="font-size: 14px;">Click <strong>Password</strong></li>
                    <li style="font-size: 14px;">Enter your new password and click <strong>Save</strong></li>
                </ol>
            </div>


            {{-- Login Button --}}
            <div style="text-align:center; margin: 30px 0;">
                <a href="{{ $url }}"
                    style="background-color: #F26531; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block">
                    Update Now
                </a>
            </div>

            <p style="font-size: 14px; color: #555; text-align:center;">
                If the button above doesn't work, copy and paste the link below into your browser:
                <a href="{{ $url }}">{{ $url }}</a></span>
            </p>
        @endif


        <p
            style="margin-top: 20px; text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
            If you believe this message was sent in error - for example, if your {{ $expiryType }} is not expiring,
            has already been renewed, or you have already taken action - please reach out to <strong>SAPS</strong> for
            clarification or assistance. We're here to help!
        </p>

        <p
            style="margin-top: 30px; text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
            Thank you for being with us! <br> - <strong>L</strong>oans <strong>U</strong>nified <strong>C</strong>ore
            <strong>A</strong>uxiliary <strong>S</strong>ystem
        </p>

        {{-- Footer Notes --}}
        <div style="width: 100%; margin-top: 15px;">
            <div style="width: 100%; height:10px; background-color: #F26531;"></div>

            <div
                style="
            width: 100%;
            height: 50px;
            background-color: #1B4298;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
        ">
                <p style="font-size: 12px; color: #ffffff; margin: 0;">
                    © {{ now()->year }} LUCAS - Bank of Makati
                </p>
            </div>
        </div>

    </div>

</div>
