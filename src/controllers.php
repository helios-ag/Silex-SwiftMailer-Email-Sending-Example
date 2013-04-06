<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app->match('/', function(Request $request) use ($app) {

    $form = $app['form.factory']->createBuilder('form')
        ->add('name', 'text')
        ->add('message', 'textarea')
        ->getForm();

    $request = $app['request'];

    if ($request->isMethod('POST'))
    {
        $form->bind($request);
        if ($form->isValid())
        {
            $data = $form->getData();

            $messagebody = $data['message'];
            $name        = $data['name'];

            $subject = "Message from ".$name;

            $app['mailer']->send(\Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom(array('silex.swiftmailer@gmail.com')) // replace with your own
                ->setTo(array('silex.swiftmailer@gmail.com'))   // replace with email recipient
                ->setBody($app['twig']->render('email.html.twig',   // email template
                    array('name'      => $name,
                          'message'   => $messagebody,
                    )),'text/html'));
        }

        return $app['twig']->render('index.html.twig', array(
            'message' => 'Message Sent',
            'form' => $form->createView()
        ));

    }
    return $app['twig']->render('index.html.twig', array(
            'message' => 'Send message to us',
            'form' => $form->createView()
        )
    );
}, "GET|POST");

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
});

