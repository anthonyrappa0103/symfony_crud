<?php

namespace App\DataFixtures;

use App\Entity\Vehicle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $generator  = Factory::create("fr_FR");
        
        for ($i = 0; $i < 20; $i++) {
            $vehicle = new Vehicle();
            $vehicle->setDateAdded(new \DateTime());
            $type = ['used','new'];
            $vehicle->setType($type[array_rand(['used','new'])]);
            $vehicle->setMsrp($generator->randomFloat(2));
            $vehicle->setYear($generator->numberBetween(1901, 2022));
            $vehicle->setMake($generator->lexify()); 
            $vehicle->setModel($generator->lexify());
            $vehicle->setMiles($generator->numberBetween(0, 100000));
            $vehicle->setVin($generator->lexify());
            
            $manager->persist($vehicle);
        }

        $manager->flush();
    }
}
