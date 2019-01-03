<?php
namespace TNU\Struct;

use \TNU\Struct\TimeTableEntry as Entry;

class TimeTable extends SubjectTable implements \JsonSerializable {
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

                        $datum->DiaDiem = $entry->DiaDiem;
                        $datum->GiaoVien = $entry->GiaoVien;
                        $datum->HinhThuc = $entry->HinhThuc;
                        $datum->LoaiLich = $entry->LoaiLich;
                        $datum->Ngay = $entry->Ngay;
                        $datum->SoBaoDanh = $entry->SoBaoDanh;
                        $datum->ThoiGian = $entry->ThoiGian;
                    }
                }

                $JSON["__SimpleStructData"] = $data;
            }
        }

        return $JSON;
    }
}
?>
