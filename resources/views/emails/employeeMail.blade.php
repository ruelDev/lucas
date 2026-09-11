<div
    style="height: 100vh; width: 100vw; display: flex; justify-content:center; align-items: center; background-color: #f0f0f0;">

    {{-- Main Card --}}
    <div
        style="width:70vw; max-width:800px; min-width: 300px; padding:20px; border:1px solid #ccc; border-radius: 10px; background-color: #ffffff; box-shadow: 0 4px 5px rgba(0,0,0,0,0.1);">

        <div style="width: 100%; margin-bottom: 15px;">
            @if ($image_banner)
                <img src="{{ $image_banner }}" style="width:100%; border-radius:8px;" alt="">
            @endif
        </div>

        {{-- Title Card --}}
        <div style="width: 100%; margin-bottom: 15px; margin-top: 15px;  text-align:center;">
            <h2 style="margin: 0; font-size: 28px; font-weight: 600; color: #333;">
                {{ $title }}
            </h2>
            {{-- <p style="margin:  5px 0 0; font-size: 20px; color: #777;">
                {{ $title }}
            </p> --}}
        </div>

        {{-- Text Content --}}
        <p style="font-size: 16px; margin-bottom: 20px;">Hello <strong>{{ $name }}</strong>,</p>

        @if ($title === 'Successful Account Creation')
            <p
                style="text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
                We're excited to welcome you aboard! Below are your initial login credentials. Please use these details
                to access your account and get started:
            </p>
        @elseif ($title === 'Recovery Password Request')
            <p
                style="text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
                We received a request to reset your password. Here's your temporary password:
            </p>
        @endif

        <div
            style="
            background-color: #f9f9f9;
            border-radius: 6px;
            margin-bottom: 20px;
            padding: 16px;
            width: 100%;
            box-sizing: border-box;
        ">
            @if ($title === 'Successful Account Creation')
                <div style="
                display: flex;
                padding: 8px 0;
            ">
                    <div style="font-weight: bold; width: 160px;">
                        Employee ID:
                    </div>
                    <div>
                        {{ $employee_id }}
                    </div>
                </div>
            @endif

            <div style="
                display: flex;
                padding: 8px 0;
            ">
                <div style="font-weight: bold; width: 160px;">
                    Temporary Password:
                </div>
                <div>
                    {{ $password }}
                </div>
            </div>
        </div>


        @if ($title === 'Successful Account Creation')
            <p
                style="margin-top: 20px; text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
                For security reasons, we recommend changing your password after first login.
            </p>

            <p
                style="margin-top: 20px; text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
                If you did not request the creation of this account or believe this email was sent to you in error,
                please contact <strong>SAPS</strong> directly for assistance.
            </p>
        @elseif ($title == 'Recovery Password Request')
            <p
                style="margin-top: 20px; text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
                Please use the temporary password to log in and make sure to set a new password immediately.
            </p>

            <p
                style="margin-top: 20px; text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
                If you did not request a password reset, please contact <strong>SAPS</strong> directly to secure your
                account and ensure no unauthorized activity has occurred.
            </p>
        @endif


        {{-- Login Button --}}
        <div style="text-align:center; margin: 30px 0;">
            <a href="{{ $url }}"
                style="background-color: #F26531; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block">Login
                Now</a>
        </div>

        <p style="font-size: 14px; color: #555; text-align:center;">
            If the button above doesn't work, copy and paste the link below into your browser:
            <a href="{{ $url }}">{{ $url }}</a></span>
        </p>

        {{-- Footer Notes --}}
        <p
            style="margin-top: 20px; text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
            If you need help or have questions, feel free to reach out SAPS for your concerns
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
