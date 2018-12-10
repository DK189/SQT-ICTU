<?php
namespace TNU\Struct;

use \JsonObject;
use \ReflectionClass;
use \ReflectionProperty;

class SubjectTable extends JsonObject {
    /**
     * Kỳ học
     * @var TNU\Struct\Semeter
     */
    public $Semester;

    /**
     * Đặt kỳ học
     * @method setSemeter
     * @param  Semester   $semester Kỳ học
     */
    public function setSemeter (Semester $semester) {
        $this->Semester = $semester;
    }

    /**
     * Lấy kỳ học
     * @method getSemeter
     * @return Semester     Thông tin về kỳ học
     */
    public function getSemeter () {
        return $this->Semester;
    }

    /**
     * Danh sách các môn học
     * @var array
     */
    public $Subjects = array();

    /**
     * Thêm môn
     * @param Subject $subject Môn học
     */
    public function addSubject (Subject $subject) {
        $MaMons = array_map(
            function (Subject $subject) {
                return $subject->MaMon;
            },
            $this->Subjects
        );
        if ( !in_array($subject->MaMon, $MaMons) ) {
            $this->Subjects[] = $subject;
        }
    }

    public function __get ($name) {
        return isset($this->{$name}) ? $this->{$name} : null;
    }
}
?>
