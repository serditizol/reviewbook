<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock\IblockTable;

Loc::loadMessages(__FILE__);


 // Подключаем наши классы
$pos = mb_strripos(__DIR__, "/install");
$folder_class_path = mb_substr(__DIR__, strlen($_SERVER["DOCUMENT_ROOT"]), $pos-strlen($_SERVER["DOCUMENT_ROOT"]));
Bitrix\Main\Loader::registerAutoLoadClasses(null, array(
    "\Test\Reviewsbook\ReviewsbookEvents" => $folder_class_path."/lib/ReviewsbookEvents.php",
    "\Test\Reviewsbook\ReviewsbookIblock" => $folder_class_path."/lib/ReviewsbookIblock.php",
));



Class test_reviewsbook extends CModule
{

    var $MODULE_ID = "test.reviewsbook";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;



    function __construct() {

        include(__DIR__."/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("TEST_RB_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TEST_RB_MODULE_DESCRIPTION");
    }

    
    // Добавляем список инфоблоков, которые нужны для модуля
    function installIblock(){
        $arRes = \Test\Reviewsbook\ReviewsbookIblock::addIblockAll(); 
        return $arRes;
    }

     // Удаляем список инфоблоков, которые нужны для модуля
    function UnInstallIblock(){
        \Test\Reviewsbook\ReviewsbookIblock::removeIblockAll();
        return true;
    }

    // Устанавливаем события для модуля
    function installEvents(){
        // Добавляем обработчики
        $eventManager = \Bitrix\Main\EventManager::getInstance(); 
        $eventManager->registerEventHandler("iblock", "OnAfterIBlockElementAdd", $this->MODULE_ID, "\Test\Reviewsbook\ReviewsbookEvents", "updateAverageRating");
        $eventManager->registerEventHandler("iblock", "OnAfterIBlockElementUpdate", $this->MODULE_ID, "\Test\Reviewsbook\ReviewsbookEvents", "updateAverageRating");
        $eventManager->registerEventHandler("iblock", "OnBeforeIBlockElementDelete", $this->MODULE_ID, "\Test\Reviewsbook\ReviewsbookEvents", "updateAverageRating");
    }
   
    function unInstallEvents(){
        // Удаляем обработчики 
        $eventManager = \Bitrix\Main\EventManager::getInstance(); 
        $eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockElementAdd", $this->MODULE_ID, "\Test\Reviewsbook\ReviewsbookEvents", "updateAverageRating");
        $eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockElementUpdate", $this->MODULE_ID, "\Test\Reviewsbook\ReviewsbookEvents", "updateAverageRating");
        $eventManager->unRegisterEventHandler("iblock", "OnBeforeIBlockElementDelete", $this->MODULE_ID, "\Test\Reviewsbook\ReviewsbookEvents", "updateAverageRating");
    }




    function DoInstall() {
        global $APPLICATION;
        $arRes = $this->installIblock();
        if ($arRes["status"]) {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            $this->installEvents();
        }
        $APPLICATION->IncludeAdminFile("Установка модуля ".$this->MODULE_NAME, $_SERVER["DOCUMENT_ROOT"]."/local/modules/test.reviewsbook/install/step.php");
        
    }


    function DoUninstall() {
        global $APPLICATION;
        $this->unInstallEvents();
        $this->UnInstallIblock();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        
        $APPLICATION->IncludeAdminFile("Деинсталляция модуля ".$this->MODULE_NAME, $_SERVER["DOCUMENT_ROOT"]."/local/modules/test.reviewsbook/install/unstep.php");
    }
}
?>