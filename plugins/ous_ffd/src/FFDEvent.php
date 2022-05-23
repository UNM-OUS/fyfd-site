<?php

namespace DigraphCMS_Plugins\unmous\ous_ffd;

use DateTime;
use DigraphCMS\Content\Page;
use DigraphCMS\Content\Pages;

class FFDEvent extends Page
{
    const DEFAULT_SLUG = "/[year]";
    protected $studentSignup = false;
    protected $facultySignup = false;

    public function studentSignup(): ?FFDStudentSignup
    {
        if ($this->studentSignup === false) {
            $this->studentSignup = Pages::children($this->uuid())
                ->where("class = 'ffd_student_signup'")
                ->limit(1)
                ->fetch();
        }
        return $this->studentSignup;
    }

    public function facultySignup(): ?FFDFacultySignup
    {
        if ($this->facultySignup === false) {
            $this->facultySignup = Pages::children($this->uuid())
                ->where("class = 'ffd_faculty_signup'")
                ->limit(1)
                ->fetch();
        }
        return $this->facultySignup;
    }

    public function date(): DateTime
    {
        return $this->_date('date');
    }

    public function setDate(DateTime $date)
    {
        $this->_setDate('date', $date);
        return $this;
    }

    public function slugVariable(string $name): ?string
    {
        switch ($name) {
            case 'year':
                return $this->date()->format('Y');
            default:
                return parent::slugVariable($name);
        }
    }

    public function routeClasses(): array
    {
        return ['ffd_event', '_any'];
    }
}
