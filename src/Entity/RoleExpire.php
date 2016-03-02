<?php
declare(strict_types = 1);

namespace Drupal\role_expire\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the RoleExpire entity.
 *
 * @ContentEntityType(
 *   id = "role_expire",
 *   label = @Translation("Role Expire"),
 *   bundle_label = @Translation("Role expire type"),
 *   handlers = {
 *     "storage" = "Drupal\role_expire\RoleExpireStorage",
 *     "storage_schema" = "Drupal\role_expire\RoleExpireStorageSchema",
 *   },
 *   base_table = "role_expire",
 *   translatable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 * )
 */
class RoleExpire extends ContentEntityBase
{
    public function getUserId(): int
    {
        return (int) $this->get('user_id')->getValue()[0]['value'];
    }

    public function setUserId(int $userId): self
    {
        $this->set('user_id', $userId);
        return $this;
    }

    public function getRoleId(): string
    {
        return $this->get('role_id')->getValue()[0]['value'];
    }

    public function setRoleId(string $roleId): self
    {
        $this->set('role_id', $roleId);
        return $this;
    }

    public function getExpire(): int
    {
        return (int) $this->get('expire')->getValue()[0]['value'];
    }

    public function setExpire(int $timestamp): self
    {
        $this->set('expire', $timestamp);
        return $this;
    }

    public function getCreatedTime(): int
    {
        return (int) $this->get('created')->getValue()[0]['value'];
    }

    public function getChangedTime(): int
    {
        return (int) $this->get('changed')->getValue()[0]['value'];
    }

    public function setChangedTime(int $timestamp): self
    {
        $this->set('changed', $timestamp);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function baseFieldDefinitions(\Drupal\Core\Entity\EntityTypeInterface $entity_type)
    {
        $fields['id'] = BaseFieldDefinition::create('integer')
          ->setLabel(t('ID'))
          ->setDescription(t('The role expire id.'))
          ->setReadOnly(TRUE)
          ->setSetting('unsigned', TRUE);

        // o UUID também é o código de referência da transação
        $fields['uuid'] = BaseFieldDefinition::create('uuid')
          ->setLabel(t('UUID'))
          ->setDescription(t('The row UUID.'))
          ->setReadOnly(TRUE);

        $fields['user_id'] = BaseFieldDefinition::create('integer')
          ->setLabel(t('User ID'))
          ->setDescription(t('The user ID.'))
          ->setReadOnly(TRUE)
          ->setSetting('unsigned', TRUE);

        // É usado como histórico de transações
        $fields['role_id'] = BaseFieldDefinition::create('string')
          ->setLabel(t('Role ID'))
          ->setDescription(t('The role ID assigned to user'))
          ->setSettings([
              'max_length' => 64 // tamanho máximo no drupal 7, @see https://api.drupal.org/api/drupal/modules%21user%21user.install/function/user_schema/7
              // provavelmente ainda se aplica ao D8
          ])
          ->setRequired(TRUE);

        $fields['expire'] = BaseFieldDefinition::create('integer')
          ->setLabel(t('ID'))
          ->setDescription(t('The expiration date.'))
          ->setSetting('unsigned', TRUE);

        $fields['created'] = BaseFieldDefinition::create('created')
          ->setLabel(t('Created'))
          ->setDescription(t('The time that the entity was created.'))
            ->setRequired(TRUE);

        $fields['changed'] = BaseFieldDefinition::create('changed')
          ->setLabel(t('Created'))
          ->setDescription(t('The time that the entity was changed.'))
          ->setRequired(TRUE);

        return $fields;
    }
}