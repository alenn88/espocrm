<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014  Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 ************************************************************************/ 

namespace Espo\Core\Utils\Database\Orm\Relations;

class BelongsTo extends \Espo\Core\Utils\Database\Orm\Base
{

	public function load($params, $foreignParams)
	{
		return array (
			$params['entityName'] => array (
				'fields' => array(
					$params['link']['name'].'Name' => array(
						'type' => 'foreign',
						'relation' => $params['link']['name'],
						'foreign' => $this->getForeignField('name', $foreignParams['entityName']),
					),
					$params['link']['name'].'Id' => array(
						'type' => 'foreignId',
						'index' => true,
					),
				),
				'relations' => array(
                	$params['link']['name'] => array(
						'type' => 'belongsTo',
						'entity' => $params['targetEntity'],
						'key' => $params['link']['name'].'Id',
						'foreignKey' => 'id', //????
					),
				),
			),
		);
	}


}