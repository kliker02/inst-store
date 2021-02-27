<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $objUser = new User();
        $objUser->setEmail('admin@gmail.com');
        $objUser->setPassword($this->encoder->encodePassword($objUser, 'some'));
        $objUser->setRoles(['ROLE_ADMIN']);
        $manager->persist($objUser);
        $manager->flush();
    }
}
