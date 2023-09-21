<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Saml;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

final class SamlBadge implements BadgeInterface
{
    public function __construct(private SamlLoginAttributes $samlToken)
    {
    }

    public function getSamlLoginAttributes(): SamlLoginAttributes
    {
        return $this->samlToken;
    }

    public function isResolved(): bool
    {
        return true;
    }
}
