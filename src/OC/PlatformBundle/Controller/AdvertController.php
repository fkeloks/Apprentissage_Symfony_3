<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Skill;
use OC\PlatformBundle\Form\AdvertType;

class AdvertController extends Controller
{

  public function indexAction($page) {
    if ($page < 1) {
        throw new NotFoundHttpException('Page '.$page.' inexistante.');
    }

    $em = $this->getDoctrine()->getManager();
    $listAdverts = $this->getDoctrine()->getManager()->getRepository('OCPlatformBundle:Advert')->getAdverts($page, 5);

    $nbPages = ceil(count($listAdverts) / 5);

    if ($page > $nbPages) {
      throw $this->createNotFoundException("La page ".$page." n'existe pas.");
    }

    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
        'listAdverts' => $listAdverts,
        'nbPages'     => $nbPages,
        'page'        => $page,
    ));
  }

  public function homeAction() {
      return new Response('Page home');
  }

  public function viewAction(Advert $advert, $id)
  {
    $em = $this->getDoctrine()->getManager();
    /*$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if($advert === null) {
        throw new NotFoundHttpException('Page ' . $id . ' inexistante.');
    }*/

    $listApplications = $em->getRepository('OCPlatformBundle:Application')->findBy(['advert' => $advert]);

    $listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')->findBy(array('advert' => $advert));

    dump($advert);

    return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
      'advert' => $advert,
      'listApplications' => $listApplications,
      'listAdvertSkills' => $listAdvertSkills
    ));
  }

  public function addAction(Request $request)
  {
    if (!$this->get('security.authorization_checker')->isGranted('ROLE_AUTEUR')) {
        throw new AccessDeniedException('Accès limité aux auteurs.');
    }

    $advert = new Advert();
    $form = $this->createForm(AdvertType::class, $advert);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);
        $em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Annonce bien enregistrée.');
        return $this->redirectToRoute('oc_platform_view', array(
            'id' => $advert->getId()
        ));

    }

    return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
        'advert' => $advert,
        'form' => $form->createView()
    ));
  }

  public function editAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
    }

    $form = $this->createForm(AdvertType::class, $advert);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
        $em->persist($advert);
        $em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Annonce bien modifiée.');
        return $this->redirectToRoute('oc_platform_view', array(
            'id' => $id
        ));
    }

    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
        'advert' => $advert,
        'form' => $form->createView()
    ));
  }

  public function editImageAction($id) {
      if($id <= 0) {
          throw new NotFoundHttpException('ID Invalide.');
      }

      $em = $this->getDoctrine()->getManager();
      $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

      $image = $advert->getImage();
      if($image != null) {
            $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
            $em->flush();
      }

      return $this->redirectToRoute('oc_platform_view', array('id' => $id));
  }

  public function deleteAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
    }

    foreach ($advert->getCategories() as $category) {
      $advert->removeCategory($category);
    }

    $em->remove($advert);
    $em->flush();

    $request->getSession()->getFlashBag()->add('success', 'Catégories enlevées.');

    return $this->redirectToRoute('oc_platform_home');
  }

  public function menuAction()
  {    
    $em = $this->getDoctrine()->getManager();
    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findBy([
        'published' => true
    ], [
        'date' => 'DESC'
    ], 3);

    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      'listAdverts' => array_reverse($listAdverts)
    ));
  }

  public function translationAction($name)
  {
    return $this->render('OCPlatformBundle:Advert:translation.html.twig', array(
      'name' => $name
    ));
  }

}