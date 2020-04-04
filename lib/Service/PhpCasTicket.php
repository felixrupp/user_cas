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

namespace OCA\UserCAS\Service;

use \OCP\IConfig;
use \OCP\IDBConnection;
use \OCP\IRequest;
use \OCP\ISession;

/**
 * Class PhpCasTicket
 *
 * @package OCA\UserCAS\Service
 *
 * @author Vincent Laffargue <vincent.laffargue@gmail.com>
 * @copyright Vincent Laffargue <vincent.laffargue@gmail.com>
 *
 * @since 1.8.4
 */
class PhpCasTicket
{
    /**
     * @var \OCP\IConfig $appConfig
     */
    private $config;

    /**
     * @var IDBConnection
     */
    private $connection;

    /**
     * @var ISession $session
     */
    private $session;


    public function __construct(IDBConnection $connection, IConfig $config, ISession $session) {
        $this->connection = $connection;
	$this->config = $config;
	$this->session = $session;
    }

    /**
     * This function save the encrypted ticket of phpCAS if present in _GET['ticket'].
     * @var casTicket;
     */
    public function saveTicket() {
	if (isset($_GET['ticket'])) {
	    $ticket = (isset($_GET['ticket']) ? $_GET['ticket'] : null);
            if (preg_match('/^[SP]T-/', $ticket) ) {
	        if (session_id()=="")
		    session_start();
	        $_SESSION['user_cas_ticket'] = $this->hashTicket($ticket);
	    }
	}
    }

    /**
     * This function get the encrypted ticket of phpCAS if present in _SESSION['user_cas_ticket'].
     * And unset it
     * @var casTicket;
     */
    private function getUniqueTicket() {
	if (session_id()=="")
	    session_start();
	if (isset($_SESSION['user_cas_ticket'])) {
	    $ticket = $_SESSION['user_cas_ticket'];
	    unset($_SESSION['user_cas_ticket']);
	    return $ticket;
	}
	return NULL;
    }

    /**
     * This function save the encrypted ticket of phpCAS with encrypted SessionId.
     * @var casTicket;
     */
    public function saveTokenTicketDb() {
	$qb = $this->connection->getQueryBuilder();
	$qb->insert('user_cas_ticket')
	    ->setValue('ticket', '?')
	    ->setValue('token', '?')
            ->setParameter(0, $this->getUniqueTicket())
	    ->setParameter(1, $this->hashTicket($this->session->getId()))
	    ->execute();
    }

     /**
     * This function delete token corresponding at ticket
     * @var casTicket;
     */
    public function invalidateTokenByTicket($ticket) {
	$sql = "DELETE FROM oc_authtoken WHERE token IN(SELECT token FROM oc_user_cas_ticket WHERE ticket = ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(1, $this->hashTicket($ticket), \PDO::PARAM_STR);
	$stmt->execute();

	$sql = "DELETE FROM oc_user_cas_ticket WHERE ticket = ?";
	$stmt = $this->connection->prepare($sql);
        $stmt->bindParam(1, $this->hashTicket($ticket), \PDO::PARAM_STR);
	$stmt->execute();
    }

    /**
     * This function delete the SessionId
     */
    public function deleteTicket() {
	$sql = "DELETE FROM oc_user_cas_ticket WHERE token = ?";
	$stmt = $this->connection->prepare($sql);
        $stmt->bindParam(1, $this->hashTicket($this->session->getId()), \PDO::PARAM_STR);
	$stmt->execute();
    }

    private function hashTicket(string $ticket): string {
	$secret = $this->config->getSystemValue('secret');
	return hash('sha512', $ticket . $secret);
    }
}