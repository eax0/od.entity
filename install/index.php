<?
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class od_entity extends CModule
{
    public function __construct()
    {
        $this->MODULE_VERSION      = '1.0.0';
        $this->MODULE_VERSION_DATE = date('Y-m-d');
        $this->MODULE_NAME         = '!Модуль для работы с сущностями';
        $this->PARTNER_NAME        = "OOO Odiva";
        $this->PARTNER_URI         = "https://www.odiva.ru";

        $this->MODULE_ID   = 'od.entity';
    }

    public function doInstall()
    {
        ModuleManager::RegisterModule($this->MODULE_ID);
        Loader::includeModule($this->MODULE_ID);

        $dbErrorText = "Ошибка во время создания БД.";
        try {
            $installRes = $this->installDB();
        } catch (Exception $ex) {
            $this->showError($dbErrorText . "\n" . $ex->getMessage());
            $this->doUninstall();

            return false;
        }

        if (!$installRes) {
            $this->showError($dbErrorText);
            $this->doUninstall();

            return false;
        }

        $this->afterInstall();

        return true;
    }

    protected function afterInstall()
    {
    }

    protected function afterUnInstall()
    {
    }

    public function installDB()
    {
        return true;
    }

    public function unInstallDB()
    {
        return true;
    }

    protected function showError($msg)
    {
        $message = new CAdminMessage($msg);
        $message->Show();
    }
}