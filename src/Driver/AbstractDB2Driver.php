<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineDbalIbmi\Driver;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\DB2Platform;
use DoctrineDbalIbmi\Platform\DB2IBMiPlatform;
use DoctrineDbalIbmi\Schema\DB2IBMiSchemaManager;
use DoctrineDbalIbmi\Schema\DB2LUWSchemaManager;

/**
 * Abstract base implementation of the {@link Doctrine\DBAL\Driver} interface for IBM DB2 based drivers.
 *
 * @author Steve Müller <st.mueller@dzh-online.de>
 * @author Cassiano Vailati <c.vailati@esconsulting.it>
 * @author James Titcumb <james@asgrim.com>
 * @link   www.doctrine-project.org
 * @since  2.5
 */
abstract class AbstractDB2Driver implements Driver
{
    const SYSTEM_IBMI = 'AIX';

    /**
     * {@inheritdoc}
     */
    public function getDatabase(\Doctrine\DBAL\Connection $conn)
    {
        $params = $conn->getParams();

        return $params['dbname'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        if (PHP_OS === static::SYSTEM_IBMI) {
            return new DB2IBMiPlatform();
        } else {
            return new DB2Platform();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        if (PHP_OS === static::SYSTEM_IBMI) {
            return new DB2IBMiSchemaManager($conn);
        } else {
            return new DB2LUWSchemaManager($conn);
        }
    }
}
