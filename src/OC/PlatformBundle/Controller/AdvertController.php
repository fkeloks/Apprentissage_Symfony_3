<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Skill;

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

  public function viewAction($id)
  {
    $em = $this->getDoctrine()->getManager();
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if($advert === null) {
        throw new NotFoundHttpException('Page ' . $id . ' inexistante.');
    }

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
    $advert = new Advert();
    $advert->setTitle('Recherche développeur Symfony.');
    $advert->setAuthor('Alexandre');
    $advert->setContent("Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…");

    $app1 = new Application();
    $app1->setAuthor('Marine');
    $app1->setContent('J\'ai toutes les qualités requises.');
    $app1->setAdvert($advert);

    $app2 = new Application();
    $app2->setAuthor('Pierre');
    $app2->setContent('Je suis très motivé');
    $app2->setAdvert($advert);

    $em = $this->getDoctrine()->getManager();

    $em->persist($advert);
    $em->persist($app1);
    $em->persist($app2);

    $listSkills = $em->getRepository('OCPlatformBundle:Skill')->findAll();
    foreach ($listSkills as $skill) {

      $advertSkill = new AdvertSkill();
      $advertSkill->setAdvert($advert);
      $advertSkill->setSkill($skill);
      $advertSkill->setLevel('Expert');

      $em->persist($advertSkill);

    }

    $em->flush();
    $request->getSession()->getFlashBag()->add('success', 'Annonce bien enregistrée.');

    // Reste de la méthode qu'on avait déjà écrit
    if ($request->isMethod('POST')) {
      // Puis on redirige vers la page de visualisation de cettte annonce
      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
    }

    // Si on n'est pas en POST, alors on affiche le formulaire
    return $this->render('OCPlatformBundle:Advert:add.html.twig', array('advert' => $advert));
  }

  public function editAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // La méthode findAll retourne toutes les catégories de la base de données
    $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

    // On boucle sur les catégories pour les lier à l'annonce
    foreach ($listCategories as $category) {
      $advert->addCategory($category);
    }

    // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
    // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

    // Étape 2 : On déclenche l'enregistrement
    $em->flush();

    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
        'advert' => $advert
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

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // On boucle sur les catégories de l'annonce pour les supprimer
    foreach ($advert->getCategories() as $category) {
      $advert->removeCategory($category);
    }

    // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
    // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

    // On déclenche la modification
    $em->flush();

    $request->getSession()->getFlashBag()->add('success', 'Catégories enlevées.');

    return $this->redirectToRoute('oc_platform_home');
  }

  public function menuAction()
  {    
    $em = $this->getDoctrine()->getManager();
    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findBy([], [], 3);

    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      'listAdverts' => array_reverse($listAdverts)
    ));
  }

}