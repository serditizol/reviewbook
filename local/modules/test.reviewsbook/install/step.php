<?
use \Bitrix\Main\Localization\Loc;
global $APPLICATION;

if (!check_bitrix_sessid()) {
	return;
}

if ($ex = $APPLICATION->GetException()) {
	echo CAdminMessage::ShowMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => "Произошла ошибка при установке модуля test.reviewsbook",
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
} else {
	echo CAdminMessage::ShowNote("Модуль test.reviewsbook успешно установлен!");
}
?>
<form action="<?echo $APPLICATION->GetCurPage();?>">
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID;?>" />
	<input type="submit" name="" value="Вернуться" />
</form>