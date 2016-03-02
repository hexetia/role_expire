<?php
declare(strict_types = 1);

namespace Drupal\role_expire;

use Drupal\Core\Database\Connection;
use Drupal\role_expire\Entity\RoleExpire;
use Drupal\role_expire\Exception\RoleExpireNotFoundException;
use Drupal\user\UserInterface;

class RoleManagerService
{
    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function addTime(UserInterface $user, string $roleId, int $time)
    {
        $roleExpire = null;

        try {
            $roleExpire = $this->getRoleExpire($user, $roleId);
        } catch (RoleExpireNotFoundException $e) {
            $roleExpire = RoleExpire::create();
            $roleExpire->setUserId((int) $user->id());
            $roleExpire->setRoleId($roleId);
            $roleExpire->setExpire(0);
        }

        if (!$user->hasRole($roleId)) {
            $user->addRole($roleId);
        }

        $roleExpire->setExpire($roleExpire->getExpire() + $time);
        $roleExpire->save();
        $user->save();
    }

    /**
     * Remove o papel do usuário e a entidade RoleExpire relacionada
     * 
     * @param \Drupal\user\UserInterface $user
     * @param string $roleId
     */
    public function expireRole(UserInterface $user, string $roleId)
    {
        $user->removeRole($roleId);
        $user->save();

        try {
            $roleExpire = $this->getRoleExpire($user, $roleId);
            $roleExpire->delete();
        } catch (RoleExpireNotFoundException $e) {
            // se não existir não precisamos remover
        }
    }

    public function getRoleExpire(UserInterface $user, string $roleId): RoleExpire
    {
        $roleId = $this->connection->query(
            'SELECT
              id
            FROM
              role_expire
            WHERE
              user_id = :userId
              AND role_id = :roleId',
            [
                ':userId' => $user->id(),
                ':roleId' => $roleId
            ]
        )->fetch();

        if ($roleId) {
           return RoleExpire::load($roleId->id);
        } else {
            throw new RoleExpireNotFoundException('Ainda não existe uma entidade de expiração');
        }
    }

    /**
     * @return \Drupal\role_expire\Entity\RoleExpire[]
     */
    public function getExpiredRoles(): array
    {
        $expiredItems = $this->connection->query(
            'SELECT
              id
            FROM
              role_expire
            WHERE
              expire < :currentTime',
            [':currentTime' => time()]
        )->fetchAll();

        $items = [];
        foreach ($expiredItems as $expiredItem) {
            $items[] = RoleExpire::load($expiredItem->id);
        }

        return $items;
    }
}
