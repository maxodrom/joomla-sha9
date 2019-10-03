<?php
/**
 * Custom script.
 *
 * @author Max Alexandrov <mxdr@ya.ru>
 * @link https://localhoster.ru
 * @copyright 2019 Max Alexandrov
 * @license proprietary
 */

// эти 3 строки - для отладки; их надо закмоеентировать на продакшен сайте
/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

// кол-во хитов (найденных строк, отвечающих поиск. запросу)
$hits = 0;
$hitRows = [];
$data = [
	'success' => false,
	'content' => '',
	'hits' => $hits,
];
$rows = [];
// файл с данными, который буем парсить (лежит в корне сайта)
$sourceFile = dirname(__FILE__) . '/1.csv';
// минимальная длина поискового запроса в символах
$minQueryStrLength = 3;
/**
 * Какие столбцы следует отображать в таблице.
 * Цифра = номер столбца. Если нужно отобразить только 1-й, 5-й и 7-й столбцы,
 * то пишем [1, 5, 7].
 * Отображаемое название столбца задается как значение: 1 => 'Первый столбец',
 */
$columnsToShow = [
	1 => 'Первая',
	2 => 'Вторая',
	3 => 'Третья',
	4 => 'Четвертая',
	5 => 'Пятая',
	6 => 'Шестая',
	7 => 'Седьмая',
	8 => 'Герои',
];
/**
 * В каких столбцах производим поиск. Аналогично предудыщей настройке.
 */
$columnsToSearch = [8];

if (($handle = @fopen($sourceFile, "r")) !== false) {
	while (($data = fgetcsv($handle, 0, ";")) !== false) {
		$rows[] = $data;
	}
	fclose($handle);

	// в поисковом запросе допустимы только символы a-z, 0-9, а-я и пробелы
	$q = preg_replace('/[^а-яё\da-z ]+/ui', '', $_POST['q']);
	// вырезаем двойные пробельные символы
	$q = preg_replace('/[\s]{2,}/', ' ', $q);

	if (mb_strlen($q) >= $minQueryStrLength) {
		foreach ($rows as $row) {
			foreach ($row as $k => $v) {
				if (!in_array($k + 1, $columnsToSearch)) {
					continue;
				}
				if (false !== mb_stristr($v, $q)) {
					$hitRows[] = $row;
					++$hits;
				}
			}
		}

		// если ничего не найдено...
		if ($hits == 0) {
			$data = [
				'success' => true,
				'content' => '<p style="color: red;">Ваш поисковый запрос не дал результатов. Попробуйте сформулировать его иначе.</p><hr/>',
				'hits' => $hits,
			];
		} else {
			// а если мы нашли, то формируем html-строку (таблицу)
			$str = '';
			foreach ($hitRows as $hitRow) {
				foreach ($hitRow as $k => $v) {
					if (!in_array($k + 1, array_keys($columnsToShow))) {
						unset($hitRow[$k]);
					}
				}
				$str .= '<tr><td>' . implode('</td><td>', $hitRow) . '</td></tr>';
			}
			// Шапка таблицы из названий столбцов
			$header = '<thead><tr><th>' . implode('</th><th>', $columnsToShow) . '</th></tr></thead>';
			$str = $header . '<tbody>' . $str . '</tbody>';

			$data = [
				'success' => true,
				'content' => <<<HTML
<style type="text/css">
#search-results-table {
	width: 100%;
	border: 1px solid #dedede;
}
#search-results-table tr td {
	border: 1px solid #dedede;
	padding: 5px;
}
</style>
<h3>Результаты поиска ({$hits})</h3>
<table id="search-results-table">$str</table>
<hr/>
HTML
				,
				'hits' => $hits,
			];
		}
	} else {
		// если запрос меньше, чем указано в найстроках кол-ва минимума символов, возвращаем предупреждение
		$data = [
			'success' => false,
			'content' => '<p style="color: red;">Поисковый запрос должен содержать минимум ' . $minQueryStrLength . ' символа(ов).</p><hr/>',
			'hits' => $hits,
		];
	}
} else {
	// если файл данных отсутствует, возвращаем ошибку
	$data = [
		'success' => true,
		'content' => 'Отсутствует файл данных для поиска!',
		'hits' => $hits,
	];
}

header('Content-Type: application/json');
echo json_encode($data);
