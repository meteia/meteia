<?php

declare(strict_types=1);

namespace Meteia\Http;

use JsonSerializable;
use Meteia\Http\Responses\JsonResponse;

class UploadProgress
{
    private int $completedWork = 0;

    private int $lastUpdateSent = 0;

    private int $totalWork = 0;

    public function __construct(private readonly int $intervalNanos = 250_000_000)
    {
    }

    public function addWork(int $amount): void
    {
        $this->totalWork += $amount;
    }

    public function complete(JsonSerializable|array $data): JsonResponse
    {
        return new JsonResponse([
            'status' => 'complete',
            'completedWork' => $this->totalWork,
            'totalWork' => $this->totalWork,
            'data' => $data,
        ]);
    }

    public function completeWork(int $amount): void
    {
        $this->completedWork += $amount;
        if ($this->completedWork !== $this->totalWork && $this->shouldSendUpdate()) {
            if (!headers_sent()) {
                header('Content-Type: application/json-lines');
            }
            while (ob_get_level()) {
                ob_end_flush();
            }
            echo json_encode([
                    'status' => 'working',
                    'completedWork' => $this->completedWork,
                    'totalWork' => $this->totalWork,
                ]) . PHP_EOL;
            flush();
        }
    }

    private function shouldSendUpdate(): bool
    {
        $now = hrtime(true);
        if ($now - $this->lastUpdateSent > $this->intervalNanos) {
            $this->lastUpdateSent = $now;

            return true;
        }

        return false;
    }
}