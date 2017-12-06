<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function () use ($app) {
    $mensaje = '';
    $form = array();
    $status = $app['session']->get('statusForm');

    if ($app['session']->get('flash')) {
        $mensaje = $app['session']->get('flash');
        $app['session']->set('flash', '');
    }

    if ($status === true)
    {
        $app['session']->set('form', '');
        $form = array(
                'nombre' => '',
                'empresa' => '',
                'telefono' => '',
                'email' => '',
                'mensaje' => ''
            );
    }
    else
    {
        $form = $app['session']->get('form');
        if (empty($form)) {
            $form = array(
                'nombre' => '',
                'empresa' => '',
                'telefono' => '',
                'email' => '',
                'mensaje' => ''
            );
        }
    }

    $one = mt_rand(1, 9);
    $two = mt_rand(1, 9);
    $captcha = $one + $two;

    $app['session']->set('captcha', $captcha);
    $app['session']->get('statusForm', ''); 

    return $app['twig']->render('pages/index.html.twig', array(
            'mensaje' => $mensaje,
            'one' => $one,
            'two' => $two,
            'formStatus' => $status,
            'form' => $form
        )
    );
})->bind('inicio');

$app->post('/sendContact', function (Request $request) use ($app) {
    if ($request->getMethod() == 'POST')
        {
            if ($app['session']->get('captcha') == $request->request->get('captcha'))
            {
                $body = $app['twig']->render('email.twig.html', array(
                        'nombre' => $request->request->get('nombre'),
                        'email' => $request->request->get('email'),
                        'empresa' => $request->request->get('empresa'),
                        'telefono' => $request->request->get('telefono'),
                        'mensaje' => $request->request->get('mensaje')
                    )
                );

                $message = \Swift_Message::newInstance()
                    ->setSubject('Contacto InMove.Mx')
                    ->setFrom('no-reply@inmove.mx')
                    ->setTo($request->request->get('email'))
                    ->setBcc('monica.perez@inmove.mx')
                    ->setBody($body, 'text/html');

                $app['mailer']->send($message);

                $app['session']->set('flash', 'Sus datos fueron envíados con éxito, en breve nos pondremos en contacto.');
                $app['session']->set('statusForm', true);
            }
            else
            {
                $app['session']->set('flash', 'El resultado es incorrecto, intentalo de nuevo por favor.');
                $app['session']->set('form', array(
                        'nombre' => $request->request->get('nombre'),
                        'email' => $request->request->get('email'),
                        'empresa' => $request->request->get('empresa'),
                        'telefono' => $request->request->get('telefono'),
                        'mensaje' => $request->request->get('mensaje')
                    )
                );
                $app['session']->set('statusForm', false);
            }

            return $app->redirect('./#form-contact');
        }
    return 'Error';
})->bind('contacto');

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
