<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

class PaymentController extends Controller
{
    public function personalQr(Request $request)
    {
        try {
            // Receiver: prefer config, fallback to a safe default (digits only)
            $receiver = Config::get('services.momo_personal.receiver', '0901234567');
            $receiver = preg_replace('/\D/', '', (string) $receiver);

            if (! preg_match('/^\d{9,11}$/', $receiver)) {
                return response()->json(['error' => 'Invalid MoMo receiver configured'], 422);
            }

            // Determine amount: prefer receipt total if receipt_id provided
            $amount = null;
            $receiptId = $request->query('receipt_id') ?? $request->input('receipt_id');

            if ($receiptId) {
                $receipt = Receipt::find($receiptId);
                if (! $receipt) {
                    return response()->json(['error' => 'Receipt not found'], 404);
                }
                // Ensure integer VND
                $amount = (int) round($receipt->total);
            } else {
                $amount = (int) $request->query('amount', $request->input('amount', 0));
            }

            if ($amount <= 0) {
                return response()->json(['error' => 'Invalid amount. Provide a positive integer amount or a valid receipt_id.'], 422);
            }

            $comment = (string) $request->query('comment', $request->input('comment', 'Thanh toán đơn hàng'));
            $comment = trim($comment);

            // Build MoMo personal URI (includes amount)
            $params = http_build_query([
                'action'   => 'pay',
                'receiver' => $receiver,
                'amount'   => (string) $amount,
                'comment'  => $comment,
            ]);
            $uri = "momo://?{$params}";

            // Create QR (PNG)
            $qrCode = QrCode::create($uri);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            return Response::make($result->getString(), 200, ['Content-Type' => 'image/png']);
        } catch (\Throwable $e) {
            Log::error('personalQr error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Unable to generate QR code'], 500);
        }
    }
}
