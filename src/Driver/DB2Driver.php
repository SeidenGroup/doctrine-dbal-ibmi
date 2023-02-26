<?php

namespace DoctrineDbalIbmi\Driver;

class DB2Driver extends AbstractDB2Driver
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        $params['user']     = $username;
        $params['password'] = $password;
        $params['dbname']   = DataSourceName::fromConnectionParameters($params)->toString();

        return new DB2IBMiConnection($params, $username, $password, $driverOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ibm_db2';
    }
}
