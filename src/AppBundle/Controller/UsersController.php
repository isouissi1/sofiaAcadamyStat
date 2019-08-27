<?php
namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
class UsersController extends Controller
{
    /**
     * @Route("/users", name="users")
     */
    public function UserShowAction()
    {
        $em = $this->getDoctrine()->getManager('db2');
        $sql = "SELECT mdl_user.id,mdl_user.username,mdl_user.firstname,mdl_user.lastname,mdl_user.country 
            FROM `mdl_user`
            WHERE mdl_user.id<>1";
        $restresult = $em->getConnection()->prepare($sql);
        $restresult->execute();
        $user = $restresult->fetchAll();
        //print_r($user);
        return $this->render('User/user.html.twig', array(
            'users' => $user));
    }
    /**
     * @Route("/user/{id}", name="users1")
     */
    public function DetailUserAction($id)
    {
        $em = $this->getDoctrine()->getManager('db2');
        $sqlusers = "SELECT COUNT('mdl_course') AS countcourse,mdl_user.username,mdl_user.id,mdl_user.firstname,mdl_user.lastname,
                     mdl_user.email,mdl_user.description AS role,mdl_user.city,mdl_user.country,mdl_files.contextid
                     FROM mdl_enrol,mdl_user,mdl_user_enrolments,mdl_course,mdl_files
                     WHERE mdl_user.id=mdl_user_enrolments.userid
                     AND mdl_user.picture=mdl_files.id
                     AND mdl_enrol.id=mdl_user_enrolments.enrolid
                     AND  mdl_enrol.courseid=mdl_course.id
                     AND mdl_user.id= :id";
        $resultusers = $em->getConnection()->prepare($sqlusers);
        $resultusers -> bindValue(':id',$id);
        $resultusers->execute();
        $detuser = $resultusers->fetchAll();
        $sqlcourse = "SELECT mdl_user.id,mdl_course.id,mdl_course.shortname,mdl_course.fullname,mdl_course.shortname,mdl_course_categories.name,
                      FROM_UNIXTIME (mdl_user_lastaccess.timeaccess) as timeacces
                      FROM mdl_enrol,mdl_user,mdl_user_enrolments,mdl_course,mdl_course_categories,mdl_user_lastaccess
                      WHERE mdl_user.id=mdl_user_enrolments.userid
                      AND mdl_enrol.id=mdl_user_enrolments.enrolid 
                      AND mdl_enrol.courseid=mdl_course.id
                      AND mdl_course.category=mdl_course_categories.id
                      AND mdl_user_lastaccess.userid=mdl_user.id
                      AND mdl_user_lastaccess.courseid=mdl_course.id
                      AND mdl_user.id= :id
                      ORDER BY mdl_course.id";
        $resultcourse = $em ->getConnection()->prepare($sqlcourse);
        $resultcourse -> bindValue(':id',$id);
        $resultcourse->execute();
        $usecourse = $resultcourse->fetchAll();
        return $this->render('User/usermodal.html.twig',array(
            'detusers' => $detuser,
            'usecourses' => $usecourse));
    }
    /**
     * @Route("/grade", name="grade")
     */
    public function GradeShowAction()
    {
        $em = $this->getDoctrine()->getManager('db2');
        $sqlgrade="SELECT mdl_user.id,mdl_user.firstname,mdl_course.fullname AS Matiere,mdl_grade_grades.rawgrade AS NOTE
        FROM mdl_user,mdl_course,mdl_grade_grades,mdl_grade_items
        WHERE mdl_user.id=mdl_grade_grades.userid
        AND mdl_grade_grades.itemid=mdl_grade_items.id
        AND mdl_course.id=mdl_grade_items.courseid
        AND mdl_grade_grades.rawgrade BETWEEN 0 and 100";
        $resultgrade = $em->getConnection()->prepare($sqlgrade);
        $resultgrade->execute();
        $grade = $resultgrade->fetchAll();
        $serializedEntity = $this->container->get('jms_serializer')->serialize($grade, 'json');
        return new Response($serializedEntity);
    }
    /**
     * @Route("/grade/{id}", name="grade1")
     */
    public function GradeUserPerIdAction($id)
    {
        $em = $this->getDoctrine()->getManager('db2');
        $sqlgrade="SELECT mdl_grade_grades.id,mdl_course.id as Courseid,mdl_user.id as Userid,mdl_user.firstname,mdl_course.shortname AS Matiere,mdl_grade_items.itemname,
                   mdl_grade_grades.rawgrade AS NOTE
                   FROM mdl_user,mdl_course,mdl_grade_grades,mdl_grade_items 
                   WHERE mdl_user.id=mdl_grade_grades.userid 
                   AND mdl_grade_grades.itemid=mdl_grade_items.id 
                   AND mdl_course.id=mdl_grade_items.courseid 
                   AND mdl_grade_grades.rawgrade BETWEEN 0 and 100 
                   AND mdl_user.id= :id
                   GROUP BY mdl_grade_grades.id ";
        $resultgrade = $em->getConnection()->prepare($sqlgrade);
        $resultgrade -> bindValue(':id',$id);
        $resultgrade->execute();
        $grade = $resultgrade->fetchAll();
        $serializedEntity = $this->container->get('jms_serializer')->serialize($grade,'json');
        return new Response($serializedEntity);}
    /**
     * @Route("/maxmin", name="grade2")
     */
    public function maxminAction(){
        $em = $this->getDoctrine()->getManager('db2');
        $sqlmax="SELECT mdl_course.id as IDCourse,mdl_grade_items.id,mdl_course.shortname AS Matiere,mdl_grade_items.itemname,
                MAX(mdl_grade_grades.rawgrade) AS maxnote,MIN(mdl_grade_grades.rawgrade) AS minnote
        FROM mdl_user,mdl_course,mdl_grade_grades,mdl_grade_items
        WHERE mdl_grade_grades.itemid=mdl_grade_items.id
        AND mdl_course.id=mdl_grade_items.courseid
        AND mdl_grade_grades.rawgrade BETWEEN 0 and 100
        GROUP BY mdl_grade_items.id";
        $resultmax = $em->getConnection()->prepare($sqlmax);
        $resultmax->execute();
        $max = $resultmax->fetchAll();
        $serializedEntity = $this->container->get('jms_serializer')->serialize($max,'json');
        return new Response($serializedEntity);
    }


        public function countCountriesAction(){
            $em = $this->getDoctrine()->getManager('db2');
            $sqlcountries ="SELECT COUNT(DISTINCT(mdl_user.country)) as count FROM mdl_user";
            $resultcountrie =$em->getConnection()->prepare($sqlcountries);
            $resultcountrie->execute();
            $countrie =$resultcountrie->fetchAll();
            return $this->render('default/index.html.twig', array(
                'countries' => $countrie));

        }
}