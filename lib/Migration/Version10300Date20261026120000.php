<?php
namespace OCA\Followme\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use OCP\Security\ICrypto;

class Version10300Date20261026120000 extends SimpleMigrationStep {
    public function __construct(private IDBConnection $db, private ICrypto $crypto) {}

    public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {
        // Keep the last saved value of each setting before adding uniqueness.
        $qb = $this->db->getQueryBuilder();
        $rows = $qb->select('id', 'name', 'value')->from('followme_parameter')->orderBy('id', 'DESC')->executeQuery()->fetchAllAssociative();
        $seen = [];
        $this->db->beginTransaction();
        try {
            foreach ($rows as $row) {
                $qb = $this->db->getQueryBuilder();
                if (isset($seen[$row['name']])) {
                    $qb->delete('followme_parameter')->where($qb->expr()->eq('id', $qb->createNamedParameter($row['id'])))->executeStatement();
                    continue;
                }
                $seen[$row['name']] = true;
                if (in_array($row['name'], ['passwordWP', 'passwordRC'], true) && $row['value'] !== '' && !str_starts_with($row['value'], 'encrypted:v1:')) {
                    $qb->update('followme_parameter')->set('value', $qb->createNamedParameter('encrypted:v1:' . $this->crypto->encrypt($row['value'])))
                        ->where($qb->expr()->eq('id', $qb->createNamedParameter($row['id'])))->executeStatement();
                }
            }
            $this->db->commit();
        } catch (\Throwable $e) { $this->db->rollBack(); throw $e; }
    }

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();
        $table = $schema->getTable('followme_parameter');
        if (!$table->hasIndex('followme_param_name')) { $table->addUniqueIndex(['name'], 'followme_param_name'); }
        $table = $schema->getTable('followme');
        if (!$table->hasIndex('followme_date')) { $table->addIndex(['date'], 'followme_date'); }
        if (!$table->hasIndex('followme_category')) { $table->addIndex(['categorie'], 'followme_category'); }
        return $schema;
    }
}
