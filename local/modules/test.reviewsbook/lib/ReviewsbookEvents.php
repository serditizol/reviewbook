<?
namespace Test\Reviewsbook;




// Класс для обработки событий
class ReviewsbookEvents
{

	// Функция для обновление средней оценки у книги при добавлении, изменении или удалении нового отзыва
	public static function updateAverageRating(&$arFields) {
		// Если это удаление элемента
		if (is_array($arFields) === false) {
			$p_elem = $arFields;
			// Получаем информацию о нашем элементе
			$arFields = array();
			$res = \CIBlockElement::GetByID($p_elem);
			if ($ar_res = $res->GetNext()){
			  	$arFields = $ar_res;
			}
		}
		// Массив id инфоблоков для работы модуля хранится в этом файле
		// reviews_book_b - id инфоблока "Книги", reviews_book_r - id инфоблока "Отзывы на книги" 
		include(__DIR__."/ReviewsbookIblockId.php"); 
		// Если действие происходит с нужным инфоблоком
		if ($arFields["IBLOCK_ID"] == $arReviewsbookIblockId["reviews_book_r"]) {
			// Получаем id книги, к которой привязан отзыв
			$book_id = 0;
			$db_props = \CIBlockElement::GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], array("sort" => "asc"), array("CODE" => "BOOK"));
			if ($ar_props = $db_props->Fetch()) {
    			$book_id = $ar_props["VALUE"];
			}
			// Если свойство было не пустое
			if (!empty($book_id)) {
				// Вычисляем среднюю оценку
				$average_rating = 0;
				$arSelect = Array("ID", "IBLOCK_ID", "NAME");
				$arFilter = Array("IBLOCK_ID" => $arReviewsbookIblockId["reviews_book_r"], "ACTIVE"=>"Y", "PROPERTY_BOOK" => $book_id);
				$res = \CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
				$p_count = $res->SelectedRowsCount();
				while($ob = $res->GetNextElement()){
					$arFields = $ob->GetFields();
				 	$arProps = $ob->GetProperties();

				 	// Если обрабатываем удаление элемента
				 	if ($action == "delete") {
			 			// Если это не наш удаляемый элемент
					 	if ($arFields["ID"] != $elem_id) {
					 		$average_rating += intval($arProps["RATING"]["VALUE"]);
					 	}
				 	}

				 	$average_rating += intval($arProps["RATING"]["VALUE"]);
				}
				if ($p_count > 0) {
					$average_rating = round($average_rating / $p_count, 2);
				} else {
					$average_rating = 0;
				}
				
				// Обновляем среднюю оценку у книги
				\CIBlockElement::SetPropertyValuesEx($book_id, $arReviewsbookIblockId["reviews_book_b"], array("AVERAGE_RATING" => $average_rating));
			}

		}
		return true;
	}





}




?>