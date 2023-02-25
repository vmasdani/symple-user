<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route('/test')]
    public function test()
    {
        return new Response('hello.');
    }

    #[Route('/persist-user')]
    public function persistUser(
        Request $request,
        ManagerRegistry $doctrine,
        SerializerInterface $serializer
    ) {
        if ($request->query->get('secret_code') != $_ENV['APP_SECRET']) {
            return new Response('secret_code not right. Check your .env...');
        }

        if (!$request->query->get('username')) {
            return new Response('username must not be null.');
        }

        $newUser = new User();
        $newUser->setUsername($request->query->get('username'));

        $em = $doctrine->getManager();
        $em->persist($newUser);

        $em->flush();

        return new Response(
            $serializer->serialize($newUser, 'json'),
            200,
            ['content-type' => 'application/json']
        );
    }


    #[Route('/users', methods: ['POST'])]
    public function post(
        Request $request,
        ManagerRegistry $doctrine,
        SerializerInterface $serializer
    ) {
        $u = $serializer->deserialize($request->getContent(), User::class, 'json');
        $em = $doctrine->getManager();
        $fUser = $em->getRepository(User::class)->find($u->getId());

        // dd($fUser);
        if ($fUser) {
            $fUser->update($u);
        } else {
            $fUser = $u;
        }

        $em = $doctrine->getManager();
        $em->persist($fUser);
        $em->flush();

        return new Response(
            $serializer->serialize($fUser, 'json'),
            200,
            ['content-type' => 'application/json']
        );
    }
    #[Route('/users', methods: ['GET'])]
    public function all(
        Request $request,
        ManagerRegistry $doctrine,
        SerializerInterface $serializer
    ) {
        return new Response(
            $serializer->serialize($doctrine->getRepository(User::class)->findAll(), 'json'),
            200,
            ['content-type' => 'application/json']
        );
    }
}
