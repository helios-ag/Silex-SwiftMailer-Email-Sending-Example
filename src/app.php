<?php

require_once __DIR__.'/../vendor/Silex/silex.phar';

require_once __DIR__.'/../vendor/swiftmailer/lib/swift_required.php';
/**  Bootstraping */

$app = new Silex\Application;

$app->register(new \Silex\Extension\TwigExtension(), array(
    'twig.path' => __DIR__.'/templates',
    'twig.class_path' => __DIR__.'/../vendor/Twig/lib',
));

$app->register(new \Silex\Extension\SymfonyBridgesExtension(), array(
   'symfony_bridges.class_path' => __DIR__ . '/../vendor/symfony/src'
));

$app->register(new \Silex\Extension\FormExtension(), array(
    'form.class_path' => __DIR__ . '/../vendor/symfony/src'
));

$app->register(new \Silex\Extension\TranslationExtension(), array(
    'translation.class_path' => __DIR__ . '/../vendor/symfony/src',
    'translator.messages' => array()
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
                        $message = \Swift_Message::newInstance()
                        ->setSubject('Hello!')
                        ->setFrom(array('silex.swiftmailer@gmail.com'))     //replace with your own
                        ->setTo(array('silex.swiftmailer@gmail.com'))       //replace with your own
                        ->setBody($app['twig']->render('email.html.twig',   // email template
                                        array('name'      => $name,
                                              'message'   => $messagebody,                                             
                                            )),'text/html');

                $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com',25)
                        ->setUsername('silex.swiftmailer@gmail.com')       //replace with your own
                        ->setPassword('simplepassword')   // replace with your own, but this account is working
                        ->setEncryption('ssl')            // this settings required by gmail
                        ->setPort('465');                 // don't forget to enable ssl support in php

                $mailer = \Swift_Mailer::newInstance($transport);
                $mailer->send($message);               
                               
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