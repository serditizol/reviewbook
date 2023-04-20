<?
namespace Test\Reviewsbook;

use \Bitrix\Main\Entity;
\Bitrix\Main\Loader::includeModule('iblock');

// Класс для вывода отзывов на книги
class Reviewsbook
{
	
	public static function getList(int $page, int $limit=10) {
		include(__DIR__."/ReviewsbookIblockId.php");  // Подключаем файл с массивом id инфоблоков нашего модуля
		$page = ($page > 0 ? $page-1 : 0);
		$offset = $page * $limit;

		$arRes = array();
		$iblock_r = \Bitrix\Iblock\Iblock::wakeUp($arReviewsbookIblockId["reviews_book_r"])->getEntityDataClass();

		$arElem = $iblock_r::getList([
			'select' => ['NAME', 'ACTIVE_FROM', 'PREVIEW_TEXT', 'BOOK.ELEMENT.NAME', 'BOOK.ELEMENT.AUTHOR', 'BOOK.ELEMENT.YEAR', 'RATING'],
			'order' => ['ID' => 'ASC'],
		    'filter' => ['ACTIVE' => 'Y'],
		    'limit' => $limit,
		    'offset' => $offset
		])->fetchCollection();
		foreach ($arElem as $element){
			$arRes[] = array(
				"date" => $element->getActiveFrom()->format("d.m.Y"),
				'text' => $element->getPreviewText(),
				'rating' => $element->getRating()->getValue(),
				'book' => [
					'title' => $element->getBook()->getElement()->getName(),
					'author' => $element->getBook()->getElement()->getAuthor()->getValue(),
					'year' => $element->getBook()->getElement()->getYear()->getValue()
				]
			);
		}
		return $arRes;
	}

}




?>