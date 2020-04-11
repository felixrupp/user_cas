<?php
/**
 * ownCloud - user_cas
 *
 * @author Vincent Laffargue <vincent.laffargue@gmail.com>
 * @copyright Vincent Laffargue <vincent.laffargue@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\UserCAS\BackgroundJob;

use \OC;
use \OCA\UserCAS\Service\PhpCasTicketManager\PhpCasTicketManager;
use \OCP\BackgroundJob\Job;

/**
 * Class TicketCleanupJob
 *
 * @package OCA\UserCAS\BackgroundJob
 *
 * @author Vincent Laffargue <vincent.laffargue@gmail.com>
 * @copyright Vincent Laffargue <vincent.laffargue@gmail.com>
 *
 * @since 1.8.4
 */
class TicketCleanupJob extends Job {

        protected function run($argument) {
                /* @var $provider IProvider */
                $phpCasTicketManager = new PhpCasTicketManager();
                $phpCasTicketManager->deleteTicketWithoutValideToken();
        }
}
