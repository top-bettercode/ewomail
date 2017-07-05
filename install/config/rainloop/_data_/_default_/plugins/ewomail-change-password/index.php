<?php

class ChangePasswordMysqlPlugin extends \RainLoop\Plugins\AbstractPlugin
{
	public function Init()
	{
		$this->addHook('main.fabrica', 'MainFabrica');
	}

	/**
	 * @param string $sName
	 * @param mixed $oProvider
	 */
	public function MainFabrica($sName, &$oProvider)
	{
		switch ($sName)
		{
			case 'change-password':

				include_once __DIR__.'/EwomailChangePasswordDriver.php';

				$oProvider = new EwomailChangePasswordDriver();

				$sDomains = \strtolower(\trim(\preg_replace('/[\s;,]+/', ' ',
					$this->Config()->Get('plugin', 'domains', ''))));

				if (0 < \strlen($sDomains))
				{
					$aDomains = \explode(' ', $sDomains);
					$oProvider->SetAllowedDomains($aDomains);
				}

				$oProvider
						->SetLogger($this->Manager()->Actions()->Logger())
						->SetmHost($this->Config()->Get('plugin', 'mHost', '127.0.0.1'))
						->SetmUser($this->Config()->Get('plugin', 'mUser', 'ewomail'))
						->SetmPass($this->Config()->Get('plugin', 'mPass', '123456'))
						->SetmDatabase($this->Config()->Get('plugin', 'mDatabase', 'ewomail'))
						->SetmTable($this->Config()->Get('plugin', 'mTable', 'i_users'))
						->SetmColumn($this->Config()->Get('plugin', 'mColumn', 'email'))
						->SetidColumn($this->Config()->Get('plugin', 'idColumn', 'password'))
				;
				
				break;
		}
	}

	/**
	 * @return array
	 */
	public function configMapping()
	{
		return array(
			\RainLoop\Plugins\Property::NewInstance('domains')->SetLabel('Allowed Domains')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::STRING_TEXT)
				->SetDescription('Allowed domains, space as delimiter')
				->SetDefaultValue('$mydomain')
//        ,
//			\RainLoop\Plugins\Property::NewInstance('mHost')->SetLabel('MySQL Host')
//				->SetDefaultValue('127.0.0.1'),
//			\RainLoop\Plugins\Property::NewInstance('mUser')->SetLabel('MySQL User')
//                ->SetDefaultValue('ewomail'),
//			\RainLoop\Plugins\Property::NewInstance('mPass')->SetLabel('MySQL Password')
//				->SetType(\RainLoop\Enumerations\PluginPropertyType::PASSWORD)
//                ->SetDefaultValue('123456'),
//			\RainLoop\Plugins\Property::NewInstance('mDatabase')->SetLabel('MySQL Database')
//                ->SetDefaultValue('ewomail'),
//			\RainLoop\Plugins\Property::NewInstance('mTable')->SetLabel('MySQL Table')
//                ->SetDefaultValue('i_users'),
//			\RainLoop\Plugins\Property::NewInstance('idColumn')->SetLabel('MySQL ID Column')
//                ->SetDefaultValue('email'),
//			\RainLoop\Plugins\Property::NewInstance('mColumn')->SetLabel('MySQL PWD Column')
//                ->SetDefaultValue('password')
		);
	}
}
