<?php
namespace TNU;

use \Curl\Client;
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

class DTS extends Client implements ClientInterface {
    private $host = "http://daotao.dhsptn.edu.vn/";
    private $prefix = "dhsp";

    public function __construct() {
        parent::__construct();

        // INIT URL TOKEN
        $this->get("login.aspx");
        $headers = self::getCurrentHeader();
        $location = isset($headers["Location"]) ? $headers["Location"] : "";
        preg_match("/^\/" . trim($this->prefix,"\\/") . "\/(\(.*?\))\/login.aspx$/", $location, $matchs);
        if ( count($matchs) == 2 ) {
            $this->prefix = trim($this->prefix,"\\/") . "/" . $matchs[1] . "/";
        }
        // END
    }

    public function url ($uri, $includePrefix = true) {
        return trim($this->host,"\\/") . "/" . ( !!$includePrefix ? trim($this->prefix,"\\/") . "/" : "" ) . trim($uri,"\\/");
    }

    public function get ($uri) {
        return parent::get( self::url($uri) );
    }

    public function post ($uri, array $post, $isJson = false) {
        return parent::post( self::url($uri), $post );
    }

    public function __toString() {
        return "TNU\DTS::{$this->host}";
    }

    /**
     * Đăng nhập
     * @param String $username Tên tài khoản
     * @param String $password Mật khẩu
     * @return Boolean
     */
    public function login ($username, $password) {
        self::get("Login.aspx");
        $doc = self::getCurrentDocument();
        $xpath = new XPath($doc);

        $elems = $xpath->query("//input[@name]");
        $postArr= array();
        foreach ($elems as $elem) {
            $postArr[$elem->getAttribute("name")] = $elem->getAttribute("value");
        }
        $postArr["txtUserName"] = $username;
        $postArr["txtPassword"] = $password;
        self::post("Login.aspx", $postArr);

        if ( isset(self::getCurrentHeader()["Set-Cookie"]) && isset(self::getCurrentHeader()["Location"]) ) {
            self::setCookie(self::getCurrentHeader()["Set-Cookie"]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Lấy thông tin sinh viên
     * @method getStudent
     * @return \TNU\Struct\Student
     */
    public function getStudent () {
        self::get("StudentMark.aspx");
        $doc = self::getCurrentDocument();
        $xpath = new XPath($doc);

        $student = new Student();

        if ( !!($MaSinhVien = $xpath->evaluate("//span[@id='lblStudentCode']")) && $MaSinhVien->length > 0 ) {
            // $student->MaSinhVien = preg_replace("/.*?\(([A-z0-9]\w+)\)/","$1",$MaSinhVien[0]->textContent);
            $student->MaSinhVien = $MaSinhVien[0]->textContent;
        }

        if ( !!($HoTen = $xpath->evaluate("//span[@id='lblStudentName']")) && $HoTen->length > 0 ) {
            $student->HoTen = $HoTen[0]->textContent;
        }

        if ( !!($Lop = $xpath->evaluate("//span[@id='lblAdminClass']")) && $Lop->length > 0 ) {
            $student->Lop = $Lop[0]->textContent;
        }

        if ( !!($Nganh = $xpath->evaluate("//select[@id='drpField']/option[@selected]")) && $Nganh->length > 0 ) {
            $student->Nganh = $Nganh[0]->textContent;
        }

        if ( !!($NienKhoa = $xpath->evaluate("//span[@id='lblAy']")) && $NienKhoa->length > 0 ) {
            $student->NienKhoa = $NienKhoa[0]->textContent;
        }

        if ( !!($HeDaoTao = $xpath->evaluate("//select[@id='drpHeDaoTaoId']/option[@selected]")) && $HeDaoTao->length > 0 ) {
            $student->HeDaoTao = $HeDaoTao[0]->textContent;
        }

        $student->Truong = "Trường Đại học Sư phạm - Đại học Thái Nguyên";

        return $student;
    }


    protected function getSemesterOf($uri, $semester = false) {
        self::get($uri);
        $doc = self::getCurrentDocument();
        $xpath = new XPath($doc);

        $elems = $xpath->query("//select[@name='drpSemester']/option");
        $semesters = array_values(
            array_map(
                function ($x) {
                    $semester = new Semester;
                    $semester->TenKy = $x->textContent;
                    $semester->MaKy = $x->getAttribute("value");
                    $semester->KyHienTai = !!$x->getAttribute("selected");
                    return $semester;
                }, array_filter(iterator_to_array($elems), function ($x) use ($semester) {
                    return !$semester ?: ($x->textContent === $semester || $x->getAttribute("value") === $semester || ($semester === !!$x->getAttribute("selected")));
                })
            )
        );
        return !$semester ? $semesters : ( count($semesters) === 1 ? $semesters[0] : false);
    }

    /**
     * Lấy thông tin các kì học của sinh viên
     * @return TNU\Struct\Semester[]
     */
    public function getSemesterOfStudy ($semester = false) {
        return self::getSemesterOf("Reports/Form/StudentTimeTable.aspx", $semester);
    }

    /**
     * Lấy lịch học
     * @param String $semester Mã học kì
     * @return TNU\Struct\TimeTable
     */
    public function getTimeTableOfStudy ($semester = true) {
        self::get("Reports/Form/StudentTimeTable.aspx");
        $doc = self::getCurrentDocument();
        $xpath = new XPath($doc);

        $postArr= array();

        foreach ($xpath->query("//input[@name]") as $elem) {
            $postArr[$elem->getAttribute("name")] = $elem->getAttribute("value");
        }
        foreach ($xpath->query("//select[@name]") as $elem) {
            $opts = $xpath->query("option[@selected]", $elem);
            if ( $opts->length >= 1 ) {
                $postArr[$elem->getAttribute("name")] = $opts[0]->getAttribute("value");
            }
        }
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

        $postArr["drpSemester"] = $semester->MaKy;
        $postArr["drpType"] = "B";

        $drpTerm = $xpath->query("//select[@name='drpTerm']/option");
        $drpTermIndex = 0;
        $tmpXls = tmpfile();
        $tmpXlsUri = stream_get_meta_data($tmpXls)["uri"];
        do {
            try {
                if ( $drpTerm->length > 0 ) {
                    $postArr["drpTerm"] = $drpTerm[$drpTermIndex]->getAttribute("value");
                } else {
                    unset($postArr["drpTerm"]);
                }
                self::post("Reports/Form/StudentTimeTable.aspx", $postArr);

                $xlsContent = explode("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" >", self::getCurrentBody(), 2);

                if ( count($xlsContent) == 2 && strlen($xlsContent[0]) > 100 ) {
                    file_put_contents($tmpXlsUri, $xlsContent[0]);
                    $xls = new ExcelReader($tmpXlsUri);

                    $ICTU_WEEKDAY_MAP = [
                        "CN" => 0,
                        "2" => 1,
                        "3" => 2,
                        "4" => 3,
                        "5" => 4,
                        "6" => 5,
                        "7" => 6,
                    ];

                    for ($i = 11; $i <= $xls->sheets[0]["numRows"]; $i++) {
                        if ( isset($xls->sheets[0]["cells"][$i]) ) {
                            $row = $xls->sheets[0]["cells"][$i];
                            if ( isset($row[1]) && !empty("" . $row[1]) && isset($row[2]) && !empty("" . $row[2]) ) {
                                $subject = new Subject();
                                $subject->MaMon = $row[2];
                                $subject->TenMon = $row[4];
                                $subject->HocPhan = $row[5];
                                $TKB->addSubject($subject);

                                $_tmpHocPhan = explode(".", $row[5]);
                                if ( strstr($_tmpHocPhan[count($_tmpHocPhan) - 1], "TH") ) {
                                    $hinhThuc = "Thực hành";
                                } else if ( strstr($_tmpHocPhan[count($_tmpHocPhan) - 1], "TL") ) {
                                    $hinhThuc = "Thảo luận";
                                } else {
                                    $hinhThuc = "Thông thường";
                                }

                                $start = explode("-", $row[11], 2);
                                $end = explode("/", $start[1], 3);
                                $start = explode("/", $start[0], 3);

                                $start = strtotime(sprintf("%d-%d-%d", $start[2], $start[1], $start[0]));
                                $end = strtotime(sprintf("%d-%d-%d", $end[2], $end[1], $end[0]));

                                if( (date("w", $start)) < $ICTU_WEEKDAY_MAP[$row[1]] ){
            						$start += ( $ICTU_WEEKDAY_MAP[$row[1]] - (date("w", $start)) ) * 24 * 60 * 60;
            					}else if( (date("w", $start)) > $ICTU_WEEKDAY_MAP[$row[1]] ){
            						$start += ( 7 - (date("w", $start)) + $ICTU_WEEKDAY_MAP[$row[1]] ) * 24 * 60 * 60;
            					}

                                for ($j = $start; $j <= $end; $j += 60*60*24*7) {
                                    $entry = new TimeTableEntry();
                                    $entry->MaMon = $row[2];
                                    $entry->ThoiGian = $row[9];
                                    $entry->Ngay = date("Y-m-d", $j);
                                    $entry->DiaDiem = $row[10];
                                    $entry->GiaoVien = $row[8];
                                    $entry->HinhThuc = $hinhThuc;
                                    $entry->LoaiLich = "LichHoc";
                                    $TKB->addEntry($entry);
                                }
                            } else {

                            }
                        }
                    }
                }
            } catch (Exception $e) {

            }
            $drpTermIndex++;
        } while ( $drpTermIndex < $drpTerm->length );
        return $TKB;
    }

    /**
     * Lấy thông tin các kì thi của sinh viên
     * @return TNU\Struct\Semester[]
     */
    public function getSemesterOfTest ($semester = false) {
        return self::getSemesterOf("StudentViewExamList.aspx", $semester);
    }

    /**
     * Lấy lịch thi
     * @param String $semester Mã kì thi
     * @return TNU\Struct\TimeTable
     */
    public function getTimeTableOfTest ($semester) {
        self::get("StudentViewExamList.aspx");

        $doc = self::getCurrentDocument();
        $xpath = new XPath($doc);

        $postArr= array();

        foreach ($xpath->query("//input[@name]") as $elem) {
            $postArr[$elem->getAttribute("name")] = $elem->getAttribute("value");
        }
        foreach ($xpath->query("//select[@name]") as $elem) {
            $opts = $xpath->query("option[@selected]", $elem);
            if ( $opts->length >= 1 ) {
                $postArr[$elem->getAttribute("name")] = $opts[0]->getAttribute("value");
            } else {
                $postArr[$elem->getAttribute("name")] = "";
            }
        }
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

        $postArr["drpSemester"] = $semester->MaKy;

        self::post("StudentViewExamList.aspx", $postArr);

        $doc = self::getCurrentDocument();
        $xpath = new XPath($doc);

        $arrDrpDotThi = array();

        foreach ($xpath->query("//input[@name]") as $elem) {
            $postArr[$elem->getAttribute("name")] = $elem->getAttribute("value");
        }
        foreach ($xpath->query("//select[@name]") as $elem) {
            if ( $elem->getAttribute("name") == "drpDotThi" ) {
                $opts = $xpath->query("option", $elem);
                foreach ($opts as $opt) {
                    if (!!$opt->getAttribute("value")) {
                        $arrDrpDotThi[] = $opt->getAttribute("value");
                    }
                }
            }
            $opts = $xpath->query("option[@selected]", $elem);
            if ( $opts->length >= 1 ) {
                $postArr[$elem->getAttribute("name")] = $opts[0]->getAttribute("value");
            } else {
                $postArr[$elem->getAttribute("name")] = "";
            }
        }
        // var_dump($arrDrpDotThi);
        for ($lanThi=0; $lanThi <= 1; $lanThi++) {
            foreach ($arrDrpDotThi as $drpDotThi) {
                $postArr["drpDotThi"] = $drpDotThi;
                $postArr["drpExaminationNumber"] = $lanThi;

                self::post("StudentViewExamList.aspx", $postArr);

                $doc = self::getCurrentDocument();
                $xpath = new XPath($doc);

                $table = $xpath->query("//table[@id='tblCourseList']");
                if ( $table = ($table->length == 1 ? $table[0] : false) ) {
                    foreach ($xpath->query($table->getNodePath() . "/tr", $table) as $k => $tr) {
                        if ( $k > 0 && $tr->childNodes->length >= 17 ) {
                            $row = $tr->childNodes;

                            $pad = 0;

                            if ( trim(str_replace(" ", "", preg_replace("/\\s/", " ", $row[$pad + 2]->textContent))) == "" ) {
                                $pad = 1;
                            }

                            $subject = new Subject();
                            $subject->MaMon = trim(str_replace("  ", " ", preg_replace("/\\s/", " ", $row[$pad + 2]->textContent)));
                            $subject->TenMon = trim(str_replace("  ", " ", preg_replace("/\\s/", " ", $row[$pad + 4]->textContent)));
                            $subject->SoTinChi = intval(trim(str_replace("  ", " ", preg_replace("/\\s/", " ", $row[$pad + 6]->textContent))));
                            $TKB->addSubject($subject);

                            $entry = new TimeTableEntry();
                            $entry->MaMon = trim(str_replace("  ", " ", preg_replace("/\\s/", " ", $row[$pad + 2]->textContent)));
                            $entry->Ngay = trim(str_replace("  ", " ", preg_replace("/\\s/", " ", $row[$pad + 8]->textContent)));
                            $entry->ThoiGian = trim(str_replace("  ", " ", preg_replace("/\\s/", " ", $row[$pad + 10]->textContent)));
                            $entry->HinhThuc = trim(str_replace("  ", " ", preg_replace("/\\s/", " ", $row[$pad + 12]->textContent)));
                            $entry->SoBaoDanh = trim(str_replace("  ", " ", preg_replace("/\\s/", " ", $row[$pad + 14]->textContent)));
                            $entry->DiaDiem = trim(str_replace("  ", " ", preg_replace("/\\s/", " ", $row[$pad + 16]->textContent)));
                            $entry->LoaiLich = "LichThi";

                            $eNgay = \explode("/", $entry->Ngay, 3);
                            if (count($eNgay) >= 3) {
                                $entry->Ngay = \sprintf("%s-%s-%s", $eNgay[2], $eNgay[1], $eNgay[0]);
                            }

                            $TKB->addEntry($entry);
                        }
                    }
                }
            }
        }

        return $TKB;
    }

    /**
     * Lấy điểm tổng kết
     * @return TNU\Struct\MarkTable
     */
    public function getMarkTable () {
        self::get("StudentMark.aspx");

        $doc = self::getCurrentDocument();
        $xpath = new XPath($doc);

        $semester = new Semester;
        $semester->TenKy = "Tất cả";
        $semester->MaKy = md5($semester->TenKy);
        $semester->KyHienTai = true;

        $result = new MarkTable();
        $result->setSemeter($semester);

        $table = $xpath->query("//table[@id='grdResult']");
        if ( $table = ($table->length == 1 ? $table[0] : false) ) {
            $trs = $xpath->query($table->getNodePath() . "/tr", $table);
            if ($trs->length > 0) {
                $tr = $trs[$trs->length - 1];
                $tds = $xpath->query($tr->getNodePath() . "/td", $tr);

                //
                $TongTC = $tds[ 12 ]->textContent;
                $STCTD = 0; // $tds[ $SoCotThongTin + 1 ]->textContent;
                $STCTLN = $tds[ 6 ]->textContent;
                $DTBC = $tds[ 2 ]->textContent;
                $DTBCQD = $tds[ 4 ]->textContent;
                $SoMonKhongDat = -1; // $tds[ $SoCotThongTin + 5 ]->textContent;
                $SoTCKhongDat = -1; // $tds[ $SoCotThongTin + 6 ]->textContent;

                $result->TongTC = intval($TongTC);
                $result->STCTD = intval($STCTD);
                $result->STCTLN = intval($STCTLN);
                $result->DTBC = floatval($DTBC);
                $result->DTBCQD = floatval($DTBCQD);
                $result->SoMonKhongDat = intval($SoMonKhongDat);
                $result->SoTCKhongDat = intval($SoTCKhongDat);
                //

            }
        }

        $table = $xpath->query("//table[@id='tblStudentMark']");
        if ( $table = ($table->length == 1 ? $table[0] : false) ) {
            $trs = $xpath->query($table->getNodePath() . "/tr", $table);

            if ($trs->length >= 2) {

                for ($i = 1; $i < $trs->length; $i++) {
                    $tr = $trs[$i];
                    $tds = $xpath->query($tr->getNodePath() . "/td", $tr);

                    if ($tds->length >= 14) {

                        $sub = new Subject();
                        $sub->MaMon = $tds[1]->textContent;
                        $sub->TenMon = $tds[2]->textContent;
                        $sub->SoTinChi = $tds[3]->textContent;

                        $entry = new MarkEntry();
                        $entry->MaMon = $sub->MaMon;
                        $entry->CC = $tds[9]->textContent; // intval($point["CC"]);
                        $entry->KT = $tds[10]->textContent; // intval($point["CC"]);
                        $entry->THI = $tds[11]->textContent; // intval($point["THI"]);
                        $entry->TKHP = $tds[12]->textContent; // intval($point["TKHP"]);
                        $entry->DiemChu = $tds[13]->textContent;

                        $result->addSubject($sub);
                        $result->addEntry($entry);
                    }


                }
            }
        }

        return $result;
        return var_export($postArr, true);
        return self::getCurrentBody();
    }
}
?>
