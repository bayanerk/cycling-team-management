<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoadObstacleDetectionRequest;
use App\Models\RoadObstacle;
use App\Services\HuggingFaceBikeSpaceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RoadObstacleDetectionController extends Controller
{
    /**
     * Sends IMU/sensor window to the Hugging Face Space (ibrahim444/bike) and optionally persists
     * a road obstacle when the model predicts pothole (1) or speed bump (2).
     *
     * Class indices from the Space: 0 طريق عادي, 1 حفرة, 2 مطب, 3 عقبة
     */
    public function detect(RoadObstacleDetectionRequest $request, HuggingFaceBikeSpaceClient $client): JsonResponse
    {
        try {
            /** @var list<list<float|int>> $rows */
            $rows = $request->validated('data');

            $hf = $client->predict($rows);

            $classIndex = $hf['class_index'];
            $obstacleType = match ($classIndex) {
                1 => 'pothole',
                2 => 'bump',
                default => null,
            };

            $saved = null;
            if ($hf['success'] && $obstacleType !== null) {
                $confidence = null;
                if ($hf['probabilities'] !== []) {
                    $key = $obstacleType === 'pothole' ? 'حفرة' : 'مطب';
                    $confidence = $hf['probabilities'][$key] ?? null;
                    if (is_numeric($confidence)) {
                        $confidence = (float) $confidence;
                    } else {
                        $confidence = null;
                    }
                }

                $saved = RoadObstacle::recordDetection([
                    'latitude' => $request->validated('latitude'),
                    'longitude' => $request->validated('longitude'),
                    'type' => $obstacleType,
                    'confidence' => $confidence,
                    'detected_at' => now(),
                    'metadata' => [
                        'source' => 'huggingface_space',
                        'space' => 'ibrahim444/bike',
                        'prediction' => $hf['prediction'],
                        'class_index' => $classIndex,
                        'probabilities' => $hf['probabilities'],
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'model' => [
                        'success' => $hf['success'],
                        'prediction' => $hf['prediction'],
                        'class_index' => $classIndex,
                        'probabilities' => $hf['probabilities'],
                    ],
                    'road_obstacle' => $saved ? [
                        'id' => $saved->id,
                        'latitude' => (string) $saved->latitude,
                        'longitude' => (string) $saved->longitude,
                        'type' => $saved->type,
                        'reports_count' => $saved->reports_count,
                    ] : null,
                    'saved' => $saved !== null,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 502);
        } catch (\Exception $e) {
            Log::error('Road obstacle detection: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحليل',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
