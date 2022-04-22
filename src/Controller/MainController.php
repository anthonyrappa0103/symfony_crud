<?php

namespace App\Controller;
use App\Entity\Vehicle;
use App\Form\VehicleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Knp\Component\Pager\PaginatorInterface;

class MainController extends AbstractController
{

    public function indexAction(Request $request, PaginatorInterface $paginator): Response
    {
        $pageIndex = $request->query->getInt('pageIndex', 1);
        $limitCount = $request->query->getInt('limitCount', 1);
        $sortBy =$request->query->get('sortBy');
        $sortDicrection = $request->query->get('sortDicrection');
        $filter = $request->query->get('filter');
        $type = $this->getParameter('app.vehicle_type');


        $repository = $this->getDoctrine()->getRepository(Vehicle::class);
        $query = $repository->createQueryBuilder('v');
        $query->select('v')
            ->where('v.deleted = :deleted')
            ->setParameter('deleted', 0);
        if ($type) {
            $query->andWhere('v.type = :type' )
                ->setParameter('type', $type);
        }

        if ($filter) {
            $query->andWhere('v.date_added LIKE :filter OR v.type LIKE :filter OR v.msrp LIKE :filter OR v.year LIKE :filter OR v.make LIKE :filter OR v.model LIKE :filter OR v.miles LIKE :filter OR v.vin LIKE :filter' )
                ->setParameter('filter', '%'.$filter.'%');
        }
        if (isset($sortBy) && !empty($sortBy)) {
            $query->addOrderBy('v.'.$sortBy, $sortDicrection);
        }


        $query->getQuery();
        $data = $paginator->paginate( $query, $pageIndex, $limitCount);

        $arrayCollection = array();

        foreach($data as $item) {
            $arrayCollection[] = array(
                'id' => $item->getId(),
                'data_added' => $item->getDateAdded(),
                'type' => $item->getType(),
                'msrp' => $item->getMsrp(),
                'year' => $item->getYear(),
                'make' => $item->getMake(),
                'model' => $item->getModel(),
                'miles' => $item->getMiles(),
                'vin' => $item->getVin(),
            );
        }

        return $this->json($arrayCollection);
    }   

    public function createAction(Request $request): Response {

        $vehicle = new Vehicle();
        $date_added = new \DateTime($request->get('date_added'));
        $vehicle->setDateAdded($date_added);
        $vehicle->setType($request->get('type'));
        $vehicle->setMsrp($request->get('msrp'));
        $vehicle->setYear($request->get('year'));
        $vehicle->setMake($request->get('make'));
        $vehicle->setModel($request->get('model'));
        $vehicle->setMiles($request->get('miles'));
        $vehicle->setVin($request->get('vin'));
 
        $em = $this->getDoctrine()->getManager();
        $em->persist($vehicle);
        $em->flush();

        return $this->json('Created new vehicle successfully with id '. $vehicle->getId());
    }
    
    public function updateAction(Request $request, int $vehicleId): Response {


        $em = $this->getDoctrine()->getManager();
        $vehicle = $em->getRepository(Vehicle::class)->find($vehicleId);
 
        if (!$vehicle) {
            return $this->json('No Vehicle found for id' . $vehicleId, 404);
        }

        $date_added = new \DateTime($request->get('date_added'));
        $vehicle->setDateAdded($date_added);
        $vehicle->setType($request->get('type'));
        $vehicle->setMsrp($request->get('msrp'));
        $vehicle->setYear($request->get('year'));
        $vehicle->setMake($request->get('make'));
        $vehicle->setModel($request->get('model'));
        $vehicle->setMiles($request->get('miles'));
        $vehicle->setVin($request->get('vin'));
        $em->flush();
 
        return $this->json('Updated vehicle successfully with id '. $vehicleId);
     }
     
    public function deleteAction(int $vehicleId): Response {

        $em = $this->getDoctrine()->getManager(); 
        $queryBuilder = $em->createQueryBuilder();
        $query = $queryBuilder->update(Vehicle::class, 'v')
                ->set('v.deleted', 1)
                ->where('v.id = :id')
                ->setParameter('id', $vehicleId)
                ->getQuery();
        $result = $query->execute();
        
        return $this->json('Deleted vehicle successfully with id '. $vehicleId);

    }
}
