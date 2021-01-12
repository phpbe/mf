<?php
namespace Be\Mf\App\Etl\Controller;




use Be\Mf\Be;
use Be\Framework\Request;
use Be\Framework\Response;

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

        $filterTables = Request::get('tables');
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

        Response::set('tables', $formattedTables);
        Response::display();
    }

}
