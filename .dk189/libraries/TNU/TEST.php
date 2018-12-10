<?php
namespace TNU;

use \ExcelReader;
use \DOMDocument as Document;
use \DOMXPath as XPath;
use \TNU\Struct\Student;
use \TNU\Struct\Semester;
use \TNU\Struct\Subject;
use \TNU\Struct\TimeTableEntry;
use \TNU\Struct\TimeTable;
use \TNU\Struct\MarkTable;
use \TNU\Struct\MarkEntry;

class TEST implements ClientInterface {
    protected $username;

    public function __toString() {
        return "TNU\TEST::{$this->username}";
    }

    /**
     * Đăng nhập
     * @param String $username Tên tài khoản
     * @param String $password Mật khẩu
     * @return Boolean
     */
    public function login ($username, $password) {
        if ( strrpos(strtoupper($username), "TESTER") == 0 ) {
            if ( md5($username) === $password ) {
                $this->username = strtoupper($username);
                return true;
            }
        }
        return false;
    }

    /**
     * Lấy thông tin sinh viên
     * @method getStudent
     * @return \TNU\Struct\Student
     */
    public function getStudent () {
        $student = new Student();

        $student->MaSinhVien = $this->username;

        $student->HoTen = "TESTER";

        $student->Lop = "GSPM_K13B";

        $student->Nganh = "Giám Sát Phần Mềm";

        $student->Truong = "Học's Đại's";

        $student->NienKhoa = "K13";

        $student->HeDaoTao = "ĐHCQ";

        return $student;
    }


    protected function getSemesterOf($uri, $semester = false) {
        $semesters = array_values(
            array_map(
                function ($x) {
                    $semester = new Semester;
                    $semester->TenKy = $x[0];
                    $semester->MaKy = $x[1];
                    $semester->KyHienTai = $x[2];
                    return $semester;
                }, array_filter([
                        ["2_2016_2017","11111111111111111111111111111111",true],
                        ["1_2016_2017","22222222222222222222222222222222",false],
                        ["3_2015_2016","33333333333333333333333333333333",false],
                        ["2_2015_2016","44444444444444444444444444444444",false],
                        ["1_2015_2016","55555555555555555555555555555555",false],
                    ], function ($x) use ($semester) {
                        return !$semester ?: ($x[0] === $semester || $x[1] === $semester || ($semester === !!$x[2]));
                    }
                )
            )
        );
        return !$semester ? $semesters : ( count($semesters) === 1 ? $semesters[0] : false);
    }

    /**
     * Lấy thông tin các kì học của sinh viên
     * @return TNU\Struct\Semester[]
     */
    public function getSemesterOfStudy ($semester = false) {
        return self::getSemesterOf("STUDY", $semester);
    }

    /**
     * Lấy lịch học
     * @param String $semester Mã học kì
     * @return TNU\Struct\TimeTable
     */
    public function getTimeTableOfStudy ($semester = true) {
        if (is_string($semester)) {
            $semester = self::getSemesterOfStudy($semester);
        }
        if (is_object($semester) && $semester instanceof Semester) {
            if ( empty("" . $semester->TenKy) && empty("" . $semester->MaKy) ) {
                $semester = self::getSemesterOfStudy(true);
            } else if (empty("" . $semester->TenKy)) {
                $semester = self::getSemesterOfStudy($semester->MaKy);
            } else if (empty("" . $semester->MaKy)) {
                $semester = self::getSemesterOfStudy($semester->TenKy);
            }
        } else {
            $semester = self::getSemesterOfStudy(true);
        }
        $TKB = new TimeTable();
        $TKB->setSemeter($semester);

        $subject = new Subject();
        $subject->MaMon = "TES312";
        $subject->TenMon = "Kiểm thử phần mềm";
        $subject->HocPhan = "Kiểm thử phần mềm-1-14-3 (K13B.KTPM.N11)";
        $subject->GiaoVien = "Albert Einstein";
        $TKB->addSubject($subject);

        for ($i = 1; $i <= 1; $i++) {
            if ( isset($xls->sheets[0]["cells"][$i]) ) {
                $row = $xls->sheets[0]["cells"][$i];
                if ( isset($row[1]) && !empty("" . $row[1]) && isset($row[2]) && !empty("" . $row[2]) ) {


                    $_tmpHocPhan = ["TT", "TH", "TL"][rand(0,2)];
                    if ( $_tmpHocPhan === "TH" ) {
                        $hinhThuc = "Thực hành";
                    } else if ( $_tmpHocPhan === "TL" ) {
                        $hinhThuc = "Thảo luận";
                    } else {
                        $hinhThuc = "Thông thường";
                    }

                    $tenKy = explode("_", $semester->TenKy, 3);

                    $ki = intval($tenKy[0]);
                    $n1 = intval($tenKy[1]);
                    $n2 = intval($tenKy[2]);

                    if ($i === 1) {
                        $start = [
                            rand(1, 27) . "/" . rand(9, 12) . "/" . $n1,
                            rand(1, 27) . "/" . rand(9, 12) . "/" . $n1
                        ];
                    } else if ($i === 2) {
                        $start = [
                            rand(1, 27) . "/" . rand(1, 4) . "/" . $n1,
                            rand(1, 27) . "/" . rand(1, 4) . "/" . $n1
                        ];
                    } else if ($i === 3) {
                        $start = [
                            rand(1, 27) . "/" . rand(6, 8) . "/" . $n1,
                            rand(1, 27) . "/" . rand(6, 8) . "/" . $n1
                        ];
                    }

                    $end = explode("/", $start[1], 3);
                    $start = explode("/", $start[0], 3);

                    $start = strtotime(sprintf("%d-%d-%d", $start[2], $start[1], $start[0]));
                    $end = strtotime(sprintf("%d-%d-%d", $end[2], $end[1], $end[0]));

                    if ($end < $start) {
                        $_end = $end;
                        $end = $start;
                        $start = $_end;
                    }

                    for ($j = $start; $j <= $end; $j += 60*60*24*7) {
                        $entry = new TimeTableEntry();
                        $entry->MaMon = $subject->MaMon;
                        $entry->ThoiGian = ["1,2,3", "3,4,5", "6,7,8", "8,9,10"][rand(0,3)];
                        $entry->Ngay = date("Y-m-d", $j);
                        $entry->DiaDiem = "C10.001";
                        $entry->HinhThuc = $hinhThuc;
                        $TKB->addEntry($entry);
                    }
                } else {

                }
            }
        }

        return $TKB;
    }

    /**
     * Lấy thông tin các kì thi của sinh viên
     * @return TNU\Struct\Semester[]
     */
    public function getSemesterOfTest ($semester = false) {
        return self::getSemesterOf("TEST", $semester);
    }

    /**
     * Lấy lịch thi
     * @param String $semester Mã kì thi
     * @return TNU\Struct\TimeTable
     */
    public function getTimeTableOfTest ($semester) {
        if (is_string($semester)) {
            $semester = self::getSemesterOfStudy($semester);
        }
        if (is_object($semester) && $semester instanceof Semester) {
            if ( empty("" . $semester->TenKy) && empty("" . $semester->MaKy) ) {
                $semester = self::getSemesterOfStudy(true);
            } else if (empty("" . $semester->TenKy)) {
                $semester = self::getSemesterOfStudy($semester->MaKy);
            } else if (empty("" . $semester->MaKy)) {
                $semester = self::getSemesterOfStudy($semester->TenKy);
            }
        } else {
            $semester = self::getSemesterOfStudy(true);
        }
        $TKB = new TimeTable();
        $TKB->setSemeter($semester);

        $subject = new Subject();
        $subject->MaMon = "TES312";
        $subject->TenMon = "Kiểm thử phần mềm";
        $subject->SoTinChi = 3;
        $TKB->addSubject($subject);

        $tenKy = explode("_", $semester->TenKy, 3);

        $ki = intval($tenKy[0]);
        $n1 = intval($tenKy[1]);
        $n2 = intval($tenKy[2]);

        if ($ki === 1) {
            $start = [
                rand(1, 27) . "/" . rand(9, 12) . "/" . $n1,
                rand(1, 27) . "/" . rand(9, 12) . "/" . $n1
            ];
        } else if ($ki === 2) {
            $start = [
                rand(1, 27) . "/" . rand(1, 4) . "/" . $n1,
                rand(1, 27) . "/" . rand(1, 4) . "/" . $n1
            ];
        } else if ($ki === 3) {
            $start = [
                rand(1, 27) . "/" . rand(6, 8) . "/" . $n1,
                rand(1, 27) . "/" . rand(6, 8) . "/" . $n1
            ];
        }

        $entry = new TimeTableEntry();
        $entry->MaMon = "TES312";
        $entry->Ngay = date("Y-m-d", strtotime($start[0]));
        $entry->ThoiGian = "Ca 1";
        $entry->HinhThuc = "Vấn Đáp";
        $entry->DiaDiem = "C10.002";
        $TKB->addEntry($entry);

        return $TKB;
    }

    /**
     * Lấy điểm tổng kết
     * @return TNU\Struct\MarkTable
     */
    public function getMarkTable () {
        $semester = new Semester;
        $semester->TenKy = "0_0000_0000";
        $semester->MaKy = md5($semester->TenKy);
        $semester->KyHienTai = true;

        $result = new MarkTable();
        $result->setSemeter($semester);

        $subject = new Subject();
        $subject->MaMon = "TES312";
        $subject->TenMon = "Kiểm thử phần mềm";
        $subject->SoTinChi = 3;

        $result->addSubject($subject);

        $entry = new MarkEntry();
        $entry->MaMon = "TES312";
        $entry->LanHoc = 1;
        $entry->LanThi = 1;
        $entry->DiemThu = 1;
        $entry->DanhGia = "TOT";
        $entry->CC = 9.5;
        $entry->THI = 9;
        $entry->TKPH = 9.15;
        $entry->DiemChu = "A";
        $result->addEntry($entry);

        return $result;
    }
}
?>
