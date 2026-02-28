<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print PO Beef - {{ $po->po_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 4px;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .item-table th,
        .item-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .signature-area {
            width: 100%;
            margin-top: 50px;
        }

        .signature-box {
            display: inline-block;
            width: 30%;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>PURCHASE ORDER (BEEF)</h2>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>PO Number</strong></td>
            <td width="35%">: {{ $po->po_number }}</td>
            <td width="15%"><strong>Supplier</strong></td>
            <td width="35%">: {{ $po->supplier->name }}</td>
        </tr>
        <tr>
            <td><strong>Date</strong></td>
            <td>: {{ \Carbon\Carbon::parse($po->po_date)->format('d F Y') }}</td>
            <td><strong>NPWP/Tax</strong></td>
            <td>: {{ $po->supplier->has_tax ? 'PKP (11%)' : 'Non-PKP' }}</td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="45%">Product Description</th>
                <th width="15%">Qty (Kg)</th>
                <th width="15%">Unit Price</th>
                <th width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ number_format($item->qty, 2, ',', '.') }}</td>
                <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Grand Total</th>
                <th>Rp {{ number_format($po->total_amount, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="signature-area">
        <div class="signature-box">
            <p>Prepared By (Requester)</p>
            <br><br><br>
            <p><strong>({{ $po->requisition->user->name }})</strong></p>
        </div>
        <div class="signature-box">
            <p>Reviewed By (Purchasing)</p>
            <br><br><br>
            <p><strong>(Ayu)</strong></p>
        </div>
        <div class="signature-box">
            <p>Approved By (Finance)</p>
            <br><br><br>
            <p><strong>({{ $po->approver->name }})</strong></p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };

        // Fungsi ini akan jalan tepat setelah dialog print ditutup
        window.onafterprint = function() {
            window.close();
        };
    </script>

</body>

</html>