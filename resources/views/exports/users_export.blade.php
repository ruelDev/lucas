<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Export PDF</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: black;
            padding: 5px;
        }

        .header {
            padding: 20px 40px;
            border-bottom: 2px solid #ddd;
        }

        .header img {
            max-height: 80px;
            width: 100%;
            object-fit: contain;
        }

        .header::after {
            content: " ";
            display: block;
            clear: both;
        }

        .image_area {
            width: 50%;
            float: left;
        }

        .title {
            margin: 5px 25px;
            float: right;
            width: 45%;
        }

        .title h2 {
            margin: 0;
            font-size: 26px;
        }

        .title p,
        .title span {
            margin: 5px 0 0;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background-color: #007BFF;
            color: white;
        }

        th,
        td {
            padding: 8px 12px;
            border: 1px solid #ccc;
            text-align: left;
            vertical-align: top;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f8ff;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        th {
            font-weight: bold;
            font-size: 13px;
        }

        td {
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="image_area">
            <img src="{{ 'data:image/jpg;base64,' . base64_encode(file_get_contents(public_path('assets/images/bmi-banner.jpg'))) }}"
                alt="Bank of Makati" />
        </div>
        <div class="title">
            <h2>User Report</h2>
            <p>Generated on: {{ date('Y-m-d h:i:sa') }}</p>
            @if (!empty($metaText))
                <span>{{ $metaText }}</span>
            @endif
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Position</th>
                <th>Role</th>
                <th>Last Modified at</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->employee_id }}</td>
                    <td>
                        {{ $user->fname }} {{ $user->mname }} {{ $user->lname }}<br>
                        {{ $user->email }}
                    </td>
                    <td>
                        {{ $user->position }} <br>
                        {{ $user->company }} <br>
                        {{ $user->area }}
                    </td>
                    <td>{{ $user->role }}</td>
                    <td>{{ $user->updated_at ? date('F, d Y, H:i:s', strtotime($user->updated_at)) : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
