<?php


namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;


class CourseController extends Controller
{
    /**
     * @Route("/courses", name="courses")
     */
    public function CourseShowAction()
    {

        $em = $this->getDoctrine()->getManager('db2');
        $sql = "SELECT mdl_course.id,mdl_course.fullname,mdl_course.shortname,mdl_course_categories.name 
                FROM `mdl_course`
                LEFT JOIN `mdl_course_categories` 
                ON mdl_course.category=mdl_course_categories.id
                WHERE mdl_course.id<>1
                ORDER BY mdl_course.id";
        $restresult = $em->getConnection()->prepare($sql);
        $restresult->execute();
        $course = $restresult->fetchAll();
        return $this->render('Course/course.html.twig', array(
        'courses' => $course,
    ));
    }


    /**
     * @Route("/course/{id}", name="courses1")
     */
    public function DetailCourseAction($id)
    {
        $em = $this->getDoctrine()->getManager('db2');
        $sqldetail="SELECT COUNT('mdl_user.username') as countUser,mdl_course_categories.name,mdl_course.fullname,mdl_course.shortname,
                    FROM_UNIXTIME(mdl_course.startdate,'%D %M %Y') as startdate,FROM_UNIXTIME(mdl_course.enddate,'%D %M %Y') as enddate ,mdl_course.id,mdl_course.summary
                    FROM mdl_enrol,mdl_user,mdl_user_enrolments,mdl_course,mdl_course_categories
                    WHERE mdl_user.id=mdl_user_enrolments.userid
                    AND mdl_enrol.id=mdl_user_enrolments.enrolid
                    AND mdl_enrol.courseid=mdl_course.id
                    AND mdl_course.category=mdl_course_categories.id
                    AND mdl_course.id = :id";
        $resultdetail = $em->getConnection()->prepare($sqldetail);
        $resultdetail -> bindValue(':id',$id);
        $resultdetail->execute();
        $detail = $resultdetail->fetchAll();
        $sqltech="  SELECT mdl_user.firstname as techfirstname,mdl_user.lastname as techlastname
                    FROM mdl_enrol,mdl_user,mdl_user_enrolments,mdl_course,mdl_course_categories
                    WHERE mdl_user.id=mdl_user_enrolments.userid
                    AND mdl_enrol.id=mdl_user_enrolments.enrolid
                    AND mdl_enrol.courseid=mdl_course.id
                    AND mdl_course.category=mdl_course_categories.id
                    AND mdl_course.id = :id
                    AND mdl_user.description = 'Enseignant'";
        $techresult = $em->getConnection()->prepare($sqltech);
        $techresult -> bindValue(':id',$id);
        $techresult->execute();
        $teach = $techresult->fetchAll();
        return $this->render('Course/course1.html.twig', array(
            'details' => $detail,
            'teachs' => $teach
        ));






    }
}