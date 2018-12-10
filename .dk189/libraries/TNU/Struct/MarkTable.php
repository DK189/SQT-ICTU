<?php
namespace TNU\Struct;

class MarkTable extends SubjectTable {

    /**
     * Lần học
     * @var integer
     */
    public $TongTC = -1;

    /**
     * Lần học
     * @var integer
     */
    public $STCTD = -1;

    /**
     * Lần học
     * @var integer
     */
    public $STCTLN = -1;

    /**
     * Lần học
     * @var integer
     */
    public $DTBC = -1;

    /**
     * Lần học
     * @var integer
     */
    public $DTBCQD = -1;

    /**
     * Lần học
     * @var integer
     */
    public $SoMonKhongDat = -1;

    /**
     * Lần học
     * @var integer
     */
    public $SoTCKhongDat = -1;

    /**
     * Thông tin chi tiết các bản ghi điểm môn học
     * @var array
     */
    public $Entries = array();

    /**
     * Thêm bản ghi
     * @param TimeTableEntry $entry Bản ghi
     */
    public function addEntry (MarkEntry $entry) {
        $this->Entries[] = $entry;
    }
}
?>
