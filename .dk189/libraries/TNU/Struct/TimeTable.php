<?php
namespace TNU\Struct;

use \TNU\Struct\TimeTableEntry as Entry;

class TimeTable extends SubjectTable {
    /**
     * Thông tin chi tiết các bản ghi thời khoá biểu
     * @var array
     */
    public $Entries = array();

    /**
     * Thêm bản ghi
     * @param TimeTableEntry $entry Bản ghi
     */
    public function addEntry (Entry $entry) {
        $this->Entries[] = $entry;
    }
}
?>
