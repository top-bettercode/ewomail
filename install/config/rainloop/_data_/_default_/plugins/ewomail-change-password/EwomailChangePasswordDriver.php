<?php

class EwomailChangePasswordDriver implements \RainLoop\Providers\ChangePassword\ChangePasswordInterface
{
    /**
     * @var string
     */
    private $mHost = '127.0.0.1';

    /**
     * @var string
     */
    private $mUser = '';

    /**
     * @var string
     */
    private $mPass = '';

    /**
     * @var string
     */
    private $mDatabase = '';

    /**
     * @var string
     */
    private $mTable = '';

    /**
     * @var string
     */
    private $idColumn = '';
    /**
     * @var string
     */
    private $mColumn = '';

    /**
     * @var \MailSo\Log\Logger
     */
    private $oLogger = null;

    /**
     * @var array
     */
    private $aDomains = array();

    /**
     * @param string $mHost
     *
     * @return \EwomailChangePasswordDriver
     */
    public function SetmHost($mHost)
    {
        $this->mHost = $mHost;
        return $this;
    }

    /**
     * @param string $mUser
     *
     * @return \EwomailChangePasswordDriver
     */
    public function SetmUser($mUser)
    {
        $this->mUser = $mUser;
        return $this;
    }

    /**
     * @param string $mPass
     *
     * @return \EwomailChangePasswordDriver
     */
    public function SetmPass($mPass)
    {
        $this->mPass = $mPass;
        return $this;
    }

    /**
     * @param string $mDatabase
     *
     * @return \EwomailChangePasswordDriver
     */
    public function SetmDatabase($mDatabase)
    {
        $this->mDatabase = $mDatabase;
        return $this;
    }

    /**
     * @param string $mTable
     *
     * @return \EwomailChangePasswordDriver
     */
    public function SetmTable($mTable)
    {
        $this->mTable = $mTable;
        return $this;
    }

    /**
     * @param string $idColumn
     *
     * @return \EwomailChangePasswordDriver
     */
    public function SetidColumn($idColumn)
    {
        $this->idColumn = $idColumn;
        return $this;
    }

    /**
     * @param string $mColumn
     *
     * @return \EwomailChangePasswordDriver
     */
    public function SetmColumn($mColumn)
    {
        $this->mColumn = $mColumn;
        return $this;
    }

    /**
     * @param \MailSo\Log\Logger $oLogger
     *
     * @return \EwomailChangePasswordDriver
     */
    public function SetLogger($oLogger)
    {
        if ($oLogger instanceof \MailSo\Log\Logger) {
            $this->oLogger = $oLogger;
        }

        return $this;
    }

    /**
     * @param array $aDomains
     *
     * @return bool
     */
    public function SetAllowedDomains($aDomains)
    {
        if (\is_array($aDomains) && 0 < \count($aDomains)) {
            $this->aDomains = $aDomains;
        }

        return $this;
    }

    /**
     * @param \RainLoop\Account $oAccount
     *
     * @return bool
     */
    public function PasswordChangePossibility($oAccount)
    {
        return $oAccount && $oAccount->Domain() &&
            \in_array(\strtolower($oAccount->Domain()->Name()), $this->aDomains);
    }

    /**
     * @param \RainLoop\Account $oAccount
     * @param string $sPrevPassword
     * @param string $sNewPassword
     *
     * @return bool
     */
    public function ChangePassword(\RainLoop\Account $oAccount, $sPrevPassword, $sNewPassword)
    {
        if ($this->oLogger) {
            $this->oLogger->Write('Try to change password for ' . $oAccount->Email());
        }

        $dsn = 'mysql:host=' . $this->mHost . ';dbname=' . $this->mDatabase . ';charset=utf8';
        $options = array(
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        $conn = new PDO($dsn, $this->mUser, $this->mPass, $options);
        $update = $conn->prepare("UPDATE $this->mTable SET $this->mColumn = :crypt WHERE $this->idColumn = :email");
        $update->execute(array(
            ':email' => $oAccount->Email(),
            ':crypt' => md5($sNewPassword)
        ));
        if ($this->oLogger) {
            $this->oLogger->Write('Success! Password changed.');
        }

        return true;
    }
}
