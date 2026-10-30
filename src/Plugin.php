<?php

namespace relyzag\pulsereporter;

use craft\base\Plugin as BasePlugin;

/**
 * Exposes GET /actions/pulse-reporter/report for Relyz Pulse. Configured via the PULSE_TOKEN env var only.
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';
}
