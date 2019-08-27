<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function CountAction(){
        $em = $this->getDoctrine()->getManager('db2');
        $sqlcount="SELECT COUNT('mdl_user') AS totaluser
                   FROM mdl_user";
        $resultcount = $em->getConnection()->prepare($sqlcount);
        $resultcount->execute();
        $count = $resultcount->fetchAll();
        $em = $this->getDoctrine()->getManager('db2');
        $sqlcourse="SELECT COUNT('mdl_course') AS totalcourse
                   FROM mdl_course";
        $resultcourse = $em->getConnection()->prepare($sqlcourse);
        $resultcourse->execute();
        $course = $resultcourse->fetchAll();
        $em = $this->getDoctrine()->getManager('db2');
        $sqlcategory="SELECT COUNT('mdl_course_categories') AS totalcategory
                   FROM mdl_course_categories";
        $resultcategory = $em->getConnection()->prepare($sqlcategory);
        $resultcategory->execute();
        $category= $resultcategory->fetchAll();
        $sqlcountries ="SELECT COUNT(DISTINCT(mdl_user.country)) as count FROM mdl_user";
        $resultcountrie =$em->getConnection()->prepare($sqlcountries);
        $resultcountrie->execute();
        $countrie =$resultcountrie->fetchAll();
        $sqluscountry="SELECT mdl_user.country as country,COUNT(mdl_user.id) as countt
                       FROM mdl_user                       
                       GROUP BY mdl_user.country
                       ORDER by countt DESC
                       LIMIT 5";
        $resultuscountry = $em->getConnection()->prepare($sqluscountry);
        $resultuscountry->execute();
        $uscountrys= $resultuscountry->fetchAll();

        $sqlgrahcat="SELECT mdl_course_categories.name,mdl_course_categories.coursecount as numbercourse ,
                     mdl_course_categories.coursecount/
                     (SELECT COUNT(mdl_course.id) FROM mdl_course) * 100 as pourcentage
                     FROM mdl_course_categories";
        $resultsqlgraph = $em->getConnection()->prepare($sqlgrahcat);
        $resultsqlgraph->execute();
        $graphcat = $resultsqlgraph->fetchAll();
        $sqlcounteach="SELECT COUNT(mdl_user.id) as counteach
                       FROM mdl_user                       
                       WHERE mdl_user.description='Enseignant'";
        $resultcounteach = $em->getConnection()->prepare($sqlcounteach);
        $resultcounteach->execute();
        $counteach=$resultcounteach->fetchAll();
        $sqlcountstuds="SELECT COUNT(mdl_user.id) as countstud
                       FROM mdl_user                       
                       WHERE mdl_user.description NOT LIKE 'Enseignant'
                      ";
        $resultcountstuds = $em->getConnection()->prepare($sqlcountstuds);
        $resultcountstuds->execute();
        $countstud=$resultcountstuds->fetchAll();

        return $this->render('default/index.html.twig', array(
            'counts' => $count,
            'courses' => $course,
            'categorys' => $category,
            'countries' => $countrie,
            'uscountrys' => $uscountrys,
            'graphcats' => $graphcat,
            'counteachs' => $counteach,
            'countstuds' => $countstud));
    }
    /**
     * @Route("/coursebar", name="coursebar")
     */
    public function CourseBarAction(){
        $em = $this->getDoctrine()->getManager('db2');
        $sqlbar="SELECT COUNT('mdl_user.username') as countUser,mdl_course.shortname
                    FROM mdl_enrol,mdl_user,mdl_user_enrolments,mdl_course,mdl_course_categories
                    WHERE mdl_user.id=mdl_user_enrolments.userid
                    AND mdl_enrol.id=mdl_user_enrolments.enrolid
                    AND mdl_enrol.courseid=mdl_course.id
                    AND mdl_course.category=mdl_course_categories.id
                    GROUP BY mdl_course.fullname
                    ORDER BY mdl_course.shortname";
        $resultsqlbar = $em ->getConnection()->prepare($sqlbar);
        $resultsqlbar->execute();
        $usecourse = $resultsqlbar->fetchAll();
        $serializedEntity = $this->container->get('jms_serializer')->serialize($usecourse, 'json');
        return new Response($serializedEntity);
    }
    /**
     * @Route("/coursecat", name="coursecat")
     */
    public function CourseCategoryCountAction(){
        $em = $this->getDoctrine()->getManager('db2');
        $sqlcountcat="SELECT mdl_course_categories.name,mdl_course_categories.coursecount as numbercourse,mdl_course_categories.coursecount/
                     (SELECT COUNT(mdl_course.id) FROM mdl_course) * 100 as pourcentage 
                      FROM mdl_course_categories";
        $resultsqlcountcat = $em ->getConnection()->prepare($sqlcountcat);
        $resultsqlcountcat->execute();
        $countcat = $resultsqlcountcat->fetchAll();
        $serializedEntity = $this->container->get('jms_serializer')->serialize($countcat, 'json');
        return new Response($serializedEntity);
    }
    /**
     * @Route("/countcountry", name="country")
     */
    public function CountryCountAction(){
        $em = $this->getDoctrine()->getManager('db2');
        $sqluscountry="SELECT mdl_user.country as country,COUNT(mdl_user.id) as countt
                       FROM mdl_user                       
                       GROUP BY mdl_user.country
                       ORDER by countt DESC";
        $resultuscountry = $em->getConnection()->prepare($sqluscountry);
        $resultuscountry->execute();
        $uscountrys= $resultuscountry->fetchAll();
        $data ='{';
        foreach($uscountrys as $uscountry){
            $data.='"'.strtolower($uscountry['country']).'":"'.$uscountry['countt'].'"';
            if (next($uscountrys)==true) $data .= ",";
        }
        $data .='}';
        return new Response($data);

    }

   

}
