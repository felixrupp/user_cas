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

declare(strict_types=1);

namespace OCA\UserCAS\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version104000Date20200401000002 extends SimpleMigrationStep {
        public function name(): string {
                return 'Add user_cas_ticket table';
        }

        public function description(): string {
                return 'Adds table to store relation ticket <=> token';
        }

        public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
                /** @var ISchemaWrapper $schema */
                $schema = $schemaClosure();

                if (!$schema->hasTable('user_cas_ticket')) {
                        $table = $schema->createTable('user_cas_ticket');
                        $table->addColumn('ticket', 'string', [
                                'notnull' => true,
                                'length' => 200,
                        ]);
                        $table->addColumn('token', 'string', [
                                'notnull' => true,
                                'length' => 200,
                        ]);
                        $table->setPrimaryKey(['ticket']);
                        $table->addUniqueIndex(['token'], 'index_token');
                }

                return $schema;
        }
}
