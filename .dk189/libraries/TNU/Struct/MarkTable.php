<?php
namespace TNU\Struct;

class MarkTable extends SubjectTable implements \JsonSerializable {

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

    /**
     * Thêm cấu trúc __SimpleStructData khi tạo dữ liệu Json
     */
    public function jsonSerialize () {
        $JSON = parent::jsonSerialize();

        if (isset($_GET["__SimpleStructData"])) {
            if ($_GET["__SimpleStructData"] === "v1") {
                $data = [];

                foreach ($this->Subjects as $subject) {
                    $entries = \array_filter($this->Entries, function ($entry) use ($subject) {
                        return $entry->MaMon === $subject->MaMon;
                    });
                    foreach ($entries as $entry) {
                        $data[] = $datum = new \JsonObject();

                        $datum->MaMon = $subject->MaMon;
                        $datum->TenMon = $subject->TenMon;
                        $datum->SoTinChi = $subject->HocPhan;
                        $datum->HocPhan = $subject->HocPhan;

                        $datum->CC = $entry->CC;
                        $datum->KT = $entry->KT;
                        $datum->THI = $entry->THI;
                        $datum->TKHP = $entry->TKHP;
                        $datum->DiemChu = $entry->DiemChu;
                    }
                }

                $JSON["__SimpleStructData"] = $data;
            }
        }

        return $JSON;
    }
}
?>
