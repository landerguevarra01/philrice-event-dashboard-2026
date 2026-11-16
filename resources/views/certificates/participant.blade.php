@php
    $first = $dates->first();
    $last = $dates->last();
    $rangeText = $first->format('F j') . ' to ' . $last->format('j, Y');

    $check = fn($day, $item) => optional($requirements->get($day))->$item ? '&#10003;' : '';
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            font-family: 'DejaVu Serif', serif;
            font-size: 12.5pt;
            color: #000;
        }

        .page {
            position: relative;
            width: 842pt;
            height: 595pt;
        }

        .bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 842pt;
            height: 595pt;
        }

        .content {
            position: absolute;
            left: 81pt;
            width: 680pt;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            letter-spacing: 7pt;
        }

        .body-text {
            text-align: justify;
            line-height: 1.5;
            text-indent: 40pt;
        }

        .fill {
            font-weight: bold;
            text-decoration: underline;
        }

        table.arr {
            border-collapse: collapse;
            margin-left: 106pt;
            width: 468pt;
            font-size: 12pt;
        }

        table.arr td,
        table.arr th {
            border: 1.5pt solid #000;
            padding: 6pt 8pt;
        }

        table.arr th {
            background: #e2efda;
            text-align: left;
        }

        table.arr th.c,
        table.arr td.c {
            text-align: center;
        }

        .shade1 {
            background: #e7e6e6;
        }

        .shade2 {
            background: #f2f2f2;
        }

        .indent {
            padding-left: 20pt !important;
        }

        .tick {
            color: #e00000;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13pt;
        }
    </style>
</head>

<body>
    <div class="page">

        @if (file_exists(public_path('images/certificate-bg.png')))
            <img class="bg" src="{{ public_path('images/certificate-bg.png') }}">
        @endif

        <div class="content title" style="top: 92pt;">CERTIFICATE OF APPEARANCE</div>

        <div class="content" style="top: 135pt;">TO WHOM IT MAY CONCERN:</div>

        <div class="content body-text" style="top: 168pt;">
            This is to certify that <span class="fill">{{ strtoupper($participant->name) }}</span>
            @if ($participant->organization)
                of <span class="fill">{{ $participant->organization }}</span>
            @endif
            participated in the <b>38<sup style="font-size:8pt">th</sup> <i>Ugnay Palay</i> National Rice R4DE
                Conference</b>
            at {{ config('event.venue') }} on <b>{{ $rangeText }}.</b>
        </div>

        <div class="content" style="top: 252pt;">In addition, the following arrangements were provided:</div>

        <div class="content" style="top: 278pt;">
            <table class="arr">
                <tr>
                    <th style="width: 40%;">Date</th>
                    @foreach ($dates as $date)
                        <th class="c">{{ $date->format('M-d') }}</th>
                    @endforeach
                </tr>
                <tr class="shade1">
                    <td><b>1. Meals</b></td>
                    @foreach ($dates as $date)
                    <td></td> @endforeach
                </tr>
                <tr class="shade2">
                    <td class="indent">Lunch</td>
                    @foreach ($dates as $day => $date)
                        <td class="c"><span class="tick">{!! $check($day, 'lunch') !!}</span></td>
                    @endforeach
                </tr>
                <tr class="shade1">
                    <td class="indent">Dinner</td>
                    @foreach ($dates as $day => $date)
                        <td class="c"><span class="tick">{!! $check($day, 'dinner') !!}</span></td>
                    @endforeach
                </tr>
                <tr class="shade2">
                    <td><b>2. Accommodation</b></td>
                    @foreach ($dates as $day => $date)
                        <td class="c"><span class="tick">{!! $check($day, 'accommodation') !!}</span></td>
                    @endforeach
                </tr>
            </table>
        </div>

        <div class="content" style="top: 448pt;">
            Issued this {{ now()->format('F j, Y') }}, for reference purposes.
        </div>

    </div>
</body>

</html>