<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MomoPaymentService
{
    public function createPayment(int $amount, string $orderId, string $orderInfo = 'Thanh toán đơn hàng'): array
    {
        $partnerCode = config('services.momo.partner_code');
        $accessKey   = config('services.momo.access_key');
        $secretKey   = config('services.momo.secret_key');
        $endpoint    = config('services.momo.endpoint');
        $ipnUrl      = config('services.momo.ipn_url');
        $returnUrl   = config('services.momo.return_url');

        $requestId   = (string) now()->timestamp . rand(1000, 9999);
        $extraData   = '';

        // Build raw signature string
        $rawSignature = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}"
            . "&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}"
            . "&redirectUrl={$returnUrl}&requestId={$requestId}";

        $signature = hash_hmac('sha256', $rawSignature, $secretKey);

        $payload = [
            'partnerCode' => $partnerCode,
            'accessKey'   => $accessKey,
            'requestId'   => $requestId,
            'amount'      => (string) $amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $returnUrl,
            'ipnUrl'      => $ipnUrl,
            'extraData'   => $extraData,
            'lang'        => 'vi',
            'signature'   => $signature,
        ];

        $res = Http::post($endpoint, $payload);

        if (!$res->ok()) {
            throw new \RuntimeException('MoMo API error: ' . $res->body());
        }

        return $res->json();
    }

    public function verifySignature(array $params): bool
    {
        $secretKey = config('services.momo.secret_key');

        // Adjust fields according to MoMo’s docs
        $raw = "amount={$params['amount']}"
             . "&extraData={$params['extraData']}"
             . "&message={$params['message']}"
             . "&orderId={$params['orderId']}"
             . "&orderInfo={$params['orderInfo']}"
             . "&orderType={$params['orderType']}"
             . "&payType={$params['payType']}"
             . "&responseTime={$params['responseTime']}"
             . "&resultCode={$params['resultCode']}"
             . "&transId={$params['transId']}";

        $calc = hash_hmac('sha256', $raw, $secretKey);

        return hash_equals($calc, $params['signature'] ?? '');
    }
}
