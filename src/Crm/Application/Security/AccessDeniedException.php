<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Security;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class AccessDeniedException extends AccessDeniedHttpException
{
}
