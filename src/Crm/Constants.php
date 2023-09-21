<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm;

/**
 * Some "very" global constants for Kimai.
 */
class Constants
{
    /**
     * The current release version
     */
    public const VERSION = '2.0.34';
    /**
     * The current release: major * 10000 + minor * 100 + patch
     */
    public const VERSION_ID = 20034;
    /**
     * The software name
     */
    public const SOFTWARE = 'BroWorld';
    /**
     * Used in multiple views
     */
    public const GITHUB = 'https://github.com/rami-aouinti/BroWorld';
    /**
     * The Github repository name
     */
    public const GITHUB_REPO = 'rami-aouinti';
    /**
     * Homepage, used in multiple views
     */
    public const HOMEPAGE = 'https://www.kimai.org';
    /**
     * Default color for Customer, Project and Activity entities
     */
    public const DEFAULT_COLOR = '#d2d6de';
}
