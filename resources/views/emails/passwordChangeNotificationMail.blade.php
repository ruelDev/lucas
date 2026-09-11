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
        <p style="font-size: 16px; margin-bottom: 20px;">
            Hello <strong>{{ $name }}</strong>,
        </p>

        <p
            style="text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
            This email is to confirm that the password associated with your LUCAS account has been
            successfully changed.
        </p>

        <p
            style="text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
            If you made this change, no further action is required. You may continue using your account
            using your newly updated password.
        </p>

        <p
            style="text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
            For security purposes, we recommend keeping your password confidential and ensuring it is not
            shared with anyone. Regularly updating your password helps maintain the security of your
            account.
        </p>

        <p
            style="margin-top: 20px; text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
            <strong>Did not request this password change?</strong>
        </p>

        <p
            style="text-align: justify; color:#000; font-size: 14px; font-weight: 400; margin-bottom: 10px; padding: 0 5px;">
            If you did not initiate this change, your account may have been accessed without your
            authorization. Please contact <strong>SAPS</strong> immediately so appropriate actions can be
            taken to help secure your account.
        </p>

        {{-- Login Button --}}
        <div style="text-align:center; margin: 30px 0;">
            <a href="{{ $url }}"
                style="background-color: #F26531; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block">
                Login Now
            </a>
        </div>

        <p style="font-size: 14px; color: #555; text-align:center;">
            If the button above doesn't work, copy and paste the link below into your browser:
            <a href="{{ $url }}">{{ $url }}</a>
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
