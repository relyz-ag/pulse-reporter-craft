<?php

namespace relyzag\pulsereporter;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * Report contract v1 (docs/report-contract.md). Craft-independent so it can be unit-tested.
 */
final class Report
{
    public const NAME = 'reporter-craft';
    // Checked against the release tag.
    public const VERSION = '1.0.2';
    public const CONTRACT_VERSION = 1;

    /**
     * @param array<string, string> $require the root composer.json `require`
     * @param callable(string): ?string $installedVersion
     * @return list<array{ecosystem: string, name: string, version: string}>
     */
    public static function packages(array $require, callable $installedVersion): array
    {
        $packages = [];
        foreach (array_keys($require) as $name) {
            // Platform requirements (php, ext-*, lib-*, composer-*) have no vendor prefix.
            if (!str_contains($name, '/')) {
                continue;
            }
            $version = $installedVersion($name);
            if ($version === null) {
                continue;
            }
            $packages[] = ['ecosystem' => 'composer', 'name' => $name, 'version' => self::normalizeVersion($version)];
        }
        usort($packages, fn(array $a, array $b) => strcmp($a['name'], $b['name']));
        return $packages;
    }

    public static function normalizeVersion(string $version): string
    {
        return preg_match('/^v\d/', $version) ? substr($version, 1) : $version;
    }

    public static function phpVersion(): string
    {
        return PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
    }

    /**
     * @param list<array{ecosystem: string, name: string, version: string}> $packages
     */
    public static function build(string $craftVersion, string $edition, array $packages, ?DateTimeInterface $now = null): array
    {
        $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
        return [
            'contractVersion' => self::CONTRACT_VERSION,
            'generatedAt' => $now->format('Y-m-d\TH:i:sP'),
            'reporter' => ['name' => self::NAME, 'version' => self::VERSION],
            'runtime' => ['name' => 'php', 'version' => self::phpVersion()],
            'platform' => ['name' => 'craft', 'version' => $craftVersion, 'edition' => $edition],
            'packages' => $packages,
        ];
    }

    public static function bearerToken(?string $header): ?string
    {
        if ($header === null || !preg_match('/^Bearer\s+(\S+)\s*$/i', $header, $match)) {
            return null;
        }
        return $match[1];
    }

    public static function tokenMatches(string $given, string $expected): bool
    {
        // Hashing first keeps the comparison constant-time for different lengths.
        return hash_equals(hash('sha256', $expected), hash('sha256', $given));
    }

    /**
     * @param callable(): array $report
     * @return array{0: int, 1: ?array}
     */
    public static function respond(?string $authorization, ?string $token, callable $report): array
    {
        if ($token === null || $token === '') {
            return [404, null];
        }
        $given = self::bearerToken($authorization);
        if ($given === null || !self::tokenMatches($given, $token)) {
            return [401, null];
        }
        return [200, $report()];
    }
}
