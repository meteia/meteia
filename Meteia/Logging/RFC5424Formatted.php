<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Meteia\ValueObjects\Identity\CorrelationId;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

use function Polyfills\array_map_assoc;

class RFC5424Formatted extends AbstractLogger
{
    private const SEVERITY = [
        'DEBUG' => 7,
        'INFO' => 6,
        'NOTICE' => 5,
        'WARNING' => 4,
        'ERROR' => 3,
        'CRITICAL' => 2,
        'ALERT' => 1,
        'EMERGENCY' => 0,
    ];

    private $msgNumber = 1;

    private $procId;

    public function __construct(private LoggerInterface $log)
    {
        $this->procId = CorrelationId::random();
    }

    public function log($level, $message, array $context = []): void
    {
        $formatted = $this->formated($level, $message, $context);
        $this->log->log($level, $formatted);
    }

    private function prefixed(string $level, string $message): string
    {
        $priority = (23 * 8) + self::SEVERITY[strtoupper($level)];

        return implode(' ', [
            // <PRI>VERSION
            "<{$priority}>1",

            // TIMESTAMP
            date(\DateTime::ATOM),

            // HOSTNAME
            gethostname() ?? '-',

            // APP-NAME
            'appName',

            // PROCID
            substr((string) $this->procId, 0, 32),

            // MSGID
            'subsystem',

            // MESSAGE
            $message,
        ]);
    }

    private function formated(string $level, string $message, array $context): string
    {
        $elements = array_map_assoc(function ($elementSourceId, $elementSourceParams) {
            $elementParams = array_map_assoc(function ($paramName, $paramValue) {
                if (\is_object($paramValue) || \is_array($paramValue)) {
                    $paramValue = '!' . \gettype($paramValue) . '!';
                }

                return [$paramName => sprintf('%s="%s"', $paramName, $this->escapeParamValue((string) $paramValue))];
            }, $elementSourceParams);
            $element = sprintf('[%s %s]', $elementSourceId, implode(' ', $elementParams));

            return [$elementSourceId => $element];
        }, array_filter([
            'Meteia.log@30452' => [
                'seq' => $this->msgNumber++,
            ],
            'psr.log.context@17589' => $context,
        ]));
        $structuredData = \count($elements) ? implode('', $elements) . ' ' : '';

        $lines = array_map('trim', explode(PHP_EOL, $message));

        $formattedMessage = trim(sprintf('%s%s', $structuredData, implode(' ␤ ', $lines)));

        return $this->prefixed($level, $formattedMessage);
    }

    private function escapeParamValue($value): string
    {
        $lines = array_map('trim', explode(PHP_EOL, $value));
        $lines = array_filter($lines);
        $value = implode(' ', $lines);

        return preg_replace('%(["\]\\\\]+)%', '\\\\$1', $value);
    }
}
