<?php
namespace TNU;

interface ClientInterface {
    /**
     * Đăng nhập
     * @param String $username Tên tài khoản
     * @param String $password Mật khẩu
     * @return Boolean
     */
    public function login ($username, $password);

    /**
     * Lấy thông tin sinh viên
     * @method getStudent
     * @return \TNU\Struct\Student
     */
    public function getStudent ();

    /**
     * Lấy thông tin các kì học của sinh viên
     * @return TNU\Struct\Semester[]
     */
    public function getSemesterOfStudy ();

    /**
     * Lấy lịch học
     * @param String $semester Mã học kì
     * @return TNU\Struct\TimeTable
     */
    public function getTimeTableOfStudy ($semester = false);

    /**
     * Lấy thông tin các kì thi của sinh viên
     * @return TNU\Struct\Semester[]
     */
    public function getSemesterOfTest ();

    /**
     * Lấy lịch thi
     * @param String $semester Mã kì thi
     * @return TNU\Struct\TimeTable
     */
    public function getTimeTableOfTest ($semester);

    /**
     * Lấy điểm tổng kết
     * @return TNU\Struct\MarkTable
     */
    public function getMarkTable ();
}
?>
