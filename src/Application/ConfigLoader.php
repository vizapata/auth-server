<?php

namespace App\Application;

class ConfigLoader
{

    private static $instance = null;
    private $settings = null;

    private function __construct()
    {
        $this->loadSettings();
    }

    public static function getInstance(): self
    {
        if (static::$instance == null) static::$instance = new self();
        return static::$instance;
    }

    public function getSettings(?string $section = null)
    {
        if ($section !== null) {
            return isset($this->settings[$section]) ? $this->settings[$section] : null;
        }
        return $this->settings;
    }

    public function get(?string $section = null, string $settingName)
    {
        $settings = $this->getSettings($section);
        return isset($settings[$settingName]) ? $settings[$settingName] : null;
    }

    private function loadSettings()
    {
        // TODO: Include yaml parser
        // $this->settings = yaml_parse_file(APP_CONFIG_FILE);
        $this->settings = parse_ini_file(APP_CONFIG_FILE, true, INI_SCANNER_TYPED);
    }
}
