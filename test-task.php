<?php
namespace Bitrix\Table;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

// тут сгенерировал ORM класс через админку, обычно его выносят в отдельный файл, но тут он небольшой и проще будет передать задание на проверку

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class TableTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'my_table';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'id' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('TABLE_ENTITY_ID_FIELD'),
			),
			'name' => array(
				'data_type' => 'text',
				'required' => true,
				'title' => Loc::getMessage('TABLE_ENTITY_NAME_FIELD'),
			),
			'income' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('TABLE_ENTITY_INCOME_FIELD'),
			),
			'cost' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('TABLE_ENTITY_COST_FIELD'),
			),
			'total_residents' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('TABLE_ENTITY_TOTAL_RESIDENTS_FIELD'),
			),
		);
	}
}

// тут начинается мое решение задачи

$arTable = [];

// вспомогательные массивы для расчета рейтингов
$arResidentsRank = [];
$arIncomeRank = [];
$arCostRank = [];

// получаем данные из БД
$res = TableTable::getList([
	'select' => ['id', 'name', 'income', 'cost', 'total_residents'],
	'order' => ['total_residents' => 'desc']
]);

while ($row = $res->fetch())
{
	$arTable[] = $row;

	$arResidentsRank[$row['total_residents']] = $row['id'];
	$arIncomeRank[ceil($row['income'] / $row['total_residents'])] = $row['id'];	// в ключе сохраняем средний доход на человека
	$arCostRank[ceil($row['cost'] / $row['total_residents'])] = $row['id']; // в ключе сохраняем средний расход на человека
}

// сортируем согласно условиям задачи
krsort($arResidentsRank);
krsort($arIncomeRank);
krsort($arCostRank);

// сбрасываем ключи, чтобы потом получить рейтиги
$arResidentsRank = array_values($arResidentsRank);
$arIncomeRank = array_values($arIncomeRank);
$arCostRank = array_values($arCostRank);
?>
<table>
	<tr>
		<th>Название</th>
		<th>Доходы общие</th>
		<th>Расходы общие</th>
		<th>Количество жителей</th>
		<th>Место в рейтинге по количеству жителей</th>
		<th>Место в рейтинге по средним доходам населения</th>
		<th>Место по средним расходам населения</th>
	</tr>
	<?foreach($arTable as $row):?>
	<tr>
		<td><?=$row['name']?></td>
		<td><?=$row['income']?></td>
		<td><?=$row['cost']?></td>
		<td><?=$row['total_residents']?></td>
		<td><?=(array_search($row['id'], $arResidentsRank) + 1)?></td>
		<td><?=(array_search($row['id'], $arIncomeRank) + 1)?></td>
		<td><?=(array_search($row['id'], $arCostRank) + 1)?></td>
	</tr>	
	<?endforeach;?>
</table>

<style>
	table {border-collapse: collapse;}
	table td, table th {border: solid 1px #ccc; padding: 5px; text-align: center;}
	table th {font-weight: bold;}
</style>