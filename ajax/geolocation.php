<?php
/**
 * LICENSE
 *
 * Copyright © 2016-2018 Teclib'
 * Copyright © 2010-2018 by the FusionInventory Development Team.
 *
 * This file is part of Flyve MDM Plugin for GLPI.
 *
 * Flyve MDM Plugin for GLPI is a subproject of Flyve MDM. Flyve MDM is a mobile
 * device management software.
 *
 * Flyve MDM Plugin for GLPI is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * Flyve MDM Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with Flyve MDM Plugin for GLPI. If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @author    Thierry Bugier
 * @copyright Copyright © 2018 Teclib
 * @license   http://www.gnu.org/licenses/agpl.txt AGPLv3+
 * @link      https://github.com/flyve-mdm/glpi-plugin
 * @link      https://flyve-mdm.com/
 * ------------------------------------------------------------------------------
 */

include '../../../inc/includes.php';
$plugin = new Plugin();
if (!$plugin->isActivated('flyvemdm')) {
   Html::displayNotFoundError();
}

Session::checkRight("flyvemdm:flyvemdm", PluginFlyvemdmProfile::RIGHT_FLYVEMDM_USE);

// a computer ID is mandatory
if (!isset($_REQUEST['computers_id'])) {
   die();
}
$computerId = intval($_REQUEST['computers_id']);

$beginDate = '0000-00-00 00:00:00';
if (isset($_REQUEST['beginDate']) && !empty(trim($_REQUEST['beginDate']))) {
   $beginDate = new DateTime($_REQUEST['beginDate']);
   $beginDate = $beginDate->format('Y-m-d H:i:s');
}

$endDate = date('Y-m-d H:i:s');
if (isset($_REQUEST['endDate']) && !empty(trim($_REQUEST['endDate']))) {
   $endDate = new DateTime($_REQUEST['endDate']);
   $endDate = $endDate->format('Y-m-d H:i:s');
}

$condition = [
   'WHERE' => [
      Computer::getForeignKeyField() => $computerId,
   ],
];
if ($beginDate == '0000-00-00 00:00:00') {
   $condition['WHERE']['date'] = ['<' => $endDate];
   $condition['LIMIT'] = '100';
} else {
   $condition['WHERE']['date'] = new \QueryExpression("BETWEEN '$beginDate' AND '$endDate'");
}
$rows = $DB->request([
   'FROM' => PluginFlyvemdmGeolocation::getTable(),
] + $condition);

$markers = [];
foreach ($rows as $row) {
   $markers[] = [
      'date'      => $row['date'],
      'latitude'  => $row['latitude'],
      'longitude'  => $row['longitude'],
   ];
}
echo json_encode($markers);
