<?php
declare(strict_types = 1);

namespace Drupal\role_expire;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

class RoleExpireStorageSchema extends SqlContentEntityStorageSchema
{
    /**
     * {@inheritdoc}
     */
    protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE)
    {
        $schema = parent::getEntitySchema($entity_type, $reset);

        $schema['role_expire']['unique keys'] += [
            'role_expire_unique' => ['user_id', 'role_id'],
        ];

        return $schema;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping)
    {
        $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
        $field_name = $storage_definition->getName();

        if ($table_name == 'role_expire') {
            switch ($field_name) {
                case 'expire':
                    $this->addSharedTableFieldIndex($storage_definition, $schema, TRUE);
                    break;
                case 'user_id':
                    $this->addSharedTableFieldIndex($storage_definition, $schema, TRUE);
                    $this->addSharedTableFieldForeignKey($storage_definition, $schema, 'users_field_data', 'uid');
                    break;
            }
        }

        return $schema;
    }
}