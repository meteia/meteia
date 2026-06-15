<?php

declare(strict_types=1);

namespace Meteia\Logging;

use DateTime;
use Meteia\ValueObjects\Identity\CorrelationId;
use Override;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;

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

    private int $msgNumber = 1;

    private CorrelationId $procId;

    public function __construct(
        private LoggerInterface $log,
    ) {
        $this->procId = CorrelationId::random();
    }

    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $formatted = $this->formated((string) $level, (string) $message, $context);
        $this->log->log($level, $formatted);
    }

    private function prefixed(string $level, string $message): string
    {
        $priority = (23 * 8) + self::SEVERITY[strtoupper($level)];

        return implode(' ', [
            // <PRI>VERSION
            "<{$priority}>1",
            // TIMESTAMP
            date(DateTime::ATOM),
            // HOSTNAME
            gethostname() ?: '-',
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

    /**
     * @param array<array-key, mixed> $context
     */
    private function formated(string $level, string $message, array $context): string
    {
        $sources = array_filter([
            'Meteia.log@30452' => [
                'seq' => $this->msgNumber++,
            ],
            'psr.log.context@17589' => $context,
        ]);

        $elements = [];
        foreach ($sources as $elementSourceId => $elementSourceParams) {
            $paramStrings = [];
            foreach ($elementSourceParams as $paramName => $paramValue) {
                if (\is_array($paramValue)) {
                    $paramValue = '!array!';
                } elseif (\is_object($paramValue) && !$paramValue instanceof Stringable) {
                    $paramValue = '!object!';
                }
                $paramStrings[] = sprintf(
                    '%s="%s"',
                    (string) $paramName,
                    $this->escapeParamValue((string) $paramValue),
                );
            }
            $elements[] = sprintf('[%s %s]', $elementSourceId, implode(' ', $paramStrings));
        }
        $structuredData = $elements === [] ? '' : implode('', $elements) . ' ';

        $lines = array_map('trim', explode(PHP_EOL, $message));

        $formattedMessage = trim(sprintf('%s%s', $structuredData, implode(' ␤ ', $lines)));

        return $this->prefixed($level, $formattedMessage);
    }

    private function escapeParamValue(string $value): string
    {
        $lines = array_map('trim', explode(PHP_EOL, $value));
        $lines = array_filter($lines);
        $value = implode(' ', $lines);

        return (string) preg_replace('%(["\]\\\\]+)%', '\\\\$1', $value);
    }
}
