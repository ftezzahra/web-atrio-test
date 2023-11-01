<?php

namespace App\Controller;

use App\Entity\Emplois;
use App\Entity\Personnes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class PersonnesController extends AbstractController
{
    /**
     * @Route("/personne", name="personne_new", methods={"POST"})
     */
    public function new(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        $personne = new Personnes();
        $personne->setNom($data['nom']);
        $personne->setPrenom($data['prenom']);

        $dateNaissance = new \DateTime($data['dateNaissance']);
        $personne->setDateDeNaissance($dateNaissance);

        // Vérifier si la personne a plus de 150 ans
        $currentDate = new \DateTime();
        $age = $currentDate->diff($dateNaissance)->y;

        if ($age > 150) {
            return new JsonResponse(['error' => 'La personne ne peut pas être plus âgée que 150 ans.'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($personne);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        $entityManager->persist($personne);
        $entityManager->flush();

        return new Response('Nouvelle personne enregistrée avec succès', 201);
    }

    /**
     * @Route("/personne/{id}/emploi", name="personne_add_emploi", methods={"POST"})
     */
    public function addEmploiToPerson(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $data = json_decode($request->getContent(), true);

        //Search if person exists
        $personne = $entityManager->getRepository(Personnes::class)->find($id);

        if (!$personne) {
            return new JsonResponse(['error' => 'Personne non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        // Job proprties
        $emploi = new Emplois();
        $emploi->setNomEntreprise($data['nomEntreprise']);
        $emploi->setPoste($data['posteOccupe']);
        $emploi->setDateDeDebut(new \DateTime($data['dateDebut']));

        if (isset($data['dateFin'])) {
            $emploi->setDateDeFin(new \DateTime($data['dateFin']));
        }

        // Match job to that person
        $emploi->setPersonne($personne);

        $entityManager->persist($emploi);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Emploi ajouté avec succès à la personne.'], Response::HTTP_CREATED);
    }

    /**
    * @Route("/personnes", name="personnes_list", methods={"GET"})
    */
    public function listOfPersons(EntityManagerInterface $entityManager): JsonResponse
    {
        // Get all persons sorted by nom
        $personnes = $entityManager->getRepository(Personnes::class)->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC']);

        $data = [];
        foreach ($personnes as $personne) {
            // Age?
            $age = $personne->getDateDeNaissance()
                ? $personne->getDateDeNaissance()->diff(new \DateTime())->y
                : null;

            //  Filter the actuel job or null finish date
            $emploisActuels = $personne->getEmplois()->filter(function (Emplois $emploi) {
                $dateFin = $emploi->getDateDeDebut();
                return !$dateFin || $dateFin > new \DateTime();
            });

            // Format jobs for response
            $emploisActuelsFormatted = [];
            foreach ($emploisActuels as $emploi) {
                $emploisActuelsFormatted[] = [
                    'nomEntreprise' => $emploi->getNomEntreprise(),
                    'posteOccupe' => $emploi->getPosteOccupe(),
                    'dateDebut' => $emploi->getDateDebut() ? $emploi->getDateDebut()->format('Y-m-d') : null,
                ];
            }

            //  Add the person's information to repsonse
            $data[] = [
                'nom' => $personne->getNom(),
                'prenom' => $personne->getPrenom(),
                'age' => $age,
                'emploisActuels' => $emploisActuelsFormatted,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/personnes/{companyName}", name="personnes_by_company")
     */
    public function findByCompanyName(EntityManagerInterface $entityManager, $companyName): Response
    {
        $personnes = $entityManager->getRepository(Personnes::class)->findByCompanyName($companyName);

        if (!$personnes) {
            return new JsonResponse(['message' => 'Aucune personne trouvée pour cette entreprise.'], Response::HTTP_NOT_FOUND);
        }

        $personnesArray = [];
        foreach ($personnes as $personne) {
            $personnesArray[] = [
                'id' => $personne->getId(),
                'nom' => $personne->getNom(),
                'prenom' => $personne->getPrenom(),
            ];
        }

        return new JsonResponse($personnesArray, Response::HTTP_OK);
    }

    /**
     * @Route("/personne/{id}/emplois", name="personne_emplois_by_date_range", methods={"GET"})
     */
    public function emploisByDateRange(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        $id,
        Request $request
    ): Response {
        try {
            $startDate = new \DateTime($request->query->get('startDate'));
            $endDate = new \DateTime($request->query->get('endDate'));

            $emplois = $entityManager->getRepository(Emplois::class)->findEmploisByPersonAndDateRange($id, $startDate, $endDate);

            // Sérialiser la liste des emplois en JSON
            $jsonContent = $serializer->serialize($emplois, 'json');

            return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}