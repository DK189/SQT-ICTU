<?php
namespace TNU\Struct;

class TimeTableEntry {
    /**
     * Mã môn học
     * @var string
     */
    public $MaMon = "";

    /**
     * Thời gian
     * @var string
     */
    public $ThoiGian = "";

    /**
     * Ngày học
     * @var string
     */
    public $Ngay = "";

    /**
     * Phòng học
     * @var string
     */
    public $DiaDiem = "";

    /**
     * Hình thức học / thi
     * @var string
     */
    public $HinhThuc = "";

    /**
     * Giáo viên giảng dạy
     * @var string
     */
    public $GiaoVien = "";

    /**
     * Loại lịch ( LichHoc / LichThi )
     * @var string
     */
    public $LoaiLich = "";

    /**
     * Số báo danh của thí sinh / sinh viên tại ca đó
     * @var string
     */
    public $SoBaoDanh = "";
}
?>
