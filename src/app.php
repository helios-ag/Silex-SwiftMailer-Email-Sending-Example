<?php

require_once __DIR__.'/../vendor/Silex/silex.phar';

/**  Bootstraping */

$app = new Silex\Application;

use Silex\Extension\SymfonyBridgesExtension;
use Silex\Extension\TranslationExtension;
use Silex\Extension\FormExtension;
use Silex\Extension\TwigExtension;
use Silex\Extension\SwiftmailerExtension;

$app->register(new TwigExtension(), array(
    'twig.path'       => array(
	  __DIR__.'/templates',
	  __DIR__.'/../vendor/symfony/src/Symfony/Bridge/Twig/Resources/views/Form'
	),
    'twig.class_path' => __DIR__.'/../vendor/Twig/lib',
));

$app->register(new SymfonyBridgesExtension(), array(
   'symfony_bridges.class_path' => __DIR__ . '/../vendor/symfony/src'
));

$app->register(new FormExtension(), array(
    'form.class_path' => __DIR__ . '/../vendor/symfony/src'
));

$app->register(new TranslationExtension(), array(
    'translation.class_path' => __DIR__ . '/../vendor/symfony/src',
    'translator.messages' => array()
));

$app->register(new SwiftmailerExtension(), array(
     'swiftmailer.options' => array(
            'host'       => 'smtp.gmail.com',
            'port'       => 465,
            'username'   => 'silex.swiftmailer@gmail.com',
            'password'   => 'simplepassword',
            'encryption' => 'ssl',
            'auth_mode'  => 'login'),
      'swiftmailer.class_path' => __DIR__.'/../vendor/swiftmailer/lib/classes'
));

/** App definition */

$app->error(function(Exception $e) use ($app){
    if (!in_array($app['request']->server->get('REMOTE_ADDR'), array('127.0.0.1', '::1'))) {
        return $app->redirect('/');
    }
});

$app->match('/', function() use ($app) {
    
    $form = $app['form.factory']->createBuilder('form')
            ->add('name', 'text')    
            ->add('message', 'textarea')            
    ->getForm();
    
    $request = $app['request'];
     
    if ($request->getMethod() == 'POST')
    {
        $form->bindRequest($request);
        if ($form->isValid())
        {         
            $data = $form->getData();
            
            $messagebody = $data['message'];
            $name        = $data['name'];
            
            $subject = "Message from ".$name;
            
            $app['mailer']->send($app['mailer']
				    ->createMessage()
				    ->setFrom('silex.swiftmailer@gmail.com') // replace with your own
				    ->setTo('silex.swiftmailer@gmail.com')   // replace with email recipient
				    ->setSubject($subject)
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

return $app;