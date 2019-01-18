<?php
namespace TNU;

use \JsonObject;

class Machine implements ClientInterface {
    public $Client;
    private $Clients;

    public function __construct () {
        $this->Clients = new \JsonObject();

        try {
            $this->Clients->{"TEST"} = new \TNU\TEST();
        } catch(\Exception $e) {
            $this->Clients->{"TEST"} = false;
        }

        try {
            $this->Clients->{"DTC"} = "\TNU\DTC";
        } catch(\Exception $e) {
            $this->Clients->{"DTC"} = false;
        }

        try {
            $this->Clients->{"DTE"} = "\TNU\DTE";
        } catch(\Exception $e) {
            $this->Clients->{"DTE"} = false;
        }

        try {
            $this->Clients->{"DTF"} = "\TNU\DTF";
        } catch(\Exception $e) {
            $this->Clients->{"DTF"} = false;
        }

        try {
            $this->Clients->{"DTN"} = "\TNU\DTN";
        } catch(\Exception $e) {
            $this->Clients->{"DTN"} = false;
        }

        try {
            $this->Clients->{"DTS"} = "\TNU\DTS";
        } catch(\Exception $e) {
            $this->Clients->{"DTS"} = false;
        }

        try {
            $this->Clients->{"DTY"} = "\TNU\DTY";
        } catch(\Exception $e) {
            $this->Clients->{"DTY"} = false;
        }

        try {
            $this->Clients->{"DTZ"} = "\TNU\DTZ";
        } catch(\Exception $e) {
            $this->Clients->{"DTZ"} = false;
        }

        try {
            $this->Clients->{"YDD"} = "\TNU\YDD";
        } catch(\Exception $e) {
            $this->Clients->{"YDD"} = false;
        }
    }

    /**
     * Đăng nhập
     * @param String $username Tên tài khoản
     * @param String $password Mật khẩu
     * @return Boolean
     */
    public function login ($username, $password) {
        if (false) {
            // no thing in here... :v
        } else if (!!$this->Clients->{"DTC"} && !!$username && strtoupper(substr($username, 0, 3)) === "DTC") {
            $this->Client = new $this->Clients->{"DTC"};
        } else if (!!$this->Clients->{"DTE"} && !!$username && strtoupper(substr($username, 0, 3)) === "DTE") {
            $this->Client = new $this->Clients->{"DTE"};
        } else if (!!$this->Clients->{"DTF"} && !!$username && strtoupper(substr($username, 0, 3)) === "DTF") {
            $this->Client = new $this->Clients->{"DTF"};
        } else if (!!$this->Clients->{"DTN"} && !!$username && strtoupper(substr($username, 0, 3)) === "DTN") {
            $this->Client = new $this->Clients->{"DTN"};
        } else if (!!$this->Clients->{"DTS"} && !!$username && strtoupper(substr($username, 0, 3)) === "DTS") {
            $this->Client = new $this->Clients->{"DTS"};
        } else if (!!$this->Clients->{"DTY"} && !!$username && strtoupper(substr($username, 0, 3)) === "DTY") {
            $this->Client = new $this->Clients->{"DTY"};
        } else if (!!$this->Clients->{"DTZ"} && !!$username && strtoupper(substr($username, 0, 3)) === "DTZ") {
            $this->Client = new $this->Clients->{"DTZ"};
        } else if (!!$this->Clients->{"YDD"} && !!$username && strtoupper(substr($username, 0, 3)) === "YDD") {
            $this->Client = new $this->Clients->{"YDD"};
        } else if (!!$this->Clients->{"TEST"} && !!$username && strtoupper(substr($username, 0, 6)) === "TESTER") {
            $this->Client = $this->Clients->{"TEST"};
        } else {
            return false;
        }
        if (!!$this->Client) {
            return $this->Client->login($username, $password);
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
        return $this->Client->getStudent();
    }

    /**
     * Lấy thông tin các kì học của sinh viên
     * @return TNU\Struct\Semester[]
     */
    public function getSemesterOfStudy () {
        return $this->Client->getSemesterOfStudy();
    }

    /**
     * Lấy lịch học
     * @param String $semester Mã học kì
     * @return TNU\Struct\TimeTable
     */
    public function getTimeTableOfStudy ($semester = false) {
        return $this->Client->getTimeTableOfStudy($semester);
    }

    /**
     * Lấy thông tin các kì thi của sinh viên
     * @return TNU\Struct\Semester[]
     */
    public function getSemesterOfTest () {
        return $this->Client->getSemesterOfTest();
    }

    /**
     * Lấy lịch thi
     * @param String $semester Mã kì thi
     * @return TNU\Struct\TimeTable
     */
    public function getTimeTableOfTest ($semester) {
        return $this->Client->getTimeTableOfTest($semester);
    }

    /**
     * Lấy điểm tổng kết
     * @return TNU\Struct\MarkTable
     */
    public function getMarkTable () {
        return $this->Client->getMarkTable();
    }
}
