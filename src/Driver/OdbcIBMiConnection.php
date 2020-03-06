<?php

namespace DoctrineDbalIbmi\Driver;

use Doctrine\DBAL\Driver\IBMDB2\DB2Connection;
use Doctrine\DBAL\Driver\PDOConnection;

/**
 * IBMi Db2 Connection.
 * More documentation about iSeries schema at https://www-01.ibm.com/support/knowledgecenter/ssw_ibm_i_72/db2/rbafzcatsqlcolumns.htm
 *
 * @author Cassiano Vailati <c.vailati@esconsulting.it>
 * @author James Titcumb <james@asgrim.com>
 */
class OdbcIBMiConnection extends PDOConnection
{
    protected $driverOptions = array();

    /**
     * @param array  $params
     * @param string $username
     * @param string $password
     * @param array  $driverOptions
     *
     * @throws \Doctrine\DBAL\Driver\IBMDB2\DB2Exception
     */
    public function __construct($params, $username, $password, $driverOptions = array())
    {
        $this->driverOptions = $driverOptions;
        $this->driverOptions[\PDO::ATTR_PERSISTENT] = false;
        if (isset($params['persistent'])) {
            $this->driverOptions[\PDO::ATTR_PERSISTENT] = $params['persistent'];
        }
        parent::__construct($params['dsn'], $username, $password, $this->driverOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($name = null)
    {
        $sql = 'SELECT IDENTITY_VAL_LOCAL() AS VAL FROM QSYS2'.$this->getSchemaSeparatorSymbol().'QSQPTABL';
        $stmt = $this->prepare($sql);
        $stmt->execute();

        $res = $stmt->fetch();

        return $res['VAL'];
    }

    /**
     * Returns the appropriate schema separation symbol for i5 systems.
     * Other systems can hardcode '.' but i5 may need '.' or  '/' depending on the naming mode.
     *
     * @return string
     */
    public function getSchemaSeparatorSymbol()
    {
        // if "i5 naming" is on, use '/' to separate schema and table. Otherwise use '.'
        if (array_key_exists('i5_naming', $this->driverOptions) && $this->driverOptions['i5_naming']) {

            // "i5 naming" mode requires a slash
            return '/';

        } else {
            // SQL naming requires a dot
            return '.';
        }
    }

    /**
     *
     * Retrieves ibm_db2 native resource handle.
     *
     * Could be used if part of your application is not using DBAL.
     *
     * @return resource
     */
    public function getWrappedResourceHandle()
    {
        $connProperty = new \ReflectionProperty(DB2Connection::class, '_conn');
        $connProperty->setAccessible(true);
        return $connProperty->getValue($this);
    }

    /**
     * @return bool
     */
    public function requiresQueryForServerVersion()
    {
        return true;
    }
}
