<?php
namespace Be\Mf\App\Etl\Controller;

use Be\Mf\Be;

class Er
{

    /**
     * 表结构
     *
     * @BePermission("*")
     */
    public function tables(){

        $db = Be::getDb();
        $tables = $db->getTables();

        $formattedTables = array();
        foreach ($tables as $table) {
            $formattedTable = new \stdClass();
            $formattedTable->name = $table->Name;
            $formattedTable->comment = $table->Comment;
            $formattedTables[] = $formattedTable;
        }

        Response::set('tables', $formattedTables);
        Response::display();
    }

    /**
     * 表结构
     *
     * @BePermission("*")
     */
    public function er(){

        $db = Be::getDb();
        $tables = $db->getTables();

        $formattedTables = array();

        $request = Be::getRequest();
        $response = Be::getResponse();
        $filterTables = $request->get('tables');
        if ($filterTables) {
            $filterTables = explode(',', $filterTables);
            foreach ($filterTables as $filterTable) {
                foreach ($tables as $table) {
                    if ($table->Name == $filterTable) {
                        $formattedTable = new \stdClass();
                        $formattedTable->name = $table->Name;
                        $formattedTable->comment = $table->Comment;
                        $formattedTable->fields = $db->getTableFields($table->Name);
                        $formattedTables[] = $formattedTable;
                        break;
                    }
                }
            }

        } else {
            foreach ($tables as $table) {
                $formattedTable = new \stdClass();
                $formattedTable->name = $table->Name;
                $formattedTable->comment = $table->Comment;
                $formattedTable->fields = $db->getTableFields($table->Name);
                $formattedTables[] = $formattedTable;
            }
        }

        $response->set('tables', $formattedTables);
        $response->display();
    }

}
