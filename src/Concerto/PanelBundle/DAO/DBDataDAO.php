<?php

namespace Concerto\PanelBundle\DAO;

use Doctrine\DBAL\Connection;

class DBDataDAO {

    private $connection;

    public function __construct(Connection $con) {
        $this->connection = $con;
    }

    public function getData($table_name, $id = null, $filter = null, $operators = null) {
        $stmt = $this->getStreamDataResult($table_name, $id, $filter, $operators);
        return $stmt->fetchAll();
    }

    public function getStreamDataResult($table_name, $id = null, $filter = null, $operators = null) {
        $q = $this->connection->createQueryBuilder()->select("*")->from($table_name, "d");

        $i = 0;
        if ($filter !== null) {
            foreach ($filter as $k => $v) {
                if ($i == 0) {
                    $q = $q->where("d.$k " . $operators[$k] . " :$k")->setParameter(":$k", $v);
                } else {
                    $q = $q->andWhere("d.$k " . $operators[$k] . " :$k")->setParameter(":$k", $v);
                }
                $i++;
            }
        }

        $result = null;
        if ($id) {
            if ($i == 0)
                $q = $q->where("d.id = :id");
            else
                $q = $q->andWhere("d.id = :id");
            $result = $q->setParameter(":id", $id)->execute();
        } else
            $result = $q->execute();
        return $result;
    }

    public function fetchMatchingData($table_name, $filters) {
        $builder = $this->connection->createQueryBuilder()->select("*")->from($table_name, "d");

        if (!$filters) {
            return $builder->execute()->fetchAll();
        }
        $f = json_decode($filters, true);

        foreach ($f["filters"] as $k => $v) {
            $builder->orWhere("d.$k LIKE :filter")->setParameter('filter', '%' . $v . '%');
        }
        foreach ($f["sorting"] as $sort) {
            $builder->addOrderBy("d." . $sort["name"], $sort["dir"]);
        }

        return $builder->setFirstResult(($f["paging"]["page"] - 1) * $f["paging"]["pageSize"])->setMaxResults($f["paging"]["pageSize"])->execute()->fetchAll();
    }

    public function countMatchingData($table_name, $filters) {
        $builder = $this->connection->createQueryBuilder()->select('count(d.id)')->from($table_name, "d");

        if ($filters) {
            $f = json_decode($filters, true);

            foreach ($f["filters"] as $k => $v) {
                $builder->orWhere("d.$k LIKE :filter")->setParameter('filter', '%' . $v . '%');
            }
        }

        return (int) $builder->execute()->fetchColumn(0);
    }

    public function updateRow($table_name, $row_id, $values, $id_field = "id") {
        $qb = $this->connection->createQueryBuilder()->update($table_name);
        $i = 0;
        foreach ($values as $k => $v) {
            $qb->set("`" . $k . "`", ":k" . $i)->setParameter(":k" . $i, $v);
            $i++;
        }
        $qb->where("$id_field=:id")->setParameter(":id", $row_id)->execute();
        return array();
    }

    public function removeRow($table_name, $id, $id_field = "id") {
        $this->connection->delete($table_name, array("$id_field" => $id));
        return array();
    }

    public function addBlankRow($table_name) {
        // with pgsql doctrine attempts to execute nonsense like: "INSERT INTO main_table () VALUES ()"
        if ($this->connection->getDriver()->getName() == 'pdo_pgsql')
            $this->connection->query('INSERT INTO ' . $table_name . ' ( id )  VALUES ( DEFAULT )');
        else
            $this->connection->insert($table_name, array());

        return array();
    }

    public function insertRow($table_name, $values) {
        $this->connection->insert($table_name, $values);
        return $this->getLastInsertId();
    }

    public function getLastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function addInsertBatch($table_name, $data, $batch = null) {
        //TODO check sqlite and sql server
        if ($this->connection->getDriver()->getName() !== "pdo_mysql") {
            $this->insertRow($table_name, $data);
        } else {
            if ($batch === null) {
                $batch = array(
                    "insert_template" => 'INSERT INTO ' . $table_name . ' (' . implode(', ', array_keys($data)) . ')  VALUES ',
                    "values_template" => '(' . implode(', ', array_fill(0, count($data), '?')) . ')',
                    "index" => 0,
                    "sql" => "",
                    "params" => array()
                );
            }
            if ($batch["index"] == 0) {
                $batch["sql"].=$batch["insert_template"];
            } else {
                $batch["sql"].=",";
            }
            $batch["sql"].=$batch["values_template"];
            $batch["params"] = array_merge($batch["params"], array_values($data));
            $batch["index"] ++;
            if ($batch["index"] % 50 == 0) {
                $batch = $this->flushInsertBatch($batch);
            }
        }
        return $batch;
    }

    public function flushInsertBatch($batch) {
        if ($this->connection->getDriver()->getName() === "pdo_mysql") {
            if ($batch !== null && $batch["index"] > 0) {
                $this->connection->connect();
                $this->connection->executeUpdate($batch["sql"], $batch["params"]);
                $batch["sql"] = "";
                $batch["params"] = array();
                $batch["index"] = 0;
            }
        }
        return $batch;
    }

    public function truncate($table_name) {
        $dbPlatform = $this->connection->getDatabasePlatform();
        if ($this->connection->getDriver()->getName() === "pdo_mysql")
            $this->connection->query('SET FOREIGN_KEY_CHECKS=0');

        $q = $dbPlatform->getTruncateTableSql($table_name);
        $this->connection->executeUpdate($q);

        if ($this->connection->getDriver()->getName() === "pdo_mysql")
            $this->connection->query('SET FOREIGN_KEY_CHECKS=1');

        return array();
    }

}
