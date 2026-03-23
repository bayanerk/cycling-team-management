<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Calls the Hugging Face Space FastAPI app (ibrahim444/bike).
 *
 * @see https://huggingface.co/spaces/ibrahim444/bike
 */
class HuggingFaceBikeSpaceClient
{
    /**
     * @param  list<list<float|int>>  $sensorRows  Each row: accel_x,y,z, magnitude, gyro_x,y,z, rotation (8 values)
     * @return array{success: bool, prediction: string, probabilities: array<string, float>, class_index: int}
     */
    public function predict(array $sensorRows): array
    {
        $cfg = config('services.huggingface_bike');
        $base = $cfg['base_url'] ?? '';
        $timeout = $cfg['timeout'] ?? 120;
        $token = $cfg['token'] ?? null;

        if ($base === '') {
            throw new \RuntimeException('HUGGINGFACE_BIKE_SPACE_URL is not configured.');
        }

        $url = $base.'/predict';

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        if (filled($token)) {
            $headers['Authorization'] = 'Bearer '.$token;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout($timeout)
                ->connectTimeout(30)
                ->post($url, ['data' => $sensorRows]);

            if (! $response->successful()) {
                Log::warning('HuggingFace bike space HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $response->throw();
            }

            /** @var array<string, mixed> $json */
            $json = $response->json();

            return [
                'success' => (bool) ($json['success'] ?? false),
                'prediction' => (string) ($json['prediction'] ?? ''),
                'probabilities' => is_array($json['probabilities'] ?? null) ? $json['probabilities'] : [],
                'class_index' => (int) ($json['class_index'] ?? -1),
            ];
        } catch (RequestException $e) {
            Log::error('HuggingFace bike space request failed: '.$e->getMessage());

            throw new \RuntimeException(
                'تعذر الاتصال بنموذج Hugging Face. تحقق من الرابط أو أن الـ Space غير نائم.',
                0,
                $e
            );
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function health(): array
    {
        $cfg = config('services.huggingface_bike');
        $base = $cfg['base_url'] ?? '';
        $token = $cfg['token'] ?? null;

        if ($base === '') {
            throw new \RuntimeException('HUGGINGFACE_BIKE_SPACE_URL is not configured.');
        }

        $headers = ['Accept' => 'application/json'];
        if (filled($token)) {
            $headers['Authorization'] = 'Bearer '.$token;
        }

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->get($base.'/health');

        $response->throw();

        /** @var array<string, mixed> */
        return $response->json();
    }
}
