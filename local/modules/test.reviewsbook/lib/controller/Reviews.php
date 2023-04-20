<?
namespace Test\Reviewsbook\Controller;

use \Bitrix\Main\Error;

class Reviews extends \Bitrix\Main\Engine\Controller
{

    public function getListAction(int $page, int $limit):? array {
        $arRes = \Test\Reviewsbook\Reviewsbook::getList($page, $limit);
        return $arRes;
    }

}
?>