<?
namespace Test\Reviewsbook;

use \Bitrix\Main\SystemException;
\Bitrix\Main\Loader::includeModule('iblock');

// Класс для добавления инфоблоков для модуля Отзывы о книгах
class ReviewsbookIblock
{

	// ID нашего типа инфоблока
	public static function getTypeIblockId(){
		return "reviews_book";
	}

	// Список code для наших инфоблоков, значение - id инфоблока
	private static function getListIblockParams(){
		return array(
			"reviews_book_b" => array( 			// Символьный код инфоблока Книги 
				"name" => "Книги", 				// Название инфоблока
				"iblock_id" => 0,
				"api_code" => "reviewsBookB",
				"properties" => array( 			// Список свойств, которые нужно добавить в инфоблок
					array(
						"code" => "AUTHOR",
						"name" => "Автор",
						"type" => "S"
					),
					array(
						"code" => "YEAR",
						"name" => "Год",
						"type" => "N"
					),
					array(
						"code" => "AVERAGE_RATING",
						"name" => "Средняя оценка",
						"type" => "N"
					)
				)
			),	
			"reviews_book_r" => array( 			
				"name" => "Отзывы о книгах", 	
				"iblock_id" => 0,
				"api_code" => "reviewsBookR",
				"properties" => array( 			// Список свойств, которые нужно добавить в инфоблок
					array(
						"code" => "RATING",
						"name" => "Оценка",
						"type" => "N"
					),
					array(
						"code" => "BOOK",
						"name" => "Книга",
						"type" => "E"
					),
				)
			),	 	
		);
	}




	// Список значений полей и свойств для генерации демо данных
	private static function getListDemoValues(){
		$arRes = array(); 	// Массив для заполнения демо данными
		$arBookName = array("Моя книга", "Книга обо всем", "Книга для всех"); // Список названий книг для генерации
		$arParams = array(
			"DATE_ACTIVE_FROM" => array("20.01.2021", "13.07.2022", "06.12.2006", "16.04.1995"),
			"PREVIEW_TEXT" => array("Книга как книга", "Интересный сюжет, только ничего не понятно", "Прочитайте ради интереса"),
			"AUTHOR" => array("Антон Чихов", "Николай Гуголь", "Александр Пашкин"),
			"YEAR" => array(2021, 2022, 2023),
			"RATING" => array(2, 3, 4, 5)
		);
		// Генерируем демо данные
		foreach ($arBookName as $key => $val) {
			// Заполняем информацию о книге
			$arRes[$key] = array(
				"NAME" => $val,
				"PREVIEW_PICTURE" => \CFile::MakeFileArray(__DIR__."/../install/images/book.jpg"),
				"ACTIVE" => "Y",
				"PROPERTIES" => array(
					"AUTHOR" => $arParams["AUTHOR"][rand(0, 2)],
					"YEAR" => $arParams["YEAR"][rand(0, 2)],
					"AVERAGE_RATING" => 0,
				),
				"REVIEWS" => array()
			);
			// Заполняем отзывы на книгу
			for ($i=1; $i<=5; $i++){
				$p_date = $arParams["DATE_ACTIVE_FROM"][rand(0, 3)];
				$arRes[$key]["REVIEWS"][$i] = array(
					"NAME" => "Отзыв на книгу «".$val."» [".$p_date."]",
					"DATE_ACTIVE_FROM" => $p_date,
					"PREVIEW_TEXT" =>  $arParams["PREVIEW_TEXT"][rand(0, 2)],
					"PROPERTIES" => array(
						"RATING" => $arParams["RATING"][rand(0, 3)]
					),
				);
			}
		}
		return $arRes;
	}


	// Список id сайтов
	private static function getSiteId(){
		$arRes = array();
		$rsSites = \CSite::GetList($by="sort", $order="desc", Array());
		while ($arSite = $rsSites->Fetch()) {
		  	$arRes[] = $arSite["ID"];
		}
		return $arRes;
	}

	// Добавление типа инфоблока "Книги"
	private static function addTypeIblock(){
		global $APPLICATION;
		$arRes = array("status" => true, "error" => ""); // Массив результатов работы функции, status - результат
		$obBlocktype = new \CIBlockType;
		$arFields = array(
	    	"ID" => self::getTypeIblockId(),
	    	"SECTIONS" => "Y",
	    	"IN_RSS" => "N",
	    	"SORT" => 500,
	    	"LANG" => array(
	    		"ru" => array(
	    			"NAME" => "Книги",
	    			"SECTION_NAME" => "Разделы",
	    			"ELEMENT_NAME" => "Элементы",
	    		),
	    		"en" => array(
	    			"NAME" => "Books",
	    			"SECTION_NAME" => "Sections",
	    			"ELEMENT_NAME" => "Elements",
	    		),
	    	)
	    );
		$res = $obBlocktype->Add($arFields);
		if (!$res) {
	   		$arRes = array("status" => false, "error" => $obBlocktype->LAST_ERROR);
	   		$APPLICATION->ThrowException($obBlocktype->LAST_ERROR);
		}
		return $arRes;
	}

	// Добавление новых свойств в инфоблок 
	// $iblock_id - id инфоблока, для которого добавляем свойство, $arIblockParams - параметры для создания инфоблока, $iblock_id_binding - id инфоблока для свойств "Привязка к инфоблоку")
	private static function addProperty($iblock_id, $arIblockParams, $iblock_id_binding=false){
	 	$arFields = Array(
	        "NAME"			=> $arIblockParams["name"],
	        "ACTIVE" 		=> "Y",
	        "SORT" 			=> "500",
	        "CODE" 			=> $arIblockParams["code"],
	        "PROPERTY_TYPE" => $arIblockParams["type"],
	        "IBLOCK_ID" 	=> $iblock_id,
        );
        // Если свойство "Привязка к элементу"
	    if ($arIblockParams["type"] == "E") {
	    	$arFields["LINK_IBLOCK_ID"] = $iblock_id_binding;
	    } 
      	$ibp = new \CIBlockProperty;
      	$PropID = $ibp->Add($arFields);
	}


	// Добавление инфоблока
	private static function addIblock($name, $code, $api_code){
		global $APPLICATION;
		$arSiteId = self::getSiteId();
		$arRes = array("status" => true, "error" => "", "iblock_id" => 0); // Массив результатов функции, status - результат
		$ib = new \CIBlock;
		$arFields = Array(
		  	"ACTIVE" 			=> "Y",
		  	"NAME" 				=> $name,
		  	"CODE" 				=> $code,
		  	"IBLOCK_TYPE_ID" 	=> self::getTypeIblockId(),
		  	"SITE_ID" 			=> $arSiteId,
		  	"SORT" 				=> "500",
		  	"GROUP_ID" 			=> Array("2" => "R"),
		  	"API_CODE" 			=> $api_code,
		);
		$iblock_id = $ib->Add($arFields);
		if ($iblock_id !== false) {
			$arRes["iblock_id"] = $iblock_id;
		} else {
			$arRes = array("status" => false, "error" => $ib->LAST_ERROR);
			$APPLICATION->ThrowException($ib->LAST_ERROR);
		}
		return $arRes;
	}



	// Добавления всех нужных нам инфоблоков для работы модуля
	public static function addIblockAll(){
		global $DB;
		$arRes = array("status" => true, "error" => "");
		$DB->StartTransaction();  // Оборачиваем все в транзакцию, чтобы в случае ошибки добавления инфоблоков не оставить часть инфоблоков
		$arIblockParams = self::getListIblockParams(); 	// Список параметров для создания инфоблоков
		// Проверяем, существует ли тип инфоблока с нашим названием
		$db_iblock_type = \CIBlockType::GetList(array(), array("ID" => self::getTypeIblockId()));
		// Если нет такого типа инфоблоков, то добавляем его
		if ($db_iblock_type->SelectedRowsCount() == 0) {
			$res = self::addTypeIblock();
			if ($result["status"] === false) {
				$arRes = array("status" => false, "error" => $res["error"]);
				$DB->Rollback();
				return $arRes;
			}
		}
		// Создаем инфоблоки
		$prev_iblock_id = false; 	// Переменная для хранения предыдущего id инфоблока
		$arIblocksId = array(); 	// Список id инфоблоков
		foreach ($arIblockParams as $iblock_code => &$val) {
			// Проверяем, существуют ли наши инфоблоки
			$res = \CIBlock::GetList(array(), array("TYPE" => self::getTypeIblockId(), "CODE" => $iblock_code));
			if ($res->SelectedRowsCount() > 0) {
				while ($ar_res = $res->Fetch()) {
				    $val["iblock_id"] = $ar_res["ID"];
				}
			} else {
				// Если инфоблока нет, то добавляем его
				$result = self::addIblock($val["name"], $iblock_code, $val["api_code"]);
				if ($result["status"]) {
					$val["iblock_id"] = $result["iblock_id"];
					// Добавляем свойства для инфоблока
					if (!empty($val["properties"])){
						foreach ($val["properties"] as $arProp) {
							self::addProperty($result["iblock_id"], $arProp, $prev_iblock_id);
						}
					}
					$prev_iblock_id = $result["iblock_id"];
				} else {
					$arRes = array("status" => false, "error" => $result["error"]);
					$DB->Rollback();
					return $arRes;
				}
			}
			$arIblocksId[$iblock_code] = $val["iblock_id"];
		}

		// Добавляем демо данные в инфоблок
		self::addDemo($arIblocksId);
		// Сохраняем id инфоблоков в файл для их использования в отложенных событиях
		self::saveIblockId($arIblocksId);
		$DB->Commit();
		return $arRes;
	}


	// Добавляем демо данные в наши созданные инфоблоки
	public static function addDemo($arIblocksId){
		$arDemoProps = self::getListDemoValues();  // Массив значений свойств, которые будут заполняться в инфоблоки
		foreach ($arDemoProps as &$arItem) {
			// Добавляем книгу
			$el = new \CIBlockElement;
			$arFields = Array(
			  	"IBLOCK_SECTION_ID" 	=> false,        
			  	"IBLOCK_ID"      		=> $arIblocksId["reviews_book_b"],
			  	"NAME"           		=> $arItem["NAME"],
			  	"ACTIVE"         		=> "Y",           
			  	"PREVIEW_PICTURE" 		=> $arItem["PREVIEW_PICTURE"],
			  	"PROPERTY_VALUES"		=> $arItem["PROPERTIES"],
		 	);
			$book_id = $el->Add($arFields);
			if ($book_id !== false) {
				// Заполняем отзывы на книгу
				$average_rating = 0;
				foreach ($arItem["REVIEWS"] as $val) {

					$average_rating += intval($val["PROPERTIES"]["RATING"]);

					$el = new \CIBlockElement;
					$arFields = Array(
					  	"IBLOCK_SECTION_ID" 	=> false,        
					  	"IBLOCK_ID"      		=> $arIblocksId["reviews_book_r"],
					  	"DATE_ACTIVE_FROM" 		=> $val["DATE_ACTIVE_FROM"],
					  	"NAME"           		=> $val["NAME"],
					  	"PREVIEW_TEXT" 			=> $val["PREVIEW_TEXT"],
					  	"ACTIVE"         		=> "Y",           
					  	"PROPERTY_VALUES"		=> array(
					  		"RATING" => $val["PROPERTIES"]["RATING"],
					  		"BOOK" 	=> $book_id 
					  	),
				 	);

					$el->Add($arFields);
				}

				// Высчитываем средную оценку книги
				$average_rating = round($average_rating / count($arItem["REVIEWS"]), 2);	
				\CIBlockElement::SetPropertyValuesEx($book_id, $arIblocksId["reviews_book_b"], array("AVERAGE_RATING" => $average_rating));	// Обновляем среднюю оценку у книги
			}
		}
	}


	// Удаление всех инфоблоков модуля
	public static function removeIblockAll(){
		$arIblockParams = self::getListIblockParams();
		// Получаем id инфоблоков
		$arIblocksId = array();
		foreach ($arIblockParams as $iblock_code => $val) {
			$res = \CIBlock::GetList(array(), array("CODE" => $iblock_code));
			while ($ar_res = $res->Fetch()) {
			    $arIblocksId[] = $ar_res["ID"];
			}
		}
		// Удаляем инфоблоки
		foreach ($arIblocksId as $iblock_id) {
			\CIBlock::Delete($iblock_id);
		}
		// Проверяем, есть ли еще инфоблоки в группе инфоблоков
		$iblock_type = self::getTypeIblockId();
		$res = \CIBlock::GetList(array(), array("TYPE" => $iblock_type));
		if ($res->SelectedRowsCount() == 0) {
			\CIBlockType::Delete($iblock_type);
		}
	}

	// Функция для сохранения id инфоблоков в файл
	private static function saveIblockId($arIblocksId){
		// Формируем массив с id инфоблоков
		$res = '<?$arReviewsbookIblockId = array(';
		foreach ($arIblocksId as $key => $val) {
			$res .= '"'.$key.'" => '.$val.',';
		}
		$res .= ');?>';
		$file = __DIR__."/ReviewsbookIblockId.php";
		$current = file_get_contents($res);
		file_put_contents($file, $res);
	}



}

?>