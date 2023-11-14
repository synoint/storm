<?php

namespace Syno\Storm\Plugin;

class PluginManager
{
    private array $plugins = [];

    public function __construct(iterable $plugins)
    {
        $pattern = '/(\d{6})/';
        foreach ($plugins as $plugin) {
            if (preg_match($pattern, get_class($plugin), $matches)) {
                $this->plugins[$matches[1]] = $plugin;
            }
        }
    }

    public function getActivePlugin($surveyId):? PluginInterface
    {
        return $this->plugins[$surveyId] ?? null;
    }
}
