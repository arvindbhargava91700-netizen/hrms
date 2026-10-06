<?php

namespace App\Support;

class SimplePdf
{
    public static function make(array $lines, string $title = 'Invoice Receipt'): string
    {
        $pdfLines = array_map(function (string $line): string {
            return self::escapeText($line);
        }, $lines);

        $content = [];
        $content[] = 'BT';
        $content[] = '/F1 18 Tf';
        $content[] = '72 760 Td';
        $content[] = '(' . self::escapeText($title) . ') Tj';
        $content[] = '/F1 11 Tf';
        $content[] = '0 -28 Td';

        foreach ($pdfLines as $line) {
            $content[] = '(' . $line . ') Tj';
            $content[] = '0 -16 Td';
        }

        $content[] = 'ET';
        $stream = implode("\n", $content);

        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
        $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj";
        $objects[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";
        $objects[] = "5 0 obj << /Length " . strlen($stream) . " >> stream\n{$stream}\nendstream endobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 " . (count($offsets)) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($index = 1; $index < count($offsets); $index++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$index]) . "\n";
        }

        $pdf .= "trailer << /Size " . count($offsets) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefPosition}\n%%EOF";

        return $pdf;
    }

    public static function makeReceipt(array $data): string
    {
        $title = $data['title'] ?? 'Invoice Receipt';
        $invoice = $data['invoice'] ?? null;
        $payment = $data['payment'] ?? null;
        $customer = $data['customer'] ?? null;
        $subscription = $data['subscription'] ?? null;
        $listing = $data['listing'] ?? null;

        $invoiceNo = $invoice?->invoice_number ?? 'N/A';
        $receiptRef = $payment?->gateway_ref ?? 'N/A';
        $gateway = $payment?->gateway ?? 'N/A';
        $amount = (float) ($invoice?->amount ?? $payment?->amount ?? 0);
        $tax = (float) ($invoice?->tax ?? 0);
        $total = (float) ($invoice?->total ?? $payment?->amount ?? 0);
        $status = $invoice?->status ?? $payment?->status ?? 'N/A';
        $dueDate = $invoice?->due_date?->format('d M Y') ?? 'N/A';
        $paidAt = $payment?->paid_at?->format('d M Y, h:i A') ?? 'N/A';
        $packageName = $subscription?->package?->name ?? 'N/A';
        $subscriptionId = $subscription?->id ?? 'N/A';
        $customerName = $customer?->name ?? 'N/A';
        $customerEmail = $customer?->email ?? 'N/A';
        $customerMobile = $customer?->mobile ?? 'N/A';
        $listingTitle = $listing?->title ?? 'N/A';

        $content = [];
        $content[] = self::rect(0, 760, 595, 82, [37, 99, 235], true);
        $content[] = self::text(42, 795, 20, $title, [255, 255, 255]);
        $content[] = self::text(42, 777, 10, 'Invoice #' . $invoiceNo, [219, 234, 254]);
        $content[] = self::text(430, 794, 11, 'Receipt Ref: ' . $receiptRef, [255, 255, 255]);
        $content[] = self::text(430, 778, 11, 'Gateway: ' . $gateway, [255, 255, 255]);

        $content[] = self::rect(40, 640, 245, 98, [255, 255, 255], false, [226, 232, 240]);
        $content[] = self::rect(310, 640, 245, 98, [255, 255, 255], false, [226, 232, 240]);

        $content[] = self::text(56, 716, 9, 'Customer', [107, 114, 128]);
        $content[] = self::text(56, 696, 13, $customerName, [17, 24, 39]);
        $content[] = self::text(56, 679, 10, $customerEmail, [75, 85, 99]);
        $content[] = self::text(56, 664, 10, $customerMobile, [75, 85, 99]);

        $content[] = self::text(326, 716, 9, 'Listing & Package', [107, 114, 128]);
        $content[] = self::text(326, 696, 13, $listingTitle, [17, 24, 39]);
        $content[] = self::text(326, 679, 10, $packageName, [75, 85, 99]);
        $content[] = self::text(326, 664, 10, 'Subscription #' . $subscriptionId, [75, 85, 99]);

        $content[] = self::rect(40, 280, 515, 340, [255, 255, 255], false, [226, 232, 240]);
        $content[] = self::rect(40, 580, 515, 40, [248, 250, 252], true);
        $content[] = self::text(56, 595, 11, 'Invoice Details', [17, 24, 39]);

        $rows = [
            ['Invoice No:', $invoiceNo],
            ['Receipt Ref:', $receiptRef],
            ['Gateway:', $gateway],
            ['Amount:', 'INR ' . number_format($amount, 2)],
            ['Tax:', 'INR ' . number_format($tax, 2)],
            ['Total:', 'INR ' . number_format($total, 2)],
            ['Status:', $status],
            ['Due Date:', $dueDate],
            ['Paid At:', $paidAt],
        ];

        $rowTop = 580;
        $rowHeight = 33;

        foreach ($rows as $index => [$label, $value]) {
            $y = $rowTop - (($index + 1) * $rowHeight);
            $content[] = self::line(40, $y + 1, 555, $y + 1, [226, 232, 240]);
            $content[] = self::text(56, $y + 12, 10, $label, [75, 85, 99]);
            $content[] = self::text(240, $y + 12, 10, (string) $value, [17, 24, 39]);
        }

        $content[] = self::rect(40, 214, 515, 46, [239, 246, 255], false, [191, 219, 254]);
        $content[] = self::text(56, 242, 10, 'Status', [37, 99, 235]);
        $content[] = self::text(240, 242, 10, $status, [17, 24, 39]);
        $content[] = self::text(56, 226, 9, 'This receipt is generated by Feetrack and is valid for records and printing.', [75, 85, 99]);

        $content[] = self::text(40, 176, 8, 'Print or save this PDF for your records.', [107, 114, 128]);

        $stream = implode("\n", $content);

        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
        $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj";
        $objects[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";
        $objects[] = "5 0 obj << /Length " . strlen($stream) . " >> stream\n{$stream}\nendstream endobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 " . count($offsets) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($index = 1; $index < count($offsets); $index++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$index]) . "\n";
        }

        $pdf .= "trailer << /Size " . count($offsets) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefPosition}\n%%EOF";

        return $pdf;
    }

    private static function rect(float $x, float $y, float $width, float $height, array $fillRgb = [255, 255, 255], bool $fill = false, ?array $strokeRgb = null): string
    {
        $parts = [];
        $parts[] = self::setFillColor($fillRgb);
        if ($strokeRgb) {
            $parts[] = self::setStrokeColor($strokeRgb);
        }
        $parts[] = sprintf('%.2f %.2f %.2f %.2f re', $x, $y, $width, $height);
        $parts[] = $fill ? 'B' : 'S';
        return implode("\n", $parts);
    }

    private static function line(float $x1, float $y1, float $x2, float $y2, array $rgb = [0, 0, 0]): string
    {
        return implode("\n", [
            self::setStrokeColor($rgb),
            sprintf('%.2f %.2f m %.2f %.2f l S', $x1, $y1, $x2, $y2),
        ]);
    }

    private static function text(float $x, float $y, float $size, string $text, array $rgb = [0, 0, 0]): string
    {
        return implode("\n", [
            self::setFillColor($rgb),
            'BT',
            '/F1 ' . $size . ' Tf',
            sprintf('1 0 0 1 %.2f %.2f Tm', $x, $y),
            '(' . self::escapeText($text) . ') Tj',
            'ET',
        ]);
    }

    private static function setFillColor(array $rgb): string
    {
        return sprintf('%.3f %.3f %.3f rg', $rgb[0] / 255, $rgb[1] / 255, $rgb[2] / 255);
    }

    private static function setStrokeColor(array $rgb): string
    {
        return sprintf('%.3f %.3f %.3f RG', $rgb[0] / 255, $rgb[1] / 255, $rgb[2] / 255);
    }

    private static function escapeText(string $text): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $text);

        return preg_replace('/[^\x20-\x7E]/', '?', $text) ?? $text;
    }
}
