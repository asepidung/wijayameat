<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>GRC - {{ $record->receiving_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0;
            font-size: 10px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .label {
            width: 150px;
            font-weight: bold;
        }

        .separator {
            width: 10px;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .item-table th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .item-table td {
            padding: 6px 8px;
            border-bottom: 1px dotted #ccc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer-table {
            width: 100%;
            margin-top: 30px;
        }

        .footer-table td {
            text-align: center;
            width: 33%;
        }

        .signature-box {
            margin-top: 60px;
            font-weight: bold;
            border-top: 1px solid #000;
            display: inline-block;
            width: 150px;
        }

        .total-row {
            font-weight: bold;
            border-top: 2px solid #000;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Wijaya Meat Indonesia</h1>
        <p>Gudang Pengolahan Sapi Hidup & Daging Sapi Segar</p>
        <p>Jl. Raya Industri No. 123, Indonesia | Telp: (021) 888-999</p>
    </div>

    <h2 class="text-center" style="text-decoration: underline; margin-bottom: 20px;">GOOD RECEIPT CATTLE (GRC)</h2>

    <table class="info-table">
        <tr>
            <td class="label">GRC Number</td>
            <td class="separator">:</td>
            <td><strong>{{ $record->receiving_number }}</strong></td>
            <td class="label">Supplier</td>
            <td class="separator">:</td>
            <td>{{ $record->supplier->name }}</td>
        </tr>
        <tr>
            <td class="label">Receive Date</td>
            <td class="separator">:</td>
            <td>{{ \Carbon\Carbon::parse($record->receive_date)->format('d F Y') }}</td>
            <td class="label">PO Number</td>
            <td class="separator">:</td>
            <td>{{ $record->purchaseOrder->po_number }}</td>
        </tr>
        <tr>
            <td class="label">Doc Number</td>
            <td class="separator">:</td>
            <td>{{ $record->doc_no ?? '-' }}</td>
            <td class="label">Health Status</td>
            <td class="separator">:</td>
            <td>
                SV: {{ $record->sv_ok ? '[✔] OK' : '[ ]' }} |
                SKKH: {{ $record->skkh_ok ? '[✔] OK' : '[ ]' }}
            </td>
        </tr>
        @if($record->note)
        <tr>
            <td class="label">Note</td>
            <td class="separator">:</td>
            <td colspan="4">{{ $record->note }}</td>
        </tr>
        @endif
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Eartag Number</th>
                <th width="20%" class="text-center">Class</th>
                <th width="20%" class="text-right">Weight (Kg)</th>
                <th width="30%">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $item->eartag }}</strong></td>
                <td class="text-center">{{ $item->category->name }}</td>
                <td class="text-right">{{ number_format($item->initial_weight, 2, ',', '.') }}</td>
                <td>{{ $item->notes ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL ({{ $record->items->count() }} Heads)</td>
                <td class="text-right">{{ number_format($record->items->sum('initial_weight'), 2, ',', '.') }} Kg</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-table">
        <tr>
            <td>
                <p>Warehouse Admin,</p>
                <div class="signature-box"></div>
                <p>{{ $record->creator->name }}</p>
            </td>
        </tr>
    </table>

    <div class="no-print" style="margin-top: 50px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: #fff; border: none; cursor: pointer;">Print Now</button>
    </div>

</body>

</html>